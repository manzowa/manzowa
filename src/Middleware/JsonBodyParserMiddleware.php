<?php 

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;

class JsonBodyParserMiddleware extends Middleware implements  MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        if (strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $body = (string) $request->getBody();
            $data = json_decode($body);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'JSON decoding error : ' . json_last_error_msg(),
                ], 400);
            }
            // Attacher les données décodées à la requête
            $request = $request->withParsedBody($data);
        }
        return $handler->handle($request);
    }
}
