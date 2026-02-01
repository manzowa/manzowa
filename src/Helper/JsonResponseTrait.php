<?php

namespace App\Helper;

use Slim\Psr7\Response;

trait JsonResponseTrait
{
    protected function jsonResponse(array $data, int $status): Response
    {
        $response = new Response();
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));

        return $response->withStatus($status)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Content-Type', 'application/json');
    }

    protected function successResponse($data, string $message = '', int $status = 200): Response
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    protected function errorResponse(string $message, int $status = 500): Response
    {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message
        ], $status);
    }

    protected function response(
        bool $success,
        mixed $message = '',
        $data = null,
        int $status = 200
    ): Response {
        $payload = [
            'success' => $success,
            'message' => $message
        ];

        if ($success && $data !== null) {
            $payload['data'] = $data;
        }

        return $this->jsonResponse($payload, $status);
    }
}