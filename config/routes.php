<?php

declare(strict_types=1);

use App\Core\Application;
use App\Middleware\AdminAuth;
use App\Middleware\UserAuth;
use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\SubjectController;
use App\Controllers\Admin\PaperController;
use App\Controllers\Admin\QuestionController;
use App\Controllers\Admin\PaperQuestionFillController;
use App\Controllers\Admin\AiSettingsController;
use App\Controllers\Admin\MailSettingsController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\LogController;
use App\Controllers\Admin\TemplateController;
use App\Controllers\Web\HomeController;
use App\Controllers\Web\AuthController as WebAuthController;
use App\Controllers\Web\QuizController;
use App\Controllers\Web\PracticeController;
use App\Controllers\Web\ProfileController;
use App\Controllers\Web\LocaleController;
use App\Controllers\Web\PasswordResetController;
use App\Controllers\Api\AiController;

$router = Application::getInstance()->router();

// ============================================================
// Public Routes
// ============================================================
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [WebAuthController::class, 'showLogin']);
$router->post('/login', [WebAuthController::class, 'login']);
$router->get('/register', [WebAuthController::class, 'showRegister']);
$router->post('/register', [WebAuthController::class, 'register']);
$router->get('/logout', [WebAuthController::class, 'logout']);
$router->get('/locale/{code}', [LocaleController::class, 'switch']);

// Password reset (public)
$router->get('/forgot-password', [PasswordResetController::class, 'showRequest']);
$router->post('/forgot-password', [PasswordResetController::class, 'sendLink']);
$router->get('/reset-password/{token}', [PasswordResetController::class, 'showReset']);
$router->post('/reset-password/{token}', [PasswordResetController::class, 'reset']);

// ============================================================
// Admin Auth (no middleware)
// ============================================================
$router->get('/admin/login', [AdminAuthController::class, 'showLogin']);
$router->post('/admin/login', [AdminAuthController::class, 'login']);
$router->get('/admin/logout', [AdminAuthController::class, 'logout']);

