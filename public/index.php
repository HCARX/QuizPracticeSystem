<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

$app = \App\Core\Application::boot();

require BASE_PATH . '/config/routes.php';

$app->run();
