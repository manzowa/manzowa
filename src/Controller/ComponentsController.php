<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComponentsController extends AbstractController
{
    #[Route('/todos', name: 'components.todos')]
    public function todos(): Response
    {
        return $this->render('components/todos.html.twig');
    }
    
    #[Route('/validator', name: 'components.validator')]
    public function validator(): Response
    {
        return $this->render('components/validator.html.twig');
    }

    #[Route('/sgjs', name: 'components.sgjs')]
    public function sgjs(): Response
    {
        return $this->render('components/sgjs.html.twig');
    }
}
