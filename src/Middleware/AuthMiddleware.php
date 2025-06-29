<?php

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Security\Auth;

class AuthMiddleware  extends Middleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->jsonResponse([
                "success" => false,
                "message" => "Access token is missing from the header"
            ], 401);
        }
        $token = $matches[1];
        $auth = new Auth(token: $token);

        // Check Access Token
        if (!$auth->hasToken()) {
            return $this->jsonResponse([
                "success" => false,
                "message" => "Invalid access token"
            ], 401);
        }
        // Check if user has exceeded maximum login attempts
        if ($auth->getUserByToken()->isLocked()) {
            return $this->jsonResponse([
                "success" => false,
                "message" => "User account is currently locked out"
            ], 401);
        }
         // Check refresh token expiration
        if ($auth->getToken()->accessTokenExpired()) {
            return $this->jsonResponse([
                "success" => false,
                "message" => "Access token expired"
            ], 401);
        }
        return $handler->handle($request);
    }
}
