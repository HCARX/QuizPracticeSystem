# Quiz Practice System

> A modern, AI-powered online quiz and exam practice platform built with native PHP 8.1+, SQLite, and Tailwind CSS.
>
> 📖 **[中文文档 / Chinese README](README.zh-CN.md)**

**Repository:** https://github.com/HCARX/QuizPracticeSystem

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)

---

## ✨ Features

### 📚 Exam Content Management
- **Subjects & Papers** — Hierarchical organization of exam subjects and papers
- **Rich Blueprint Editor** — Visual paper template builder with sections, blocks, and scoring
- **14 Question Types** — Single choice, multi choice, true/false, fill-in-blank, short answer, writing, translation, cloze, reading material (parent/child), listening material (parent/child), matching, ordering, description, and media embeds
- **Bilingual Support** — Built-in i18n for Simplified Chinese (`zh`) and English (`en`)

### 🤖 AI-Assisted Authoring
- **OpenAI-Compatible Integration** — Configure any OpenAI-API-compatible provider (OpenAI, Azure, DeepSeek, Moonshot, local LLMs, etc.)
- **Multi-Model Whitelist** — Admin-managed model catalog with per-role access control
- **Custom Prompt Templates** — Scene-based system/user prompts with variable interpolation
- **Schema-Guided Generation** — AI receives an annotated JSON schema tailored to each block type, ensuring complete and well-structured output
- **Comprehensive Logging** — Token usage, response time, and error tracking per request

### ✍️ Paper Authoring Workflows
- **Slot-by-Slot Filler** — Step through every question slot defined by a template, with live preview
- **AI Bulk-Fill** — Generate an entire paper from a template blueprint in one call
- **Draft / Published States** — Safe iteration before releasing to learners
- **Auto-Scored Types** — Objective questions graded instantly; subjective types flagged for review

### 👥 Users & Permissions
- **Role-Based Access Control** — `super_admin`, `admin`, `editor`, `reviewer`, `vip`, `user`
- **Session Auth** — Secure login with CSRF protection on all mutating endpoints
- **Activity Trails** — Last-login timestamp and IP per user

### 🎨 Frontend
- **Responsive Tailwind UI** — Modern, clean admin dashboard and learner views
- **Zero Build Step** — Plain PHP views with CDN Tailwind; no Node toolchain required
- **Lightweight JS Helpers** — `QS.fetch`, `QS.toast`, `QS.confirm` for consistent UX

---

## 🏗️ Architecture

```
QuizPracticeSystem/
├── app/
│   ├── Controllers/       # HTTP controllers (Admin/, Api/, Web/)
│   ├── Core/              # Framework: Router, Controller, Auth, DB, Request, Response, View
│   ├── DTOs/              # Data transfer objects
│   ├── Middleware/        # Auth / RBAC / CSRF middleware
│   ├── Repositories/      # Query layer for core tables
│   └── Services/          # Business logic (AiService, PaperService, etc.)
├── config/
│   ├── app.php            # App-wide config
│   ├── database.php       # SQLite path & options
│   ├── ai.php             # AI defaults
│   └── routes.php         # Route table
├── database/
│   ├── schema.sql         # Full DDL
│   └── quiz_system.sqlite # SQLite database file
├── public/
│   ├── index.php          # Front controller
│   ├── assets/            # CSS/JS/images
│   └── uploads/           # User-uploaded media
├── resources/
│   ├── lang/              # zh.php / en.php translation tables
│   └── views/             # PHP templates (admin/, web/, layout/)
└── storage/               # Logs, cache, temp files
```

### Core Concepts

- **Routing** — Declarative in `config/routes.php`; dispatched by `App\Core\Router`.
- **Blueprint JSON** — Each paper template stores a `blueprint_json` with `sections → blocks` describing question layout, counts, and scores.
- **Content JSON** — Each question stores three JSON columns: `content_json` (stem, options, media, ...), `answer_json`, and `analysis_json`.
- **Part Markers** — Sections with `type === "part"` act as headings (e.g. *Listening Part 1*) and do not generate question slots.

---

## 🚀 Quick Start

