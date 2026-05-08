<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Auth;
use App\Core\Response;

class UserAuth
{
    public function handle(Request $request): bool
    {
        if (!Auth::check()) {
            if ($request->isAjax()) {
                Response::json(['error' => 'Unauthorized'], 401);
                return false;
            }
            Response::redirect('/login');
            return false;
        }
        return true;
    }
}
