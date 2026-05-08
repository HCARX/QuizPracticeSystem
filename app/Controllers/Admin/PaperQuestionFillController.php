<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;

class PaperQuestionFillController extends Controller
{
    public function pickTemplate(string $id): void
    {
        $paper = $this->db->fetch(
            'SELECT p.*, s.name as subject_name FROM papers p LEFT JOIN subjects s ON p.subject_id = s.id WHERE p.id = ?',
            [(int) $id]
        );
        if (!$paper) {
            Response::error(404, 'Paper not found.');
            return;
        }

        $templates = $this->db->fetchAll(
            'SELECT * FROM paper_templates WHERE paper_id = ? ORDER BY created_at DESC',
            [(int) $id]
        );

        foreach ($templates as &$tpl) {
            $bp = json_decode($tpl['blueprint_json'] ?? '{}', true) ?: [];
            $sections = $bp['sections'] ?? [];
            $blockCount = 0;
            $qCount = 0;
            foreach ($sections as $sec) {
                foreach (($sec['blocks'] ?? []) as $blk) {
                    $blockCount++;
                    $qCount += (int) ($blk['count'] ?? 1);
                }
            }
            $tpl['_section_count'] = count($sections);
            $tpl['_block_count'] = $blockCount;
            $tpl['_q_count'] = $qCount;
        }
        unset($tpl);

        $this->view('admin.papers.pick_template', [
            'pageTitle' => 'Pick Template',
            'currentNav' => 'papers',
            'paper' => $paper,
            'templates' => $templates,
        ], 'admin');
    }

    public function start(string $paperId, string $templateId): void
    {
        $paper = $this->db->fetch(
            'SELECT p.*, s.name as subject_name FROM papers p LEFT JOIN subjects s ON p.subject_id = s.id WHERE p.id = ?',
            [(int) $paperId]
        );
        if (!$paper) { Response::error(404, 'Paper not found.'); return; }

        $template = $this->db->fetch(
            'SELECT * FROM paper_templates WHERE id = ? AND paper_id = ?',
            [(int) $templateId, (int) $paperId]
        );
        if (!$template) { Response::error(404, 'Template not found.'); return; }

        $slots = $this->buildSlots($template);

        // Map existing questions by (section_key, sort_order)
        $existing = $this->db->fetchAll(
            'SELECT id, section_key, sort_order, type, content_json, answer_json, analysis_json FROM questions WHERE paper_id = ?',
            [(int) $paperId]
        );
        $bySlot = [];
        foreach ($existing as $q) {
            $k = ($q['section_key'] ?? '') . '|' . (int) $q['sort_order'];
            $bySlot[$k] = $q;
        }
        foreach ($slots as &$slot) {
            $key = $slot['section_key'] . '|' . $slot['sort_order'];
            if (isset($bySlot[$key])) {
                $q = $bySlot[$key];
                $slot['question_id'] = (int) $q['id'];
                $slot['content_json'] = $q['content_json'];
                $slot['answer_json'] = $q['answer_json'];
                $slot['analysis_json'] = $q['analysis_json'];
            }
        }
        unset($slot);

        $initial = max(0, (int) $this->request->get('slot', '0'));
        if ($initial >= count($slots)) $initial = 0;

        $this->view('admin.papers.fill', [
            'pageTitle' => 'Enter Questions',
            'currentNav' => 'papers',
            'paper' => $paper,
            'template' => $template,
            'slots' => $slots,
            'initialSlot' => $initial,
        ], 'admin');
    }

