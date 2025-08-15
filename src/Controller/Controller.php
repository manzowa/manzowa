<?php

namespace App\Controller;

use App\Security\Auth;
use App\Session\Session;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use \Manzowa\Validator\Validator;
use App\Model\User;
use App\Session\FlashMessage;

class Controller
{
    protected Twig $view;
    protected Validator $validator;
    protected Session $session;
    protected Auth $auth;
    protected FlashMessage $flash;

    public function __construct(
        Twig $view,
        Validator $validator,
        Session $session,
        Auth $auth,
        FlashMessage $flash
    ) {
        $this->view = $view;
        $this->validator = $validator;
        $this->session = $session;
        $this->auth = $auth;
        $this->flash = $flash;
    }

    protected function render(Response $response, string $template, array $data = []): Response
    {
        $data["messages"] = $this->flash->getMessages();
        $data["errors"] = $this->flash->getErrors();
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
        string $name,
        array $routeParams = [],
        array $queryParams = [],
        int $status = 302
    ): Response {
        $routeParser = \Slim\Routing\RouteContext::fromRequest($request)->getRouteParser();

        // Générer l'URL avec les paramètres de route
        $url = $routeParser->urlFor($name, $routeParams);

        // Ajouter les paramètres de requête (GET) à l'URL si fournis
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $response->withHeader('Location', $url)->withStatus($status);
    }

    public function addFlashMessage($type, $message): self
    {
        // Ajouter un message flash
        $this->flash->addMessage($type, $message);
        return $this;
    }
    public function addError($key, $message, string $type = "danger"): self
    {
        // Ajouter un message flash
        $this->flash->addError($key, $message, $type);
        return $this;
    }
    public function addErrors(array $errors = []): self
    {
        // Ajouter un message flash
        foreach ($errors as $key => $error) {
            $this->addError($key, $error);
        }
        return $this;
    }

    protected function imageFileUnique(int $schoolid, $filename): string
    {
        $file = uniqid("img_" . $schoolid . "_" . $filename . '_', true);
        return $file;
    }

    protected function extensionFile(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($extension);
    }
    protected function getFileName(string $filename): string
    {
        $file = pathinfo($filename, PATHINFO_FILENAME);
        return $file;
    }
}
