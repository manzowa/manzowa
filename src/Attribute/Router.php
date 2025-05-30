<?php 

/**
 * Router
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category ApiSchool\V1
 * @package  ApiSchool\V1
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App\Attribute;

use App\Attribute\RouteInterface;
use App\Attribute\Route;
use App\Attribute\RouteException;
use App\Http\Response;
use App\Http\Request;

class Router 
{
    private array $_routes = [];
    private ?Route $route;

    public function add(Route $routeObject, callable $callBack): void 
    {
        if ($routeObject instanceof Route && is_callable($callBack)) {
            if (!isset($this->_routes[$routeObject->getPath()])) {
                $this->_routes[$routeObject->getPath()] = [];
                $this->_routes[$routeObject->getPath()][] =  [
                    'callback' => $callBack,
                'route'   => $routeObject
                ];
            } else {
                if ( $routeObject->getName() !== 'default') {
                    $this->_routes[$routeObject->getPath()][] =  [
                        'callback' => $callBack,
                        'route'   => $routeObject
                    ];
                }
            } 
        } 
    }
    public function all(): array 
	{
        return$this->_routes;
    }
    
    public function defaultPath(): string 
	{
        if ($this->getRoute()?->getPath()) {
            return $this->getRoute()?->getPath();
        } else {
            return '/';
        }
    }
    public function relativePath(?string $name= null): ?string 
	{
        $relativePath = null;
        if(is_null($name)) {
            $relativePath= $this->defaultPath();
        } else {
            $bag = $this->getBag($name);
            if (is_array($bag)) {
                $path = $bag['route']->getPath();
                $default = $this->defaultPath();
                $relativePath = "{$default}{$path}";
            }
        }
        return $relativePath;
    }

    private function getBag(?string $name = null): ?array 
    {
        $collects = $this->all();
        $bag = null;
        foreach ($collects as $collect) {
            if (is_array($collect) && count($collect) > 0) {
                foreach($collect as $c) {
                    if (isset($c['route']) && ($c['route']->getName() === $name)) {
                        $bag = $c;
                        break;
                    }
                }
            }
        }
        return $bag;
    }
    
    private function collect($reflection, ?object $arguments = null): void 
	{
       
        $className       = $reflection->getName();
        $classAttributes = $reflection->getAttributes(
            RouteInterface::class, 
            \ReflectionAttribute::IS_INSTANCEOF
        );

        $classMethods    = $reflection->getMethods(\ReflectionProperty::IS_PUBLIC);
		$instanceClass   = (new $className)?? null;
        is_object($instanceClass) ? $instanceClass->setEnvironment($arguments) : false;
        // Attribute out
        if (count($classAttributes) > 0) {
            foreach($classAttributes as $attribute) {
                if ($attribute->getName() === Route::class) {
                    $route = $attribute->newInstance();
                    $this->setRoute($route);
                }
            }
        } 
    
        // Attribute innner
        foreach($classMethods as $classMethod) {
            $refMethod  = new \ReflectionMethod($classMethod->class, $classMethod->name);
            $attributes = $refMethod->getAttributes();
            $route = current(array_map(fn($attribute) => $attribute->newInstance(), $attributes));
            $closure = $reflection->getMethod($classMethod->name)->getClosure($instanceClass);
            if ($route instanceof Route && is_callable($closure)) {
                $this->add($route, $closure);
            }
        } 
    }

    public function initControllers(array $controllers = [], ?object $object = null): void 
	{
        if (count($controllers) > 0) {
            foreach($controllers as $controller) {
                $this->collect(new \ReflectionClass($controller), $object);
            }
        }
    }

    public function keyPathMatches(): string|bool{
        $request  = new Request;
        $keys = array_keys($this->all());
        $path= $this->parse($request->uri());
        $path_alter = empty($path) ? "/": $path;
        $returnKey = false;
        foreach ($keys as $key) {
            if (preg_match("#^".$key."$#", $path_alter, $matches)) {
                $returnKey = $key;
                break;
            }
        }
        return $returnKey;
    } 

    public function parse($path) 
    {
        $path= str_replace(
            $this->defaultPath(), '', 
            str_replace(ltrim($this->defaultPath(), "/"), '', $path)
        );
        $path= str_replace("//", '/', $path);
        return $path;
    }

    public function call()
    {
        $response = new Response;
        $request  = new Request;
        $collects = $this->all();
        $method = $request->getMethod();
        if (!$keyRoute = $this->keyPathMatches()) {
            throw new RouteException('Endpoint not found',404);
        }

        $bags = isset($collects[$keyRoute])? $collects[$keyRoute] : [];
        $bag  = array_filter($bags, function($b) use($method) {
            return (isset( $b['route']) && $b['route']->getMethod() === $method);
        });
        $currentBag = (object) current($bag);

        if (array_key_exists($keyRoute,  $collects) && !isset($currentBag->route)) {
            throw new RouteException('Method not found',405);
        }
        echo call_user_func_array($currentBag->callback, [$request, $response]);
        exit; 
    }

    /**
     * Get the value of default
     *
     * @return ?Route
     */
    public function getRoute(): ?Route {
        return $this->route?? null;
    }

    /**
     * Set the value of default
     *
     * @param ?Route $default
     *
     * @return self
     */
    public function setRoute(?Route $route): self {
        $this->route = $route;
        return $this;
    }
}