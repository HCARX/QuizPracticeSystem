<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;

class QuestionController extends Controller
{
    public function index(): void
    {
        $subjectId = $this->request->get('subject_id', '');
        $search = $this->request->get('search', '');

        $subjects = $this->db->fetchAll('SELECT id, name FROM subjects WHERE status = 1 ORDER BY sort_order');

        $where = '1=1';
        $params = [];
        if ($subjectId !== '') {
            $where .= ' AND p.subject_id = ?';
            $params[] = (int) $subjectId;
        }
        if ($search !== '') {
            $where .= ' AND p.title LIKE ?';
            $params[] = "%{$search}%";
        }

        $papers = $this->db->fetchAll(
            "SELECT p.*, s.name as subject_name,
                    (SELECT COUNT(*) FROM questions q WHERE q.paper_id = p.id AND q.parent_id IS NULL) as q_count,
                    (SELECT COUNT(*) FROM paper_templates pt WHERE pt.paper_id = p.id) as bp_count
             FROM papers p
             LEFT JOIN subjects s ON p.subject_id = s.id
             WHERE {$where}
             ORDER BY p.updated_at DESC",
            $params
        );

        $this->view('admin.questions.index', [
            'pageTitle' => 'Questions',
            'currentNav' => 'questions',
            'papers' => $papers,
            'subjects' => $subjects,
            'filters' => compact('subjectId', 'search'),
        ], 'admin');
    }

