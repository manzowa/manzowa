<?php

namespace App\Middleware;
use \Psr\Http\Message\ResponseInterface as Response;

class Middleware 
{
    protected function jsonResponse(
        array $data, 
        int $status
    ): Response {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}