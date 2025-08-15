<?php

namespace App\Controller\Account;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends \App\Controller\Controller
{
    public function indexAction(Request $request, Response $response): Response
    {
        return $this->render(
            $response, 'account/index.html.twig', [
            'hasContentHeader' => false, 
            'hasFooter' => false, 
        ]);
    }
}