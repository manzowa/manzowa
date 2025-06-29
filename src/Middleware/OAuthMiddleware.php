<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;

class OAuthMiddleware extends Middleware implements MiddlewareInterface
{
    private string $introspectionEndpoint;
    private string $clientId;
    private string $clientSecret;

    public function __construct(string $introspectionEndpoint, string $clientId, string $clientSecret)
    {
        $this->introspectionEndpoint = $introspectionEndpoint;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->unauthorizedResponse('Missing or malformed token');
        }

        $token = $matches[1];

        // Vérification via introspection (RFC 7662)
        $ch = curl_init($this->introspectionEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->clientId}:{$this->clientSecret}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['token' => $token]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$result) {
            return $this->unauthorizedResponse('OAuth server error');
        }

        $data = json_decode($result, true);
        if (empty($data['active'])) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        // Le token est valide, on peut attacher les infos utilisateur à la requête
        $request = $request->withAttribute('oauth_user', $data);

        return $handler->handle($request);
    }

    private function unauthorizedResponse(string $message): Response
    {
        return $this->jsonResponse([
           'error' => 'unauthorized',
            "message" => $message
        ], 401);
    }
}
