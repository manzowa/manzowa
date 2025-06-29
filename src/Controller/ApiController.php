<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class ApiController
{

    
    protected function jsonResponse(
        array $data, 
        int $status
    ): Response {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }

    protected function checkArguments(...$args): bool 
    {
        foreach ($args as $index => $arg) {
            if (!is_numeric($arg) || $arg == 0 || $arg === '' || $arg === null) {
                echo "Argument à la position $index invalide : $arg\n";
                return false;
            }
        }
        return true;
    }

    protected function imageFileUnique(int $schoolid, $filename): string 
    {
        $file = uniqid("img_".$schoolid."_".$filename.'_', true);
        return $file;
    }

    protected function extensionFile(string $filename): string 
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($extension);
    }
}