    public function saveSlot(string $paperId, string $templateId, string $slotIndex): void
    {
        $this->validateCsrf();

        $template = $this->db->fetch(
            'SELECT * FROM paper_templates WHERE id = ? AND paper_id = ?',
            [(int) $templateId, (int) $paperId]
        );
        if (!$template) { $this->json(['error' => 'Template not found'], 404); return; }

        $slots = $this->buildSlots($template);
        $i = (int) $slotIndex;
        if (!isset($slots[$i])) { $this->json(['error' => 'Invalid slot'], 422); return; }
        $slot = $slots[$i];

        $data = $this->request->body();

        $type = $slot['block_type'];
        if ($slot['slot_type'] === 'child' && in_array($type, ['reading_material', 'listening_material'], true)) {
            $type = $slot['child_type'] ?? 'single_choice';
        }

        $content = $data['content'] ?? null;
        if (is_array($content)) $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        if (!$content) {
            $fallback = [];
            $contentKeys = [
                'stem','options','passage','audio_url','transcript','prompt','title','source',
                'word_count','pairs','items','sub_items',
                'media_type','url','file','caption','link_url',
                'content','format',
            ];
            foreach ($contentKeys as $f) {
                if (isset($data[$f]) && $data[$f] !== '') $fallback[$f] = $data[$f];
            }
            $content = json_encode($fallback, JSON_UNESCAPED_UNICODE);
        }
        $answer = $data['answer'] ?? null;
        if (is_array($answer)) $answer = json_encode($answer, JSON_UNESCAPED_UNICODE);
        $analysis = $data['analysis'] ?? null;
        if (is_array($analysis)) $analysis = json_encode($analysis, JSON_UNESCAPED_UNICODE);

        // parent_id lookup
        $parentId = null;
        if ($slot['slot_type'] === 'child' && isset($slot['parent_slot_index'])) {
            $parentSlot = $slots[$slot['parent_slot_index']] ?? null;
            if ($parentSlot) {
                $pRow = $this->db->fetch(
                    'SELECT id FROM questions WHERE paper_id = ? AND section_key = ? AND sort_order = ? AND parent_id IS NULL',
                    [(int) $paperId, $parentSlot['section_key'], (int) $parentSlot['sort_order']]
                );
                if ($pRow) $parentId = (int) $pRow['id'];
            }
        }

        $existing = $this->db->fetch(
            'SELECT id FROM questions WHERE paper_id = ? AND section_key = ? AND sort_order = ?' .
                ($slot['slot_type'] === 'child' ? ' AND parent_id IS NOT NULL' : ' AND parent_id IS NULL'),
            [(int) $paperId, $slot['section_key'], (int) $slot['sort_order']]
        );

        $row = [
            'paper_id' => (int) $paperId,
            'parent_id' => $parentId,
            'section_key' => $slot['section_key'],
            'type' => $type,
            'content_json' => $content,
            'answer_json' => $answer,
            'analysis_json' => $analysis,
            'score' => (float) ($slot['score'] ?? 0),
            'sort_order' => (int) $slot['sort_order'],
            'difficulty' => 3,
            'status' => 'draft',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $questionId = (int) $existing['id'];
            $this->db->update('questions', $row, 'id = ?', [$questionId]);
        } else {
            $row['created_by'] = Auth::id();
            $row['created_at'] = date('Y-m-d H:i:s');
            $questionId = $this->db->insert('questions', $row);
        }

        $count = $this->db->count('questions', 'paper_id = ? AND parent_id IS NULL', [(int) $paperId]);
        $this->db->update('papers', [
            'question_count' => $count,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $paperId]);

        $next = ($i + 1 < count($slots)) ? $i + 1 : null;
        $this->json(['success' => true, 'question_id' => $questionId, 'next_slot' => $next]);
    }

    private function buildSlots(array $template): array
    {
        $bp = json_decode($template['blueprint_json'] ?? '{}', true) ?: [];
        $sections = $bp['sections'] ?? [];

        $slots = [];
        $globalSort = 0;

        foreach ($sections as $sIdx => $section) {
            if (($section['type'] ?? '') === 'part') {
                continue;
            }
            $sectionKey = (string) ($section['id'] ?? $section['type'] ?? ('sec_' . $sIdx));
            $sectionTitle = (string) ($section['title'] ?? $section['type'] ?? ('Section ' . ($sIdx + 1)));

            foreach (($section['blocks'] ?? []) as $bIdx => $block) {
                $blockType = (string) ($block['type'] ?? 'single_choice');
                $blockTitle = (string) ($block['title'] ?? $blockType);
                $count = max(1, (int) ($block['count'] ?? 1));
                if (in_array($blockType, ['description', 'media'], true)) {
                    $count = 1;
                }
                $score = (float) ($block['score'] ?? 0);

                if ($blockType === 'reading_material' || $blockType === 'listening_material') {
                    $globalSort++;
                    $parentIndex = count($slots);
                    $slots[] = [
                        'index' => $parentIndex,
                        'section_key' => $sectionKey,
                        'section_title' => $sectionTitle,
                        'block_title' => $blockTitle,
                        'block_type' => $blockType,
                        'slot_type' => 'parent',
                        'parent_slot_index' => null,
                        'sort_order' => $globalSort,
                        'score' => 0.0,
                        'question_id' => null,
                    ];
                    $childType = (string) ($block['sub_type'] ?? 'single_choice');
                    $subCount = isset($block['sub_questions']) && is_array($block['sub_questions'])
                        ? count($block['sub_questions']) : $count;
                    for ($c = 0; $c < $subCount; $c++) {
                        $globalSort++;
                        $slots[] = [
                            'index' => count($slots),
                            'section_key' => $sectionKey,
                            'section_title' => $sectionTitle,
                            'block_title' => $blockTitle,
                            'block_type' => $blockType,
                            'child_type' => $childType,
                            'slot_type' => 'child',
                            'parent_slot_index' => $parentIndex,
                            'sort_order' => $globalSort,
                            'score' => $score,
                            'question_id' => null,
                        ];
                    }
                } else {
                    for ($c = 0; $c < $count; $c++) {
                        $globalSort++;
                        $slots[] = [
                            'index' => count($slots),
                            'section_key' => $sectionKey,
                            'section_title' => $sectionTitle,
                            'block_title' => $blockTitle,
                            'block_type' => $blockType,
                            'slot_type' => 'single',
                            'parent_slot_index' => null,
                            'sort_order' => $globalSort,
                            'score' => $score,
                            'question_id' => null,
                        ];
                    }
                }
            }
        }
        return $slots;
    }
}
