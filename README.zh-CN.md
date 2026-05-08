# Quiz Practice System（在线题库练习系统）

> 基于原生 PHP 8.1+、SQLite 与 Tailwind CSS 打造的现代化、AI 赋能的在线考试与练习平台。
>
> 📖 **[English README](README.md)**

**代码仓库：** https://github.com/HCARX/QuizPracticeSystem

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)

---

## ✨ 功能特性

### 📚 题库与试卷管理
- **科目 / 试卷层级** — 支持多科目、多试卷的树状组织
- **可视化蓝图编辑器** — 图形化搭建试卷模板（章节、区块、分值）
- **14 种题型** — 单选、多选、判断、填空、简答、写作、翻译、完形填空、阅读材料（主题 + 子题）、听力材料（主题 + 子题）、匹配、排序、说明文本、媒体嵌入
- **中英双语** — 内置 `zh` / `en` 语言包，一键切换

### 🤖 AI 辅助出题
- **兼容 OpenAI API** — 可对接 OpenAI、Azure、DeepSeek、Moonshot、本地大模型等任意兼容服务
- **多模型白名单** — 后台可管理模型目录，按角色控制访问
- **自定义 Prompt 模板** — 按场景维护系统 / 用户提示词，支持变量插值
- **结构化 Schema 约束** — AI 调用前会收到针对各题型定制的注释 JSON Schema，保证生成结果完整且契合题型
- **全量调用日志** — 记录 Token 消耗、响应时间、错误信息

### ✍️ 出题工作流
- **逐题填充** — 按模板生成的题位顺序，逐个录入 / 编辑
- **AI 一键生成整卷** — 根据模板蓝图一次生成全部题目
- **草稿 / 发布状态** — 审阅确认后再对学员开放
- **客观题自动判分** — 主观题进入人工审阅流程

### 👥 用户与权限
- **角色体系** — `super_admin` / `admin` / `editor` / `reviewer` / `vip` / `user`
- **Session 认证** — 所有写操作均带 CSRF 校验
- **登录审计** — 记录最近登录时间与 IP

### 🎨 前端
- **Tailwind 响应式界面** — 现代简洁的后台与学员端
- **零构建** — 原生 PHP 视图 + Tailwind CDN，无需 Node 工具链
- **轻量 JS 助手** — `QS.fetch` / `QS.toast` / `QS.confirm` 统一交互

---

## 🏗️ 项目结构

```
QuizPracticeSystem/
├── app/
│   ├── Controllers/       # HTTP 控制器（Admin/ Api/ Web/）
│   ├── Core/              # 框架核心：Router/Controller/Auth/DB/Request/Response/View
│   ├── DTOs/              # 数据传输对象
│   ├── Middleware/        # 认证 / 权限 / CSRF 中间件
│   ├── Repositories/      # 数据访问层
│   └── Services/          # 业务服务（AiService、PaperService 等）
├── config/
│   ├── app.php            # 应用配置
│   ├── database.php       # SQLite 路径及连接参数
│   ├── ai.php             # AI 默认配置
│   └── routes.php         # 路由表
├── database/
│   ├── schema.sql         # 完整建表脚本
│   └── quiz_system.sqlite # SQLite 数据库文件
├── public/
│   ├── index.php          # 入口文件（前端控制器）
│   ├── .htaccess          # Apache 伪静态
│   ├── assets/            # 静态资源
│   └── uploads/           # 用户上传文件
├── resources/
│   ├── lang/              # zh.php / en.php 语言包
│   └── views/             # PHP 视图（admin/ web/ layout/）
└── storage/               # 日志、缓存、临时文件
```

### 关键概念
- **路由**：在 `config/routes.php` 中声明，由 `App\Core\Router` 分发。
- **蓝图 JSON**：试卷模板的 `blueprint_json` 描述章节 → 区块，定义题型、题量、分值。
- **内容 JSON**：每题存三列 —— `content_json`（题干 / 选项 / 媒体）、`answer_json`（答案）、`analysis_json`（解析）。
- **Part 标记**：`type === "part"` 的章节仅作为标题（如"听力 Part 1"），不生成题位。

---

## 🚀 快速开始