// ============================================================
// Admin Protected Routes
// ============================================================
$router->group(['prefix' => 'admin', 'middleware' => [AdminAuth::class]], function ($router) {

    // Dashboard
    $router->get('/', [DashboardController::class, 'index']);
    $router->get('/dashboard', [DashboardController::class, 'index']);

    // Subjects
    $router->get('/subjects', [SubjectController::class, 'index']);
    $router->post('/subjects', [SubjectController::class, 'store']);
    $router->post('/subjects/{id}/update', [SubjectController::class, 'update']);
    $router->post('/subjects/{id}/delete', [SubjectController::class, 'destroy']);
    $router->post('/subjects/{id}/toggle', [SubjectController::class, 'toggle']);

    // Papers
    $router->get('/papers', [PaperController::class, 'index']);
    $router->get('/papers/create', [PaperController::class, 'create']);
    $router->post('/papers', [PaperController::class, 'store']);
    $router->get('/papers/{id}/edit', [PaperController::class, 'edit']);
    $router->post('/papers/{id}/update', [PaperController::class, 'update']);
    $router->post('/papers/{id}/delete', [PaperController::class, 'destroy']);
    $router->post('/papers/{id}/status', [PaperController::class, 'updateStatus']);

    // Paper → Template → Question Fill workflow
    $router->get('/papers/{id}/fill', [PaperQuestionFillController::class, 'pickTemplate']);
    $router->get('/papers/{paperId}/fill/{templateId}', [PaperQuestionFillController::class, 'start']);
    $router->post('/papers/{paperId}/fill/{templateId}/slot/{slotIndex}', [PaperQuestionFillController::class, 'saveSlot']);

    // Questions
    $router->get('/questions', [QuestionController::class, 'index']);
    $router->get('/questions/create', [QuestionController::class, 'create']);
    $router->get('/questions/fill', [QuestionController::class, 'fillFromTemplate']);
    $router->post('/questions/fill', [QuestionController::class, 'saveFromTemplate']);
    $router->post('/questions', [QuestionController::class, 'store']);
    $router->get('/questions/{id}/edit', [QuestionController::class, 'edit']);
    $router->post('/questions/{id}/update', [QuestionController::class, 'update']);
    $router->post('/questions/{id}/delete', [QuestionController::class, 'destroy']);
    $router->post('/questions/batch-import', [QuestionController::class, 'batchImport']);

    // API helpers
    $router->get('/api/subjects/{id}/papers', [QuestionController::class, 'apiPapersForSubject']);

    // AI Settings
    $router->get('/ai', [AiSettingsController::class, 'index']);
    $router->post('/ai/provider', [AiSettingsController::class, 'updateProvider']);
    $router->post('/ai/prompts/{id}', [AiSettingsController::class, 'updatePrompt']);
    $router->post('/ai/models', [AiSettingsController::class, 'storeModel']);
    $router->post('/ai/models/{id}/update', [AiSettingsController::class, 'updateModel']);
    $router->post('/ai/models/{id}/toggle', [AiSettingsController::class, 'toggleModel']);
    $router->post('/ai/models/{id}/delete', [AiSettingsController::class, 'deleteModel']);

    // Users
    $router->get('/users', [UserController::class, 'index']);
    $router->post('/users', [UserController::class, 'store']);
    $router->post('/users/{id}/update', [UserController::class, 'update']);
    $router->post('/users/{id}/toggle', [UserController::class, 'toggleStatus']);
    $router->post('/users/{id}/delete', [UserController::class, 'destroy']);

    // Templates
    $router->get('/templates', [TemplateController::class, 'index']);
    $router->post('/templates', [TemplateController::class, 'storeBlueprint']);
    $router->get('/templates/{id}/editor', [TemplateController::class, 'editor']);
    $router->post('/templates/{id}/update', [TemplateController::class, 'updateBlueprint']);
    $router->post('/templates/{id}/delete', [TemplateController::class, 'deleteBlueprint']);
    $router->post('/templates/modules', [TemplateController::class, 'storeModule']);
    $router->post('/templates/modules/{id}/update', [TemplateController::class, 'updateModule']);
    $router->post('/templates/modules/{id}/delete', [TemplateController::class, 'deleteModule']);

    // Mail Settings
    $router->get('/mail', [MailSettingsController::class, 'index']);
    $router->post('/mail', [MailSettingsController::class, 'update']);
    $router->post('/mail/test', [MailSettingsController::class, 'test']);

    // Logs
    $router->get('/logs', [LogController::class, 'index']);
});

// ============================================================
// User Protected Routes
// ============================================================
$router->group(['prefix' => '', 'middleware' => [UserAuth::class]], function ($router) {
    // Specialized Practice
    $router->get('/practice', [PracticeController::class, 'setup']);
    $router->post('/practice/start', [PracticeController::class, 'start']);

    // Quiz
    $router->get('/quiz/{id}/start', [QuizController::class, 'start']);
    $router->get('/quiz/{id}', [QuizController::class, 'take']);
    $router->post('/quiz/{id}/save-answer', [QuizController::class, 'saveAnswer']);
    $router->post('/quiz/{id}/submit', [QuizController::class, 'submit']);
    $router->get('/quiz/{id}/result', [QuizController::class, 'result']);

    // Profile
    $router->get('/profile', [ProfileController::class, 'index']);
    $router->get('/vocabulary', [ProfileController::class, 'vocabulary']);
    $router->post('/vocabulary', [ProfileController::class, 'addWord']);
    $router->post('/vocabulary/{id}/update', [ProfileController::class, 'updateWord']);
    $router->post('/vocabulary/{id}/delete', [ProfileController::class, 'deleteWord']);
    $router->get('/mistakes', [ProfileController::class, 'mistakes']);
    $router->post('/mistakes/{id}/mastered', [ProfileController::class, 'toggleMistakeMastered']);
    $router->get('/favorites', [ProfileController::class, 'favorites']);
    $router->post('/favorites/toggle', [ProfileController::class, 'toggleFavorite']);
    $router->get('/settings', [ProfileController::class, 'settings']);
    $router->post('/settings', [ProfileController::class, 'updateSettings']);
    $router->post('/settings/password', [ProfileController::class, 'updatePassword']);

    // AI
    $router->post('/api/ai/explain-word', [AiController::class, 'explainWord']);
    $router->post('/api/ai/analyze-question', [AiController::class, 'analyzeQuestion']);
});