### Prerequisites
- PHP **8.1+** with extensions: `pdo_sqlite`, `json`, `mbstring`, `openssl`, `curl`
- Composer 2.x
- A writable `database/` and `storage/` directory

### Installation

```bash
# 1. Clone
git clone https://github.com/HCARX/QuizPracticeSystem.git
cd QuizPracticeSystem

# 2. Install autoloader (no runtime deps required)
composer install

# 3. Copy environment template (optional)
cp .env.example .env

# 4. One-shot install: init DB + seed super-admin
php bin/install.php
# or with custom credentials:
# php bin/install.php --username=admin --password='S3cret!' --name="Admin"

# 5. Start the built-in PHP server
php -S 127.0.0.1:8080 -t public
```

Open **http://127.0.0.1:8080** in your browser.

### Default Admin Account

On a fresh install, seed a **super-admin** user by running:

```bash
php -r "require 'vendor/autoload.php'; \
  \$pdo = new PDO('sqlite:database/quiz_system.sqlite'); \
  \$hash = password_hash('admin123', PASSWORD_DEFAULT); \
  \$pdo->prepare('INSERT INTO users (username,password_hash,display_name,role,status) VALUES (?,?,?,?,1)') \
      ->execute(['admin', \$hash, 'Administrator', 'super_admin']);"
```

| Field       | Value                 |
|-------------|-----------------------|
| URL         | `/login`              |
| Username    | `admin`               |
| Password    | `admin123`            |
| Role        | `super_admin`         |

> ⚠️ **Change the password immediately after your first login** via *Admin → Profile*.

---

## 🔁 URL Rewriting (Pretty URLs)

The project ships with a front controller at `public/index.php`. For clean URLs (e.g. `/admin/papers` instead of `/index.php?...`), enable URL rewriting on your web server. A working `.htaccess` is included at `public/.htaccess`.

### Apache

Make sure `mod_rewrite` is enabled and the virtual host allows `.htaccess` overrides:

```apache
<VirtualHost *:80>
    ServerName quiz.example.com
    DocumentRoot /var/www/QuizPracticeSystem/public

    <Directory /var/www/QuizPracticeSystem/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Bundled `public/.htaccess`:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Nginx

```nginx
server {
    listen 80;
    server_name quiz.example.com;
    root /var/www/QuizPracticeSystem/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }

    # Deny access to sensitive folders
    location ~ ^/(app|config|database|storage|resources|vendor)/ {
        deny all;
        return 404;
    }
}
```

### Caddy

```caddyfile
quiz.example.com {
    root * /var/www/QuizPracticeSystem/public
    php_fastcgi unix//run/php/php8.1-fpm.sock
    file_server
    try_files {path} {path}/ /index.php?{query}
}
```

### IIS (web.config)

```xml
<configuration>
  <system.webServer>
    <rewrite>
      <rules>
        <rule name="Front Controller" stopProcessing="true">
          <match url="^(.*)$" ignoreCase="false" />
          <conditions logicalGrouping="MatchAll">
            <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
          </conditions>
          <action type="Rewrite" url="index.php" />
        </rule>
      </rules>
    </rewrite>
  </system.webServer>
</configuration>
```

> **宝塔面板 / BT Panel users:** In the site settings open *伪静态 (URL Rewrite)* and paste the Nginx or Apache block above, matching whichever web server your site uses.

---

## ⚙️ Configuration

### App (`config/app.php`)
```php
return [
    'name'     => 'Quiz Practice System',
    'debug'    => true,              // set false in production
    'timezone' => 'Asia/Shanghai',
    'url'      => 'http://localhost:8080',
];
```

### Database (`config/database.php`)
Points to `database/quiz_system.sqlite` by default. Override via `DB_PATH` env var if desired.

### AI Provider
Configure via **Admin → AI Settings** (no file editing required):
1. Set **Base URL** (e.g. `https://api.openai.com/v1`) and **API Key**.
2. Add one or more **Models** with `model_id`, display name, and `allowed_roles`.
3. Enable **Prompt Templates** for scenes like `paper_fill`, `question_polish`, etc.

All calls are logged to the `ai_logs` table and surfaced on the AI Settings page.

