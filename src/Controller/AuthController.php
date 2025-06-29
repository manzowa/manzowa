<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

class AuthController extends Controller
{
    public function indexAction(
        Request $request, 
        Response $response
    ): Response {
        if ($this->check()) {
            return $this->redirectTo(
                $request, $response, 
                "account.index" 
            );
        }
        return $this->render(
            $response, 'login/index.html.twig', [
            'hasContentHeader' => false, 
            'hasNavigation' => false,
            'hasFooter' => false, 
        ]);
    }
    public function postAction(
        Request $request, 
        Response $response,array  $args
    ) {
        $validator = $this->validator;
        // Récupérer les données POST sous forme de tableau associatif
        if ($validator->method()) {
            $validator->validation([
                "username" => function () use ($validator) {
                    $validator->isEmpty()->get();
                },
                "password" => function () use ($validator) {
                    $validator->isEmpty()->get();
                }
            ]);
            if ($validator->failed()) {
                die();
            } else {
                $data = $validator->resultats();
                if ($this->auth->attempt($data['username'], $data['password'])) {
                    return $this->redirectTo($request, $response, "account.index");
                    
                } else {
                    die('Problème entre username or password');
                }
            }
        }
    }
    public function logoutAction(
        Request $request, 
        Response $response
        
    ): Response {
        $this->auth->deconnecter();
        return $this->redirectTo($request, $response, "index");
    }
}