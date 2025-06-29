<?php
namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;

class JsonMiddleware extends Middleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === false) {
            $response = new \Slim\Psr7\Response();
            return $this->jsonResponse([
                "success" => false,
                "message" => "Content-Type must be application/json",  
            ], 415);
        }

        // If the Content-Type is valid, proceed to the next middleware
        return $handler->handle($request);
    }
}