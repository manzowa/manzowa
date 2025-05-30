<?php 

namespace App\Controller\Account;
use App\Controller\AppController;

class IndexController extends AppController
{
    public function index()
    {
        $this->view->render('account/index');
    }
}