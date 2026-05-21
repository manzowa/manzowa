<?php 

### ───────────────────────────────────────────────
### @route Base
### ───────────────────────────────────────────────
use Slim\App;
use App\Controller\IndexController;
use App\Controller\AuthController;
use App\Controller\ContactController;
use App\Controller\Account\IndexController as AccountIndexController;
use App\Controller\Account\SchoolController as AccountSchoolController;
use App\Controller\Account\ImageController as AccountImageController;
use App\Middleware\DatabaseConnectionMiddleware;
use App\Middleware\CheckAuthMiddleware;

return function (App $app) {
    $app->get('/', [IndexController::class, 'index'])->setName('index');

    $app->group('/authentification', function ($group) {
        $group->map(
            ['GET', 'POST'], '/connexion', 
            [AuthController::class, 'indexAction']
        )->setName('auth.login');
        $group->get('/deconnexion', [AuthController::class, 'logoutAction'])
        ->setName('auth.logout');
    });

    $app->map(
        ['GET', 'POST'],'/contact', 
        [ContactController::class, 'indexAction']
    )->setName('contact.index');

    ### ───────────────────────────────────────────────
    ### @route Account
    ### ───────────────────────────────────────────────
    $app->group('/compte', function ($group) {
        $group->get(
            '/profil', 
            [AccountIndexController::class, 'indexAction']
        )->setName('account.index');

        $group->group('/ecoles', function($group){
            $group->group('/{id:[0-9]+}', function($group){
                $group->get(
                    '/voir', 
                    [AccountSchoolController::class, 'showAction']
                )->setName('account.show_ecole');
                $group->map(['GET', 'POST'], '/edit', 
                    [AccountSchoolController::class, 'editAction']
                )->setName('account.edit_ecole');
                $group->map(['GET', 'POST'], '/images', 
                    [AccountImageController::class, 'addAction']
                )->setName('account.add_image_ecole');

            });
            $group->get(
                '/liste', 
                [AccountSchoolController::class, 'indexAction']
            )->setName('account.liste_ecole');

            $group->map(['GET', 'POST'], '/ajouter', 
                [AccountSchoolController::class, 'addAction']
            )->setName('account.add_ecole');
            
            $group->get(
                '/supprimer', 
                [AccountSchoolController::class, 'deleteAction']
            )->setName('account.delete_ecole');
        });


    })->add(new DatabaseConnectionMiddleware())
    ->add(new CheckAuthMiddleware());
};