### 环境要求
- PHP **8.1+**，需开启扩展：`pdo_sqlite`、`json`、`mbstring`、`openssl`、`curl`
- Composer 2.x
- `database/`、`storage/` 目录具备写权限

### 安装步骤

```bash
# 1. 克隆项目
git clone https://github.com/HCARX/QuizPracticeSystem.git
cd QuizPracticeSystem

# 2. 安装自动加载（运行时无额外依赖）
composer install

# 3. 复制环境变量模板（可选）
cp .env.example .env

# 4. 一键安装：初始化数据库 + 创建超级管理员
php bin/install.php
# 或自定义账号：
# php bin/install.php --username=admin --password='S3cret!' --name="管理员"

# 5. 启动 PHP 内置服务器
php -S 127.0.0.1:8080 -t public
```

浏览器访问 **http://127.0.0.1:8080** 即可。

### 默认管理员账号

初次部署时，执行以下命令创建一个 **超级管理员**：

```bash
php -r "require 'vendor/autoload.php'; \
  \$pdo = new PDO('sqlite:database/quiz_system.sqlite'); \
  \$hash = password_hash('admin123', PASSWORD_DEFAULT); \
  \$pdo->prepare('INSERT INTO users (username,password_hash,display_name,role,status) VALUES (?,?,?,?,1)') \
      ->execute(['admin', \$hash, '超级管理员', 'super_admin']);"
```

| 项目         | 值              |
|--------------|-----------------|
| 登录地址     | `/login`        |
| 用户名       | `admin`         |
| 初始密码     | `admin123`      |
| 角色         | `super_admin`   |

> ⚠️ **请在首次登录后立即到"后台 → 个人资料"修改密码！**

---

## 🔁 伪静态（URL Rewrite）配置

项目采用前端控制器模式，入口文件位于 `public/index.php`。为了生成友好的 URL（例如 `/admin/papers` 而非 `/index.php?...`），请在 Web 服务器上开启 URL 重写。仓库已内置 `public/.htaccess`。

### Apache

需开启 `mod_rewrite`，并允许 `.htaccess` 覆盖：

```apache
<VirtualHost *:80>
    ServerName quiz.example.com
    DocumentRoot /www/wwwroot/QuizPracticeSystem/public

    <Directory /www/wwwroot/QuizPracticeSystem/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

内置 `public/.htaccess`：
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
    root /www/wwwroot/QuizPracticeSystem/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }

    # 禁止访问敏感目录
    location ~ ^/(app|config|database|storage|resources|vendor)/ {
        deny all;
        return 404;
    }
}
```

### 宝塔面板 (BT Panel)

> 站点根目录必须指向 `public/`（可在站点设置的"网站目录 → 运行目录"里修改）。

在站点的 **伪静态** 设置中粘贴下列规则（Nginx 版）：

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ ^/(app|config|database|storage|resources|vendor)/ {
    deny all;
    return 404;
}
```

Apache 版直接使用项目自带的 `public/.htaccess` 即可。

### Caddy

```caddyfile
quiz.example.com {
    root * /www/wwwroot/QuizPracticeSystem/public
    php_fastcgi unix//run/php/php8.1-fpm.sock
    file_server
    try_files {path} {path}/ /index.php?{query}
}
```

### IIS（web.config）

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

---

## ⚙️ 配置说明

### 应用 `config/app.php`
```php
return [
    'name'     => 'Quiz Practice System',
    'debug'    => true,              // 生产环境请改为 false
    'timezone' => 'Asia/Shanghai',
    'url'      => 'http://localhost:8080',
];
```

### 数据库 `config/database.php`
默认指向 `database/quiz_system.sqlite`，可通过环境变量 `DB_PATH` 覆盖。

### AI 服务商
在 **后台 → AI 设置** 中配置（无需改代码）：
1. 填写 **Base URL**（如 `https://api.openai.com/v1`）与 **API Key**。
2. 添加一个或多个 **模型**：`model_id`、显示名称、`allowed_roles`。
3. 为不同场景（`paper_fill`、`question_polish` 等）启用对应的 **Prompt 模板**。

所有调用会写入 `ai_logs` 表，并在 AI 设置页展示。

---

## 🧭 使用流程

