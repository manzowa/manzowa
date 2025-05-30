<?php 
/**
 * This file is part of school_manager
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App
 * @package  App
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App
{
    use \App\Http\Response;
    use \App\Attribute\Router;
    use \App\Attribute\RouteException;
    use App\Template\Environment;

    /**
     * Class Application 
     * @package  App
     */
    class Runtime
    {
        public static function run(Environment $environment)
        {
            try {
                $router = new Router();
                $configRoutes = static::arrayControlles();
                $router->initControllers($configRoutes, $environment);
                $router->call();
            } catch (RouteException $e) {
                $response = new Response;
                $response->json(
                    statusCode: $e->getCode(), 
                    success: false, 
                    message: $e->getMessage()
                );
            }
        }
        private static function arrayControlles(): array {
            $c = include join(DS, [APP_ROOT, 'config', '_router.php']);
            return $c;
        }
    }
}