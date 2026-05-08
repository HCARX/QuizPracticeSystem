<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\I18n;
use App\Core\Response;

class LocaleController extends Controller
{
    public function switch(string $code): void
    {
        I18n::setLocale($code);
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        Response::redirect($referer);
    }
}
