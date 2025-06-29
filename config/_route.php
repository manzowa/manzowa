<?php 

### ───────────────────────────────────────────────
### @route Base
### ───────────────────────────────────────────────
$app->get('/', [\App\Controller\IndexController::class, 'index'])->setName('index');

$app->group('/authentification', function ($group) {
    $group->get('/connexion', [\App\Controller\AuthController::class, 'indexAction'])->setName('auth.login');
    $group->post('/connexion', [\App\Controller\AuthController::class, 'postAction'])->setName('auth.post');
    $group->get('/deconnexion', [\App\Controller\AuthController::class, 'logoutAction'])->setName('auth.logout');
});

$app->group('/compte', function ($group) {
    $group->get('/profil', [\App\Controller\Account\IndexController::class, 'indexAction'])->setName('account.index');
});