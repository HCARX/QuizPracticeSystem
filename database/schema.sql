-- Quiz Practice System - Database Schema
-- SQLite 3 with WAL mode and strict foreign keys

PRAGMA journal_mode = WAL;
PRAGMA foreign_keys = ON;

-- ============================================================
-- 1. Users & Authentication
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100),
    avatar VARCHAR(255),
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    -- Roles: super_admin, admin, editor, reviewer, vip, user
    status INTEGER NOT NULL DEFAULT 1,
    -- 1: active, 0: disabled
    last_login_at DATETIME,
    last_login_ip VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);

-- ============================================================
-- 2. Exam Subjects (考试科目)
-- ============================================================

CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    alias VARCHAR(50),
    description TEXT,
    cover_color VARCHAR(20) DEFAULT '#4F46E5',
    icon VARCHAR(50),
    sort_order INTEGER DEFAULT 0,
    status INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_subjects_status ON subjects(status);
CREATE INDEX idx_subjects_sort ON subjects(sort_order);

-- ============================================================
-- 3. Papers (试卷)
-- ============================================================

CREATE TABLE IF NOT EXISTS papers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    subtitle VARCHAR(200),
    year VARCHAR(10),
    month VARCHAR(10),
    batch VARCHAR(50),
    source VARCHAR(100),
    description TEXT,
    difficulty INTEGER DEFAULT 3,
    -- 1-5 scale
    duration INTEGER DEFAULT 120,
    -- minutes
    total_score DECIMAL(8,2) DEFAULT 0,
    pass_score DECIMAL(8,2) DEFAULT 0,
    question_count INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    -- draft, published, archived
    published_at DATETIME,
    created_by INTEGER,
    updated_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE INDEX idx_papers_subject ON papers(subject_id);
CREATE INDEX idx_papers_status ON papers(status);
CREATE INDEX idx_papers_year ON papers(year);

-- ============================================================
-- 4. Template Modules (公共模块池)
-- ============================================================

CREATE TABLE IF NOT EXISTS template_modules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    -- title, instruction, passage, audio_instruction, writing_prompt, section_header
    content TEXT NOT NULL,
    content_format VARCHAR(20) DEFAULT 'text',
    -- text, html, markdown
    tags TEXT,
    -- JSON array of tags
    is_favorite INTEGER DEFAULT 0,
    usage_count INTEGER DEFAULT 0,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE INDEX idx_template_modules_category ON template_modules(category);

-- ============================================================
-- 5. Paper Templates (试卷模板骨架)
-- ============================================================

CREATE TABLE IF NOT EXISTS paper_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paper_id INTEGER NOT NULL,
    name VARCHAR(200) NOT NULL,
    version INTEGER DEFAULT 1,
    blueprint_json TEXT NOT NULL,
    -- The core template schema (sections, blocks, question slots)
    -- Structure: { sections: [{ type, title, blocks: [{ type, config, children }] }] }
    status VARCHAR(20) DEFAULT 'draft',
    -- draft, active, archived
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paper_id) REFERENCES papers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE INDEX idx_paper_templates_paper ON paper_templates(paper_id);

-- ============================================================
-- 6. Questions (题目 - 树形结构)
-- ============================================================