    public function create(): void
    {
        $paperId = (int) $this->request->get('paper_id', '0');
        $paper = null;
        if ($paperId) {
            $paper = $this->db->fetch(
                'SELECT p.*, s.name as subject_name FROM papers p LEFT JOIN subjects s ON p.subject_id = s.id WHERE p.id = ?',
                [$paperId]
            );
        }

        $subjects = $this->db->fetchAll('SELECT id, name FROM subjects WHERE status = 1 ORDER BY sort_order');
        $papers = $this->db->fetchAll('SELECT id, title, subject_id FROM papers ORDER BY subject_id, sort_order');

        $this->view('admin.questions.form', [
            'pageTitle' => 'Add Question',
            'currentNav' => 'questions',
            'question' => null,
            'paper' => $paper,
            'subjects' => $subjects,
            'papers' => $papers,
            'questionTypes' => self::questionTypes(),
        ], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $data = $this->request->body();
        $errors = $this->validateQuestion($data);
        if ($errors) {
            $this->json(['error' => $errors[0]], 422);
            return;
        }

        $row = $this->buildRow($data);
        $row['created_by'] = Auth::id();
        $row['created_at'] = date('Y-m-d H:i:s');
        $row['updated_at'] = date('Y-m-d H:i:s');

        $questionId = $this->db->insert('questions', $row);

        if (!empty($data['children'])) {
            $this->saveChildren($questionId, (int) $data['paper_id'], $data['children']);
        }

        $this->updatePaperQuestionCount((int) $data['paper_id']);

        $this->json(['success' => true, 'id' => $questionId, 'message' => 'Question created.']);
    }

    public function edit(string $id): void
    {
        $question = $this->db->fetch('SELECT * FROM questions WHERE id = ?', [(int) $id]);
        if (!$question) {
            Response::error(404, 'Question not found.');
            return;
        }

        $children = $this->db->fetchAll(
            'SELECT * FROM questions WHERE parent_id = ? ORDER BY sort_order',
            [(int) $id]
        );

        $paper = $this->db->fetch(
            'SELECT p.*, s.name as subject_name FROM papers p LEFT JOIN subjects s ON p.subject_id = s.id WHERE p.id = ?',
            [$question['paper_id']]
        );

        $subjects = $this->db->fetchAll('SELECT id, name FROM subjects WHERE status = 1 ORDER BY sort_order');
        $papers = $this->db->fetchAll('SELECT id, title, subject_id FROM papers ORDER BY subject_id, sort_order');

        $this->view('admin.questions.form', [
            'pageTitle' => 'Edit Question',
            'currentNav' => 'questions',
            'question' => $question,
            'children' => $children,
            'paper' => $paper,
            'subjects' => $subjects,
            'papers' => $papers,
            'questionTypes' => self::questionTypes(),
        ], 'admin');
    }

    public function update(string $id): void
    {
        $this->validateCsrf();

        $question = $this->db->fetch('SELECT * FROM questions WHERE id = ?', [(int) $id]);
        if (!$question) {
            $this->json(['error' => 'Question not found.'], 404);
            return;
        }

        $data = $this->request->body();
        $errors = $this->validateQuestion($data);
        if ($errors) {
            $this->json(['error' => $errors[0]], 422);
            return;
        }

        $row = $this->buildRow($data);
        $row['updated_at'] = date('Y-m-d H:i:s');

        $this->db->update('questions', $row, 'id = ?', [(int) $id]);

        $this->db->delete('questions', 'parent_id = ?', [(int) $id]);
        if (!empty($data['children'])) {
            $this->saveChildren((int) $id, (int) $data['paper_id'], $data['children']);
        }

        $this->updatePaperQuestionCount($question['paper_id']);

        $this->json(['success' => true, 'message' => 'Question updated.']);
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();

        $question = $this->db->fetch('SELECT paper_id FROM questions WHERE id = ?', [(int) $id]);
        if (!$question) {
            $this->json(['error' => 'Question not found.'], 404);
            return;
        }

        $this->db->delete('questions', 'parent_id = ?', [(int) $id]);
        $this->db->delete('questions', 'id = ?', [(int) $id]);
        $this->updatePaperQuestionCount($question['paper_id']);

        $this->json(['success' => true, 'message' => 'Question deleted.']);
    }

    public function batchImport(): void
    {
        $this->validateCsrf();

        $paperId = (int) $this->request->post('paper_id', '0');
        $jsonStr = $this->request->post('json_data', '');

        if (!$paperId) {
            $this->json(['error' => 'Paper ID is required.'], 422);
            return;
        }

        $paper = $this->db->fetch('SELECT id FROM papers WHERE id = ?', [$paperId]);
        if (!$paper) {
            $this->json(['error' => 'Paper not found.'], 404);
            return;
        }

        $questions = json_decode($jsonStr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json(['error' => 'Invalid JSON: ' . json_last_error_msg()], 422);
            return;
        }

        if (!is_array($questions)) {
            $this->json(['error' => 'JSON must be an array of question objects.'], 422);
            return;
        }

        $imported = 0;
        $failed = 0;

        $this->db->transaction(function ($db) use ($questions, $paperId, &$imported, &$failed) {
            $maxSort = (int) ($db->fetch(
                'SELECT COALESCE(MAX(sort_order), 0) as m FROM questions WHERE paper_id = ? AND parent_id IS NULL',
                [$paperId]
            )['m'] ?? 0);

            foreach ($questions as $q) {
                try {
                    $q['paper_id'] = $paperId;
                    $row = $this->buildRow($q);
                    $row['sort_order'] = ++$maxSort;
                    $row['created_by'] = Auth::id();
                    $row['created_at'] = date('Y-m-d H:i:s');
                    $row['updated_at'] = date('Y-m-d H:i:s');

                    $qid = $db->insert('questions', $row);

                    if (!empty($q['children'])) {
                        $this->saveChildren($qid, $paperId, $q['children']);
                    }
                    $imported++;
                } catch (\Throwable $e) {
                    $failed++;
                }
            }
        });

        $this->updatePaperQuestionCount($paperId);

        $this->json([
            'success' => true,
            'imported' => $imported,
            'failed' => $failed,
            'message' => "Imported {$imported} questions" . ($failed ? ", {$failed} failed" : '') . '.',
        ]);
    }

    public function apiPapersForSubject(string $subjectId): void
    {
        $papers = $this->db->fetchAll(
            'SELECT id, title FROM papers WHERE subject_id = ? ORDER BY sort_order, year DESC',
            [(int) $subjectId]
        );
        $this->json($papers);
    }

    /**
     * Step 1: Choose paper + blueprint for slot filling.
     * Step 2 (when ?blueprint_id=X): render the slot-filling form.
     */
    public function fillFromTemplate(): void
    {
        $paperId = (int) $this->request->get('paper_id', '0');
        $blueprintId = (int) $this->request->get('blueprint_id', '0');

        $papers = $this->db->fetchAll(
            "SELECT p.id, p.title, s.name as subject_name,
                    (SELECT COUNT(*) FROM paper_templates pt WHERE pt.paper_id = p.id) as bp_count
             FROM papers p LEFT JOIN subjects s ON p.subject_id = s.id
             ORDER BY p.created_at DESC"
        );

        $paper = $paperId ? $this->db->fetch(
            'SELECT p.*, s.name as subject_name FROM papers p LEFT JOIN subjects s ON p.subject_id = s.id WHERE p.id = ?',
            [$paperId]
        ) : null;

        $blueprints = $paperId ? $this->db->fetchAll(
            'SELECT id, name, status, blueprint_json, version FROM paper_templates WHERE paper_id = ? ORDER BY updated_at DESC',
            [$paperId]
        ) : [];

        $blueprint = null;
        $annotatedSchema = null;
        if ($blueprintId) {
            $blueprint = $this->db->fetch('SELECT * FROM paper_templates WHERE id = ?', [$blueprintId]);
            if ($blueprint) {
                $annotatedSchema = self::buildAnnotatedSchema(
                    json_decode($blueprint['blueprint_json'], true) ?: ['sections' => []]
                );
            }
        }

        $this->view('admin.questions.fill', [
            'pageTitle' => 'Fill Questions from Template',
            'currentNav' => 'questions',
            'papers' => $papers,
            'paper' => $paper,
            'blueprints' => $blueprints,
            'blueprint' => $blueprint,
            'annotatedSchema' => $annotatedSchema,
            'questionTypes' => self::questionTypes(),
        ], 'admin');
    }

    /**
     * Bulk-save questions filled into a blueprint's slots.
     * Body: { paper_id, blueprint_id, sections: [{ id, blocks: [{ id, type, instances: [questionData...] }] }] }
     */
    public function saveFromTemplate(): void
    {
        $this->validateCsrf();

        $paperId = (int) $this->request->post('paper_id', '0');
        $blueprintId = (int) $this->request->post('blueprint_id', '0');
        $sections = $this->request->post('sections', []);

        if (!$paperId || !is_array($sections)) {
            $this->json(['error' => 'paper_id and sections are required.'], 422);
            return;
        }

        $paper = $this->db->fetch('SELECT id FROM papers WHERE id = ?', [$paperId]);
        if (!$paper) {
            $this->json(['error' => 'Paper not found.'], 404);
            return;
        }

        $imported = 0;
        $failed = 0;

        $this->db->transaction(function ($db) use ($sections, $paperId, $blueprintId, &$imported, &$failed) {
            $maxSort = (int) ($db->fetch(
                'SELECT COALESCE(MAX(sort_order), 0) as m FROM questions WHERE paper_id = ? AND parent_id IS NULL',
                [$paperId]
            )['m'] ?? 0);

            foreach ($sections as $sec) {
                $sectionKey = $sec['id'] ?? null;
                foreach (($sec['blocks'] ?? []) as $block) {
                    $blockType = $block['type'] ?? 'single_choice';
                    foreach (($block['instances'] ?? []) as $q) {
                        try {
                            $q['paper_id'] = $paperId;
                            $q['section_key'] = $sectionKey;
                            $q['type'] = $q['type'] ?? $blockType;
                            $q['score'] = $q['score'] ?? ($block['score'] ?? 0);
                            $row = $this->buildRow($q);
                            $row['sort_order'] = ++$maxSort;
                            $row['created_by'] = Auth::id();
                            $row['created_at'] = date('Y-m-d H:i:s');
                            $row['updated_at'] = date('Y-m-d H:i:s');
                            $qid = $db->insert('questions', $row);

                            if (!empty($q['children'])) {
                                $this->saveChildren($qid, $paperId, $q['children']);
                            }
                            $imported++;
                        } catch (\Throwable $e) {
                            $failed++;
                        }
                    }
                }
            }
        });

        $this->updatePaperQuestionCount($paperId);

        $this->json([
            'success' => true,
            'imported' => $imported,
            'failed' => $failed,
            'message' => "Imported {$imported} questions" . ($failed ? ", {$failed} failed" : '') . '.',
        ]);
    }

    /**
     * Build a richly-commented JSON schema describing exactly what to fill
     * for each block in a blueprint. Meant to be copy-pasted to an AI.
     */
    public static function buildAnnotatedSchema(array $blueprint): array
    {
        // Per-type field schema for AI filling. Keep shapes aligned with
        // buildRow() and the fill.php / papers/fill.php editor expectations.
        $typeHelp = [
            'single_choice' => [
                'stem' => 'Question stem (plain text or HTML).',
                'options' => ['A. option text', 'B. option text', 'C. option text', 'D. option text'],
                'answer' => 'A',
                'analysis' => 'Explanation shown after answering.',
            ],
            'multi_choice' => [
                'stem' => 'Question stem.',
                'options' => ['A. ...', 'B. ...', 'C. ...', 'D. ...'],
                'answer' => ['A', 'C'],
                'analysis' => 'Explanation.',
            ],
            'true_false' => [
                'stem' => 'Statement to judge.',
                'answer' => true,
                'analysis' => 'Explanation.',
            ],
            'fill_blank' => [
                'stem' => 'Sentence with ____ marking each blank (use 4 underscores).',
                'answer' => ['blank1_text', 'blank2_text'],
                'analysis' => 'Explanation.',
            ],
            'short_answer' => [
                'stem' => 'Open question.',
                'answer' => 'Reference answer text.',
                'analysis' => 'Scoring key points.',
            ],
            'writing' => [
                'stem' => 'Writing prompt / task description.',
                'word_count' => 150,
                'answer' => 'Sample essay (optional).',
                'analysis' => 'Rubric, outline, or sample commentary.',
            ],
            'translation' => [
                'stem' => 'Source text to translate.',
                'answer' => 'Reference translation.',
                'analysis' => 'Notes on key phrases.',
            ],
            'cloze' => [
                'passage' => 'Paragraph with ____ marking each blank.',
                'sub_items' => [
                    ['stem' => 'Context for blank 1', 'options' => ['A','B','C','D'], 'answer' => 'A', 'analysis' => '...'],
                ],
            ],
            'reading_material' => [
                'title' => 'Optional passage title.',
                'source' => 'Optional citation / source.',
                'passage' => 'Full reading passage.',
                'children' => [
                    ['type' => 'single_choice', 'stem' => '...', 'options' => ['A. ...','B. ...','C. ...','D. ...'], 'answer' => 'A', 'analysis' => '...'],
                    ['type' => 'short_answer', 'stem' => '...', 'answer' => '...', 'analysis' => '...'],
                ],
            ],
            'listening_material' => [
                'audio_url' => 'https://example.com/audio.mp3',
                'audio_duration' => 120,
                'transcript' => 'Optional transcript shown after answering.',
                'children' => [
                    ['type' => 'single_choice', 'stem' => '...', 'options' => ['A. ...','B. ...','C. ...','D. ...'], 'answer' => 'A', 'analysis' => '...'],
                ],
            ],
            'matching' => [
                'stem' => 'Match the items.',
                'pairs' => [['left' => 'A', 'right' => '1'], ['left' => 'B', 'right' => '2']],
                'answer' => ['A' => '1', 'B' => '2'],
                'analysis' => 'Explanation.',
            ],
            'ordering' => [
                'stem' => 'Arrange the items in the correct order.',
                'items' => ['step 1 text', 'step 2 text', 'step 3 text'],
                'answer' => [0, 1, 2],
                'analysis' => 'Explanation.',
            ],
            'description' => [
                '_note' => 'Non-question block: Markdown instructions/notes. Output ONE instance only (count is always 1).',
                'content' => "# Section Notes\nMarkdown-formatted description shown to test takers.",
                'format' => 'markdown',
            ],
            'media' => [
                '_note' => 'Non-question block: media asset. Output ONE instance only.',
                'media_type' => 'image | audio | link',
                'url' => 'https://example.com/asset.ext',
                'caption' => 'Optional caption.',
                'link_url' => 'Optional click-through link (for link type).',
            ],
        ];

        $out = [
            '_instructions' => implode(' ', [
                'Fill the "instances" array of each block with real question objects.',
                'Each instance MUST match the schema shown under "_example" for that block type.',
                'Respect the "count" field: produce exactly that many instances per block.',
                'For "reading_material" and "listening_material", every entry in "children" MUST include its own "type" field.',
                'For "description" and "media" blocks, output ONE instance (these are static content, not quizzable).',
                'Output valid JSON only — no prose, no markdown fences.',
            ]),
            '_common_fields' => [
                'score' => 'Float — points for this question; defaults to block.score.',
                'difficulty' => 'Integer 1–5.',
                'tags' => ['optional', 'string', 'array'],
            ],
            'sections' => [],
        ];

        foreach ($blueprint['sections'] ?? [] as $sec) {
            $sType = $sec['type'] ?? 'default';
            if ($sType === 'part') {
                continue; // part markers produce no questions
            }
            $sNode = [
                '_note' => 'Section: ' . ($sec['title'] ?? '') . ' (type=' . $sType . ')',
                'id' => $sec['id'] ?? '',
                'title' => $sec['title'] ?? '',
                'type' => $sType,
                'instructions' => $sec['instructions'] ?? '',
                'blocks' => [],
            ];
            foreach ($sec['blocks'] ?? [] as $block) {
                $bt = $block['type'] ?? 'single_choice';
                $count = (int) ($block['count'] ?? 1);
                if (in_array($bt, ['description', 'media'], true)) {
                    $count = 1;
                }
                $example = $typeHelp[$bt] ?? ['stem' => '...', 'answer' => '...', 'analysis' => '...'];
                $bNode = [
                    '_note' => "Block type={$bt}, produce {$count} instance(s)"
                        . (isset($block['score']) ? ", {$block['score']} pts each" : ''),
                    'id' => $block['id'] ?? '',
                    'type' => $bt,
                    'count' => $count,
                    'score' => $block['score'] ?? 0,
                    'difficulty' => $block['difficulty'] ?? 3,
                    '_example' => $example,
                    'instances' => array_fill(0, max(1, $count), $example),
                ];
                $sNode['blocks'][] = $bNode;
            }
            $out['sections'][] = $sNode;
        }

        return $out;
    }

    // ----------------------------------------------------------

    private function validateQuestion(array $data): array
    {
        $errors = [];
        if (empty($data['paper_id'])) {
            $errors[] = 'Paper is required.';
        }
        if (empty($data['type'])) {
            $errors[] = 'Question type is required.';
        }
        if (empty($data['content_json']) && empty($data['stem'])) {
            $errors[] = 'Question content is required.';
        }
        return $errors;
    }

    private function buildRow(array $data): array
    {
        $contentJson = $data['content_json'] ?? null;
        if (!$contentJson) {
            // Whitelist of fields that belong inside content_json, covering every
            // block type produced by the AI schema / template editor.
            $contentKeys = [
                'stem', 'options', 'passage', 'title', 'source',
                'audio_url', 'transcript', 'instructions', 'word_count',
                'pairs', 'items', 'sub_items',
                'media_type', 'url', 'file', 'caption', 'link_url',
                'content', 'format',
            ];
            $content = [];
            foreach ($contentKeys as $k) {
                if (array_key_exists($k, $data) && $data[$k] !== null && $data[$k] !== '') {
                    $content[$k] = $data[$k];
                }
            }
            $contentJson = json_encode($content, JSON_UNESCAPED_UNICODE);
        } elseif (is_array($contentJson)) {
            $contentJson = json_encode($contentJson, JSON_UNESCAPED_UNICODE);
        }

        $answerJson = $data['answer_json'] ?? $data['answer'] ?? null;
        if (is_array($answerJson) || is_bool($answerJson)) {
            $answerJson = json_encode($answerJson, JSON_UNESCAPED_UNICODE);
        }

        $analysisJson = $data['analysis_json'] ?? $data['analysis'] ?? null;
        if (is_array($analysisJson)) {
            $analysisJson = json_encode($analysisJson, JSON_UNESCAPED_UNICODE);
        }

        return [
            'paper_id' => (int) $data['paper_id'],
            'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            'section_key' => $data['section_key'] ?? null,
            'type' => $data['type'],
            'content_json' => $contentJson,
            'answer_json' => $answerJson,
            'analysis_json' => $analysisJson,
            'score' => (float) ($data['score'] ?? 0),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'difficulty' => (int) ($data['difficulty'] ?? 3),
            'tags' => is_array($data['tags'] ?? null) ? json_encode($data['tags']) : ($data['tags'] ?? null),
            'audio_url' => $data['audio_url'] ?? null,
            'audio_duration' => !empty($data['audio_duration']) ? (int) $data['audio_duration'] : null,
            'status' => $data['status'] ?? 'draft',
        ];
    }

    private function saveChildren(int $parentId, int $paperId, array $children): void
    {
        foreach ($children as $i => $child) {
            $child['paper_id'] = $paperId;
            $child['parent_id'] = $parentId;
            $child['sort_order'] = $i + 1;
            $row = $this->buildRow($child);
            $row['created_by'] = Auth::id();
            $row['created_at'] = date('Y-m-d H:i:s');
            $row['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert('questions', $row);
        }
    }

    private function updatePaperQuestionCount(int $paperId): void
    {
        $count = $this->db->count('questions', 'paper_id = ? AND parent_id IS NULL', [$paperId]);
        $this->db->update('papers', [
            'question_count' => $count,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$paperId]);
    }

    public static function questionTypes(): array
    {
        return [
            'single_choice' => 'Single Choice',
            'multi_choice' => 'Multiple Choice',
            'true_false' => 'True / False',
            'fill_blank' => 'Fill in the Blank',
            'short_answer' => 'Short Answer',
            'writing' => 'Writing / Essay',
            'translation' => 'Translation',
            'cloze' => 'Cloze',
            'reading_material' => 'Reading Comprehension',
            'listening_material' => 'Listening Comprehension',
            'matching' => 'Matching',
            'ordering' => 'Ordering',
        ];
    }
}
