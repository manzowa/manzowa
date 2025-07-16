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

    public function postAction(Request $request, Response $response): Response
    {
        // Handle form submission logic here
        // For example, validate input and send an email

        return $this->redirectTo($request, $response, "contact.index");
    }
}