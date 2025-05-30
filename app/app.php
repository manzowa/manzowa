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
use App\Template\Environment;
use \Dotenv\Dotenv;

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('APP_ROOT') or define('APP_ROOT', dirname(__DIR__));
defined('APP_PUBLIC') or define('APP_PUBLIC', APP_ROOT . DS);
defined('APP_IMAGES_ROOT') or define('APP_IMAGES_ROOT', join(DS, [APP_ROOT, 'public', 'images']));

require_once join(DS, [__DIR__, 'bootstrap.php']);
require_once App\path('app','api-school', 'v1', 'public', 'index.php');
require_once App\path('vendor', 'autoload.php');

$context = [];
$dotenv = Dotenv::createUnsafeImmutable(APP_ROOT)->load();
$environment = new Environment(App\path('views'), '.phtml');

$environment->addGlobal('base', [
    'url' => 'http://manzowa.local',
    'title' => 'manzowa',
    'description' => 'Site de présentation personnelle',
    'keywords' => 'portfolio, service, projets ,web application',
]);
$environment->addGlobal('date', date('l jS \of F Y'));
$environment->addGlobal('name', 'Christian SHUNGU'); 
$environment->addGlobal('email', 'christiashungu@gmail.com');
$environment->addGlobal('phone', '+33603620181');
$environment->addGlobal('website', 'http://manzowa.com');
$environment->addGlobal('github', 'https://github.com/manzowa');
$environment->addGlobal('linkedin', 'https://www.linkedin.com/in/christian-shungu-4964b2121/');
$environment->addGlobal('whatsapp', 'https://wa.me/33603620181');
$environment->setVariables($dotenv);