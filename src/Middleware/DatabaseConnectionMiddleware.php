<?php 

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;

use \PDO;
use \PDOException;

class DatabaseConnectionMiddleware extends Middleware implements MiddlewareInterface 
{


    /**
     * Process the request and check the database connection status.
     *
     * @param Request $request
     * @param Response $response
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, Handler $handler): Response
    {
        $dns = getenv('DATABASE_SECURITY_DNS') ?? null;
        $username = getenv('DATABASE_SECURITY_USER')?? null;
        $password = getenv('DATABASE_SECURITY_PASSWORD')?? null;

        try {
            $pdo = new PDO($dns, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo = null;
        } catch (PDOException $ex) {
            return $this->jsonResponse([
                "success" => false,
                "message" => "Database connection failed"
            ], 500);
        }
        return $handler->handle($request);
    }
}