CREATE TABLE IF NOT EXISTS questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paper_id INTEGER NOT NULL,
    parent_id INTEGER DEFAULT NULL,
    -- For nested structures: reading_material -> sub_questions
    section_key VARCHAR(50),
    -- References a section in the template blueprint
    type VARCHAR(30) NOT NULL,
    -- single_choice, multi_choice, fill_blank, true_false,
    -- reading_material, listening_material, writing, translation,
    -- cloze, short_answer, matching, ordering
    content_json TEXT NOT NULL,
    -- Flexible JSON: stem, options, passage, audio_url, instructions, etc.
    answer_json TEXT,
    -- Standard answer(s): string, array, or structured object
    analysis_json TEXT,
    -- Explanation: { text, key_points[], common_mistakes, ai_analysis }
    score DECIMAL(6,2) DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    difficulty INTEGER DEFAULT 3,
    tags TEXT,
    -- JSON array: knowledge points, labels
    audio_url VARCHAR(500),
    audio_duration INTEGER,
    -- seconds
    status VARCHAR(20) DEFAULT 'draft',
    -- draft, reviewing, published, archived
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paper_id) REFERENCES papers(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE INDEX idx_questions_paper ON questions(paper_id);
CREATE INDEX idx_questions_parent ON questions(parent_id);
CREATE INDEX idx_questions_type ON questions(type);
CREATE INDEX idx_questions_section ON questions(section_key);
CREATE INDEX idx_questions_status ON questions(status);

-- ============================================================
-- 7. Practice Sessions (答题记录)
-- ============================================================

CREATE TABLE IF NOT EXISTS practice_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    paper_id INTEGER NOT NULL,
    mode VARCHAR(20) DEFAULT 'exam',
    -- exam (整卷), practice (专项), review (复习)
    status VARCHAR(20) DEFAULT 'ongoing',
    -- ongoing, completed, abandoned
    total_score DECIMAL(8,2) DEFAULT 0,
    max_score DECIMAL(8,2) DEFAULT 0,
    correct_count INTEGER DEFAULT 0,
    wrong_count INTEGER DEFAULT 0,
    unanswered_count INTEGER DEFAULT 0,
    accuracy DECIMAL(5,2) DEFAULT 0,
    -- percentage
    time_spent INTEGER DEFAULT 0,
    -- seconds
    answers_json TEXT,
    -- { question_id: { answer, is_correct, score, time_spent } }
    result_json TEXT,
    -- Summary: per-section scores, weak areas, etc.
    settings_json TEXT,
    -- Practice settings: question_types, difficulty, count, shuffle
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (paper_id) REFERENCES papers(id) ON DELETE RESTRICT
);

CREATE INDEX idx_practice_user ON practice_sessions(user_id);
CREATE INDEX idx_practice_paper ON practice_sessions(paper_id);
CREATE INDEX idx_practice_status ON practice_sessions(status);

-- ============================================================
-- 8. User Answer Details (每题答案明细)
-- ============================================================

CREATE TABLE IF NOT EXISTS user_answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    user_answer TEXT,
    -- The user's raw answer
    is_correct INTEGER DEFAULT 0,
    score DECIMAL(6,2) DEFAULT 0,
    time_spent INTEGER DEFAULT 0,
    -- seconds on this question
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES practice_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE INDEX idx_user_answers_session ON user_answers(session_id);
CREATE INDEX idx_user_answers_question ON user_answers(question_id);

-- ============================================================
-- 9. User Favorites (收藏)
-- ============================================================

CREATE TABLE IF NOT EXISTS user_favorites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, question_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- ============================================================
-- 10. User Mistakes (错题本)
-- ============================================================

