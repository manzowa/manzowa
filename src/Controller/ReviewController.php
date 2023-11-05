<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    #[Route('/review', name: 'review.build')]
    public function index(): Response
    {
        return $this->render('review/under.html.twig', [
            'controller_name' => 'ReviewController',
        ]);
    }
}
