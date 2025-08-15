<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

class AuthController extends Controller
{
    public function indexAction(
        Request $request, 
        Response $response,
        array $args
    ): Response {
       
        if ($this->check()) {
            return $this->redirectTo(
                $request, $response, 
                "account.index" 
            );
        }
        // Récupérer les données POST sous forme de tableau associatif
        $v = $this->validator;
        if ($v->method()) {
            $v->validate([
                "username" => function() use ($v) {$v->isEmpty()->get();},
                "password" => function() use ($v) {$v->isEmpty()->get();}
            ]);
            if ($v->failed()) {
                $this->addErrors($v->errors());
                return $this->redirectTo($request, $response, "auth.login");
            } else {
                if ($this->auth->attempt($v->results()['username'], $v->results()['password'])) {
                    return $this->redirectTo($request, $response, "account.index");
                } else {
                    $this->flash->addMessage(
                        'danger', "vérifiez votre nom d'utilisateur ou mot de passe"
                    );
                    return $this->redirectTo($request, $response, "auth.login");
                }
            }
        }
        return $this->render(
            $response, 'login/index.html.twig', [
            'hasContentHeader' => false, 
            'hasNavigation' => false,
            'hasFooter' => false, 
        ]);
    }

    public function logoutAction(
        Request $request, 
        Response $response
        
    ): Response {
        $this->auth->deconnecter();
        return $this->redirectTo($request, $response, "index");
    }
}