CREATE TABLE IF NOT EXISTS user_mistakes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    wrong_count INTEGER DEFAULT 1,
    last_wrong_answer TEXT,
    last_practiced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    mastered INTEGER DEFAULT 0,
    UNIQUE(user_id, question_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE INDEX idx_mistakes_user ON user_mistakes(user_id);

-- ============================================================
-- 11. User Vocabulary (生词本)
-- ============================================================

CREATE TABLE IF NOT EXISTS user_vocabularies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    word VARCHAR(100) NOT NULL,
    phonetic VARCHAR(100),
    context_sentence TEXT,
    context_source VARCHAR(200),
    meaning_json TEXT,
    -- { pos, definition, usage, examples[], context_meaning }
    status VARCHAR(20) DEFAULT 'unseen',
    -- unseen, fuzzy, mastered
    review_count INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_vocab_user ON user_vocabularies(user_id);
CREATE INDEX idx_vocab_status ON user_vocabularies(status);
CREATE UNIQUE INDEX idx_vocab_user_word ON user_vocabularies(user_id, word);

-- ============================================================
-- 12. AI Configuration
-- ============================================================

CREATE TABLE IF NOT EXISTS ai_models (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    model_id VARCHAR(100) NOT NULL,
    -- actual API model identifier
    provider VARCHAR(50) DEFAULT 'openai',
    description TEXT,
    sort_order INTEGER DEFAULT 0,
    status INTEGER DEFAULT 1,
    allowed_roles TEXT DEFAULT '["super_admin","admin"]',
    -- JSON array of roles
    rate_limit_json TEXT,
    -- { "user": 10, "vip": 50, "admin": -1 } per day
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai_prompts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    scene VARCHAR(50) NOT NULL UNIQUE,
    -- word_explain, question_analysis, question_generate, translate, writing_review
    name VARCHAR(100) NOT NULL,
    description TEXT,
    system_prompt TEXT,
    user_prompt_template TEXT,
    -- Supports {{variable}} placeholders
    variables_json TEXT,
    -- Description of available variables
    applicable_types TEXT,
    -- JSON array of question types this prompt applies to
    default_model_id INTEGER,
    status INTEGER DEFAULT 1,
    version INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (default_model_id) REFERENCES ai_models(id)
);

CREATE TABLE IF NOT EXISTS ai_provider_config (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    provider VARCHAR(50) NOT NULL UNIQUE,
    base_url VARCHAR(500) NOT NULL,
    api_key_encrypted VARCHAR(500),
    default_model VARCHAR(100),
    timeout INTEGER DEFAULT 30,
    temperature DECIMAL(3,2) DEFAULT 0.70,
    max_tokens INTEGER DEFAULT 2000,
    retry_count INTEGER DEFAULT 2,
    system_prompt TEXT,
    status INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 13. AI Call Logs
-- ============================================================

CREATE TABLE IF NOT EXISTS ai_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    scene VARCHAR(50),
    model VARCHAR(100),
    prompt_tokens INTEGER DEFAULT 0,
    completion_tokens INTEGER DEFAULT 0,
    total_tokens INTEGER DEFAULT 0,
    response_time INTEGER DEFAULT 0,
    -- milliseconds
    status VARCHAR(20) DEFAULT 'success',
    -- success, error
    error_message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX idx_ai_logs_user ON ai_logs(user_id);
CREATE INDEX idx_ai_logs_scene ON ai_logs(scene);
CREATE INDEX idx_ai_logs_created ON ai_logs(created_at);

-- ============================================================
-- 14. Tags & Knowledge Points
-- ============================================================

CREATE TABLE IF NOT EXISTS tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL,
    category VARCHAR(30) DEFAULT 'general',
    -- question_type, knowledge, difficulty, source, frequency
    color VARCHAR(20),
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX idx_tags_name_cat ON tags(name, category);

-- ============================================================
-- 15. Operation Logs (审计日志)
-- ============================================================

CREATE TABLE IF NOT EXISTS operation_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action VARCHAR(50) NOT NULL,
    target_type VARCHAR(50),
    target_id INTEGER,
    detail TEXT,
    ip VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX idx_op_logs_user ON operation_logs(user_id);
CREATE INDEX idx_op_logs_created ON operation_logs(created_at);

-- ============================================================
-- 16. System Settings (Key-Value)
-- ============================================================

CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    group_name VARCHAR(50) DEFAULT 'general',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Seed Data
-- ============================================================

-- Default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, display_name, role, status)
VALUES ('admin', 'admin@example.com', '$2y$12$0m.M.SD3Ouzyu9NuWZz52O6F9M8PJ.4q.i5HA20jeaLHYxz8wU/g2', 'Administrator', 'super_admin', 1);

-- Default exam subjects
INSERT INTO subjects (name, alias, description, cover_color, sort_order, status) VALUES
('CET-4', '英语四级', 'College English Test Band 4', '#4F46E5', 1, 1),
('CET-6', '英语六级', 'College English Test Band 6', '#7C3AED', 2, 1),
('NETEM', '考研英语', 'National Entrance Test of English for MA/MS Candidates', '#2563EB', 3, 1),
('IELTS', '雅思', 'International English Language Testing System', '#DC2626', 4, 1),
('TOEFL', '托福', 'Test of English as a Foreign Language', '#059669', 5, 1);

-- Default AI models
INSERT INTO ai_models (name, model_id, provider, description, sort_order, status, allowed_roles) VALUES
('GPT-4o', 'gpt-4o', 'openai', 'Most capable model for complex analysis', 1, 1, '["super_admin","admin","vip"]'),
('GPT-4.1', 'gpt-4.1', 'openai', 'Latest GPT-4 series model', 2, 1, '["super_admin","admin","vip"]'),
('GPT-4o-mini', 'gpt-4o-mini', 'openai', 'Fast and efficient for simpler tasks', 3, 1, '["super_admin","admin","vip","user"]');

-- Default AI prompts
INSERT INTO ai_prompts (scene, name, description, system_prompt, user_prompt_template, variables_json, applicable_types, status) VALUES
('word_explain', '划词解释', 'Contextual word/phrase explanation during reading',
 'You are an expert English language tutor. Explain words and phrases in the context they appear, not just dictionary definitions. Be concise but thorough. Always respond in a structured format.',
 'The word/phrase "{{word}}" appears in this sentence:\n\n"{{sentence}}"\n\nPlease explain:\n1. Meaning in this context\n2. Part of speech\n3. Common usage\n4. A brief example sentence',
 '[{"name":"word","desc":"The selected word or phrase"},{"name":"sentence","desc":"The sentence containing the word"}]',
 NULL, 1),

('question_analysis', '全题解析', 'Deep analysis of a question with explanation',
 'You are a professional exam tutor. Provide detailed, structured analysis of exam questions. Be thorough but clear.',
 'Please analyze this {{question_type}} question:\n\nQuestion: {{stem}}\n{{#options}}\nOptions:\n{{options}}\n{{/options}}\n\nCorrect Answer: {{answer}}\n\nProvide:\n1. Question Analysis\n2. Key Knowledge Points\n3. Why the correct answer is right\n4. Why other options are wrong (if applicable)\n5. Problem-solving approach\n6. Common mistakes to avoid',
 '[{"name":"question_type","desc":"Type of question"},{"name":"stem","desc":"Question stem"},{"name":"options","desc":"Answer options if any"},{"name":"answer","desc":"Correct answer"}]',
 '["single_choice","multi_choice","fill_blank","true_false","reading_material","cloze"]', 1),

('question_generate', '题目生成', 'Generate questions from template schema',
 'You are an exam question generator. Generate high-quality, realistic exam questions following the exact JSON schema provided. Ensure questions are appropriate for the specified exam level.',
 'Generate exam questions following this JSON schema:\n\n{{schema}}\n\nExam type: {{exam_type}}\nDifficulty: {{difficulty}}/5\n\nPlease fill in all required fields with realistic, high-quality content.',
 '[{"name":"schema","desc":"JSON schema defining the structure"},{"name":"exam_type","desc":"Type of exam"},{"name":"difficulty","desc":"Difficulty level 1-5"}]',
 NULL, 1);

-- Default AI provider config
INSERT INTO ai_provider_config (provider, base_url, default_model, status) VALUES
('openai', 'https://api.openai.com/v1', 'gpt-4o', 1);

-- Default system settings
INSERT INTO settings (key, value, group_name) VALUES
('site_name', 'Quiz Practice System', 'general'),
('site_description', 'Online English Exam Practice Platform', 'general'),
('items_per_page', '20', 'general'),
('ai_enabled', '1', 'ai'),
('ai_daily_limit_user', '10', 'ai'),
('ai_daily_limit_vip', '50', 'ai');

-- ============================================================
-- Password Resets
-- ============================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_password_resets_token ON password_resets(token);
