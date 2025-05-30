<?php 

/**
 * Route
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App\Attribute
 * @package  App\Attribute
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App\Attribute;

use \Attribute;

#[Attribute]
class Route implements RouteInterface 
{
    public function __construct(
        private readonly ?string $path,
        private string $name = 'default',
        private string $method='GET'
        
    ){}
    
    public function getPath(): string {
        return $this->path;
    }
    public function getMethod(): string {
        return $this->method;
    }
    public function getName(): ?string {
        return $this->name;
    }
}