---

## 🧭 Usage Guide

### 1. Create a Subject
`Admin → Subjects → New` — Give it a name, color, and icon.

### 2. Create a Paper + Template
`Admin → Papers → New` — Choose the subject, then build a **Template** describing the paper's structure (sections, blocks, counts, scores). Templates are reusable.

### 3. Fill Questions
Two workflows are supported:

- **Manual Slot Filler** — `Papers → Pick Template → Start` walks you through each slot with the appropriate form (choice options, passage + sub-questions, matching pairs, media URL, ...).
- **AI Generation** — On the same page, click **AI Fill**. The system sends the template blueprint plus an annotated schema to your configured model and populates all slots at once. Review and save.

### 4. Publish
Once all questions are in `status = draft` and reviewed, set the paper to `published` to make it visible to learners.

### 5. Learner View
Users see published papers under their subject list and can start an attempt. Objective answers are graded automatically; subjective responses await manual review.

---

## 🔌 REST-ish Endpoints (selected)

| Method | Path                                      | Purpose                          |
|-------:|-------------------------------------------|----------------------------------|
| GET    | `/admin/papers`                           | List papers                      |
| GET    | `/admin/papers/{id}/pick-template`        | Choose a template for filling    |
| GET    | `/admin/papers/{paperId}/fill/{tplId}`    | Slot-by-slot filler              |
| POST   | `/admin/papers/{paperId}/fill/{tplId}/{slotIndex}` | Save one slot           |
| POST   | `/admin/ai/generate-paper`                | AI bulk-fill a paper             |
| POST   | `/admin/ai/provider`                      | Save provider config             |
| POST   | `/admin/ai/models`                        | Add model                        |
| POST   | `/admin/ai/models/{id}/update`            | Update model                     |
| POST   | `/admin/ai/models/{id}/toggle`            | Enable/disable                   |
| POST   | `/admin/ai/models/{id}/delete`            | Delete model                     |

All `POST` routes require a valid CSRF token.

---

## 🌐 Internationalization

Translations live in `resources/lang/{zh,en}.php` as flat dot-keyed arrays. In views and controllers:

```php
<?= $t('admin.papers.pick_template') ?>
```

To add a language, copy `en.php` → `xx.php`, translate the values, and register it in `config/app.php`.

---

## 🔒 Security Notes

- Passwords hashed with `password_hash()` (bcrypt).
- CSRF tokens on every state-changing POST.
- SQL access exclusively via prepared statements (`App\Core\Database`).
- File uploads validated by MIME + extension; served from `/public/uploads`.
- **Do not** commit `database/quiz_system.sqlite` with real user data. Add it to `.gitignore` before going public.

---

## 🧪 Development

```bash
# Run the dev server
php -S 127.0.0.1:8080 -t public

# Reset the database
rm database/quiz_system.sqlite
sqlite3 database/quiz_system.sqlite < database/schema.sql

# Tail error log
tail -f storage/logs/app.log
```

### Code Style
- PHP: `declare(strict_types=1);` at the top of every file; PSR-12 formatting.
- Views: one logical block per file, no inline business logic.
- JS: vanilla ES2020, no framework.

---

## 🗺️ Roadmap

- [ ] Automated test suite (PHPUnit + Dusk-like browser tests)
- [ ] Import/export papers as JSON
- [ ] Learner analytics dashboard
- [ ] Timed exams with auto-submit
- [ ] Question bank search & tag cloud
- [ ] More AI scenes: distractor generation, difficulty calibration, bilingual translation

---

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repo and create a feature branch (`git checkout -b feat/my-feature`).
2. Follow the existing code style and add translations for any new user-facing strings.
3. Open a PR with a clear description and screenshots for UI changes.

For larger changes, open an issue first to discuss the approach.

---

## 📄 License

Released under the [MIT License](LICENSE). You are free to use, modify, and distribute this software, provided the copyright notice is retained.

---

## 🙏 Acknowledgements

- [Tailwind CSS](https://tailwindcss.com/) for the design system
- [Lucide Icons](https://lucide.dev/) for the icon set
- The PHP community for the rock-solid standard library this project leans on
