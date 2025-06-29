<?php
namespace App\Controller;

use App\Security\Auth;
use App\Session\Session;
use Dotenv\Validator as DotenvValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use \Manzowa\Validator\Validator;
use Slim\Routing\RouteContext;
use App\Model\User;

class Controller
{
    protected Twig $view;
    protected Validator $validator;
    protected Session $session;
    protected Auth $auth;

    public function __construct(
        Twig $view, 
        Validator $validator, 
        Session $session,
        Auth $auth
    ) {
        $this->view = $view;
        $this->validator = $validator;
        $this->session = $session;
        $this->auth = $auth;
    }

    protected function render(Response $response, string $template, array $data = []): Response
    {
        return $this->view->render($response, $template, $data);
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    public function user(): ?User
    {
        return $this->auth->user();
    }
    public function check(): bool
    {
        return $this->auth->check();
    }


     public function redirectTo(
        Request $request, 
        Response $response, 
        string $name, int $status = 302
    ): Response{
        $routeParser = \Slim\Routing\RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor($name);
        return $response->withHeader('Location', $url)->withStatus($status);
    }
}