<?php 

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ContactController extends \App\Controller\Controller
{
    public function indexAction(Request $request, Response $response): Response
    {
        return $this->render(
            $response, 'contact/index.html.twig', [
            'hasContentHeader' => false, 
            'hasFooter' => false, 
            'hasNavigation' => false,

        ]);
    }
}