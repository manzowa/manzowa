<?php

/**
 * App
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App
 * @package  App
 * @author   User: Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', dirname(__DIR__));
define('APP_PUBLIC', APP_ROOT . DS);
define('APP_IMAGES_ROOT', join(DS, [APP_ROOT, 'public', 'images']));

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Manzowa\Validator\Validator;
use App\Session\Session;
use App\Security\Auth;
use App\Controller\Controller;
use Slim\Csrf\Guard;

// Chargement autoload et config
require_once join(DS, [__DIR__, 'helpers.php']);
require_once App\path('vendor', 'autoload.php');
App\session();
\Dotenv\Dotenv::createUnsafeImmutable(APP_ROOT)->load();


// Initialisation container + app
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();
$responseFactory = $app->getResponseFactory();

### ───────────────────────────────────────────────
### SERVICES
### ───────────────────────────────────────────────
// ✅ CSRF protection (clé "csrf" nécessaire si on utilise ->add('csrf'))
$container->set('csrf', function () use ($responseFactory) {
    return new Guard($responseFactory);
});
// Autres services applicatifs
$container->set(Validator::class, fn() => new Validator());
$container->set(Session::class, fn() => new Session());
$container->set(Auth::class, fn() => new Auth());
$container->set(Twig::class, function () use ($container){
    $twig = Twig::create(App\path('templates'), [
        'cache' => false,
        'debug' => true,
    ]);
    $csrf = $container->get('csrf');
    $auth = $container->get(Auth::class);
    $twig->getEnvironment()->addGlobal('csrf', [
      'nameKey'  => $csrf->getTokenNameKey(),
      'valueKey' => $csrf->getTokenValueKey(),
      'name'     => $csrf->getTokenName(),
      'value'    => $csrf->getTokenValue()
    ]);
    $twig->getEnvironment()->addGlobal('user', $auth->user());
    $twig->addExtension(new \Twig\Extension\DebugExtension());
    return $twig;
});
// Contrôleur avec injection
$container->set(Controller::class, function ($c) {
    return new Controller(
        $c->get(Twig::class),
        $c->get(Validator::class),
        $c->get(Session::class),
        $c->get(Auth::class)
    );
});
$app->add(new App\Middleware\ErrorTypeMiddleware($container->get(Twig::class)));


### ───────────────────────────────────────────────
### ROUTES Principal
### ───────────────────────────────────────────────
require_once App\path('config', '_route.php');
require_once App\path('config', '_route.api.php');

### ───────────────────────────────────────────────
### MIDDLEWARES
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));

### ───────────────────────────────────────────────
### RUN
### ───────────────────────────────────────────────
$app->run();