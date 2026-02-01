<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helper\JsonResponseTrait;


class ApiController
{
    use JsonResponseTrait;

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

    protected function ensureValidArguments( string $message = '', ...$args): Response|bool
    {
        if (!$this->checkArguments(...$args)) {
            return $this->jsonResponse([
                "success" => false,
                "message" => $message
            ], 400);
        }

        return true;
    }
}