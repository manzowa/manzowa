<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        return $this->render($response, 'home/index.html.twig', []);
    }
}