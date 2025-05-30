<?php 

namespace App\Controller;
use App\Controller\Controller;
use App\Attribute\Route;

class IndexController extends Controller
{
    #[Route(path:'/')]
    public function index() {
        return $this->render('home/index');
    }
}   