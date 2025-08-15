<?php 

### ───────────────────────────────────────────────
### @route Base
### ───────────────────────────────────────────────
$app->get('/', [\App\Controller\IndexController::class, 'index'])->setName('index');

$app->group('/authentification', function ($group) {
    $group->map(
        ['GET', 'POST'], '/connexion', 
        [\App\Controller\AuthController::class, 'indexAction']
    )->setName('auth.login');
    $group->get('/deconnexion', [\App\Controller\AuthController::class, 'logoutAction'])
    ->setName('auth.logout');
});

$app->map(
    ['GET', 'POST'],'/contact', 
    [\App\Controller\ContactController::class, 'indexAction']
)->setName('contact.index');

### ───────────────────────────────────────────────
### @route Account
### ───────────────────────────────────────────────
$app->group('/compte', function ($group) {
    $group->get(
        '/profil', 
        [\App\Controller\Account\IndexController::class, 'indexAction']
    )->setName('account.index');

    $group->group('/ecoles', function($group){
        $group->group('/{id:[0-9]+}', function($group){
            $group->get(
                '/voir', 
                [\App\Controller\Account\SchoolController::class, 'showAction']
            )->setName('account.show_ecole');
            $group->map(['GET', 'POST'], '/edit', 
                [\App\Controller\Account\SchoolController::class, 'editAction']
            )->setName('account.edit_ecole');
            $group->map(['GET', 'POST'], '/images', 
                [\App\Controller\Account\ImageController::class, 'addAction']
            )->setName('account.add_image_ecole');

        });
        $group->get(
            '/liste', 
            [\App\Controller\Account\SchoolController::class, 'indexAction']
        )->setName('account.liste_ecole');

        $group->map(['GET', 'POST'], '/ajouter', 
            [\App\Controller\Account\SchoolController::class, 'addAction']
        )->setName('account.add_ecole');
        
        $group->get(
            '/supprimer', 
            [\App\Controller\Account\SchoolController::class, 'deleteAction']
        )->setName('account.delete_ecole');
    });


})->add(new App\Middleware\DatabaseConnectionMiddleware())
->add(new App\Middleware\CheckAuthMiddleware());