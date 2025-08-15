<?php

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Security\Auth;

class CheckAuthMiddleware extends Middleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $auth = new Auth();
        if (!$auth->check()) {
            return $this->redirect("/authentification/connexion");
        }
        return $handler->handle($request);
    }
}