1. **创建科目**：`后台 → 科目 → 新建`，设置名称、颜色、图标。
2. **创建试卷与模板**：`后台 → 试卷 → 新建`，选择科目后编辑 **模板**（章节 / 区块 / 题量 / 分值）。模板可复用。
3. **填充题目**：
   - **手动逐题**：`试卷 → 选择模板 → 开始` 按题位顺序录入，UI 随题型自适应。
   - **AI 生成**：在同一页面点击 **AI 填充**，系统将模板蓝图 + 注释 Schema 发送给已配置模型，一次性生成全部题目，审阅后保存即可。
4. **发布**：所有题目审核无误后，将试卷状态改为 `published`，学员端即可看到。
5. **学员答题**：客观题自动判分，主观题进入人工批阅队列。

---

## 🔌 部分接口一览

| 方法   | 路径                                               | 功能                       |
|-------:|----------------------------------------------------|----------------------------|
| GET    | `/admin/papers`                                    | 试卷列表                   |
| GET    | `/admin/papers/{id}/pick-template`                 | 选择模板进入填充           |
| GET    | `/admin/papers/{paperId}/fill/{tplId}`             | 逐题填充界面               |
| POST   | `/admin/papers/{paperId}/fill/{tplId}/{slotIndex}` | 保存单题                   |
| POST   | `/admin/ai/generate-paper`                         | AI 一键生成整卷            |
| POST   | `/admin/ai/provider`                               | 保存 AI 服务商配置         |
| POST   | `/admin/ai/models`                                 | 新增模型                   |
| POST   | `/admin/ai/models/{id}/update`                     | 更新模型                   |
| POST   | `/admin/ai/models/{id}/toggle`                     | 启用 / 停用                |
| POST   | `/admin/ai/models/{id}/delete`                     | 删除模型                   |

所有 `POST` 接口均需携带有效的 CSRF Token。

---

## 🌐 国际化

语言文件位于 `resources/lang/{zh,en}.php`，使用点号风格的扁平数组。在视图 / 控制器里：

```php
<?= $t('admin.papers.pick_template') ?>
```

如需新增语言，复制 `en.php → xx.php` 翻译后在 `config/app.php` 注册即可。

---

## 🔒 安全要点

- 密码使用 `password_hash()`（bcrypt）存储。
- 所有写操作接口均启用 CSRF 保护。
- 数据库访问统一走预处理语句（`App\Core\Database`）。
- 上传文件按 MIME + 扩展名双重校验，统一存放于 `public/uploads`。
- **切勿**将含有真实用户数据的 `database/quiz_system.sqlite` 提交到公共仓库。`.gitignore` 已默认忽略。

---

## 🧪 开发辅助

```bash
# 启动开发服务器
php -S 127.0.0.1:8080 -t public

# 重置数据库
rm database/quiz_system.sqlite
sqlite3 database/quiz_system.sqlite < database/schema.sql

# 查看错误日志
tail -f storage/logs/app.log
```

### 代码规范
- PHP：每个文件首行 `declare(strict_types=1);`，遵循 PSR-12。
- 视图：单一职责，避免内联业务逻辑。
- JS：原生 ES2020，不引入框架。

---

## 🗺️ 路线图

- [ ] 自动化测试套件（PHPUnit + 浏览器测试）
- [ ] 试卷 JSON 导入 / 导出
- [ ] 学员数据分析看板
- [ ] 限时考试 + 自动交卷
- [ ] 题库检索与标签云
- [ ] 更多 AI 场景：干扰项生成、难度校准、双语翻译

---

## 🤝 贡献

欢迎提交 Issue 与 PR！

1. Fork 项目并创建功能分支（`git checkout -b feat/my-feature`）。
2. 遵循现有代码风格，新增用户可见文案请同时补齐语言包。
3. 提交 PR 时附上清晰描述，UI 改动请附截图。

较大改动建议先开 Issue 讨论方案。

---

## 📄 许可证

本项目基于 [MIT License](LICENSE) 开源，允许自由使用、修改和再发布，但需保留版权声明。

---

## 🙏 致谢

- [Tailwind CSS](https://tailwindcss.com/) — 设计体系
- [Lucide Icons](https://lucide.dev/) — 图标库
- PHP 社区提供的坚实标准库
