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
use \Slim\Flash\Messages as Flash;
use App\Session\FlashMessage;
use Twig\TwigFunction;
use Twig\TwigFilter;

// Chargement autoload et config
require_once join(DS, [__DIR__, 'helpers.php']);
require_once App\path('vendor', 'autoload.php');
App\session();
\Dotenv\Dotenv::createUnsafeImmutable(APP_ROOT)->load();

// Messager alter
$alter_message = include_once App\path('config', 'message.php');

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
$container->set(Validator::class, function() use($alter_message) {
    $validator =  new Validator();
    $validator->setMessage("empty", $alter_message['empty']);
    $validator->setMessage("maxSizeFile", $alter_message['maxSizeFile']);
    $validator->setMessage("invalidFileType",$alter_message['invalidFileType']);
    return $validator;
});
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
      'value'    => $csrf->getTokenValue(),
    ]);
    $twig->getEnvironment()->addGlobal('app', [
        'request' => $_REQUEST,
        'session' => $_SESSION,
        'user' =>  $auth->user()
    ]);
    $twig->addExtension(new \Twig\Extension\DebugExtension());
   // Add custom ucfirst Filter
    $twig->getEnvironment()
        ->addFilter(new TwigFilter('ucfirst', function ($str) {
            return ucfirst($str);
        }
    ));
    $twig->getEnvironment()
        ->addFilter(new TwigFilter('ucwords', function ($str) {
            return ucwords($str);
        }
    ));
    $twig->getEnvironment()->addFilter(new TwigFilter('lower', function ($str) {
        return strtolower($str);  // Convert to lowercase
    }));
    
    // Add the custom function to the Twig environment
    $twig->getEnvironment()->addFunction(new TwigFunction('attribute', function($object, $key){
        if (is_array($object) && array_key_exists($key, $object)) {
            return $object[$key];
        } elseif (is_object($object) && property_exists($object, $key)) {
            return $object->$key;
        }
        return null;  // Return null if not found
    }));

    return $twig;
});
$container->set(FlashMessage::class, fn() => new FlashMessage());
// Contrôleur avec injection
$container->set(Controller::class, function ($c) {
    return new Controller(
        $c->get(Twig::class),
        $c->get(Validator::class),
        $c->get(Session::class),
        $c->get(Auth::class),
        $c->get(FlashMessage::class)
    );
});
$app->add(new App\Middleware\ErrorTypeMiddleware($container->get(Twig::class)));


### ───────────────────────────────────────────────
### ROUTES Principal
### ───────────────────────────────────────────────
require_once App\path('config', 'routes', "base.php");
require_once App\path('config', 'routes', "api.php");

### ───────────────────────────────────────────────
### MIDDLEWARES
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));

### ───────────────────────────────────────────────
### RUN
### ───────────────────────────────────────────────
$app->run();