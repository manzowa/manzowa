<?php 

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;



class ErrorTypeMiddleware  extends Middleware implements MiddlewareInterface 
{
    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }


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
        try {
            // On passe au middleware ou au contrôleur suivant
            return $handler->handle($request);

        } catch (\Throwable $ex) {
            $uri = $request->getUri()->getPath();
            $response = new \Slim\Psr7\Response();
            
            if (str_starts_with($uri, '/api')) {
                return $this->jsonResponse([
                    'success' => false,
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage()
                ], 500);
            }
            // Page HTML simple
            // Utilisation de Twig pour HTML
            return $this->twig->render(
                $response, 'error/error.html.twig', [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ])->withStatus(500);
        }
    }
}