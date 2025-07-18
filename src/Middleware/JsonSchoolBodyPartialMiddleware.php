<?php

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Model\School;


class JsonSchoolBodyPartialMiddleware extends Middleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $data = [];
        $data['success'] = false;
        $data['messages'] = [];
        if (strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $body = (string) $request->getBody();
            $jsonObject = json_decode($body); // Or fa
            if (is_object($jsonObject) && count(get_object_vars($jsonObject))> 1) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Too many fields, only one required for this verbe."
                ], 400);
            }
            $refClass = new \ReflectionClass(School::class);
            $propSchools = $refClass->getProperties();
            $propExisted = true;
            $field = null;
            foreach ($propSchools as $propSchool) {
                $named = $propSchool->getName();
                $fields = ['id', 'images', 'adresses', 'maximage'];
                if (!in_array($named, $fields)) {
                    if (property_exists($jsonObject, $named)) {
                        $propExisted = false;
                        $field = $named;
                        break;
                    } 
                }
            }
            if ($propExisted) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "No field to update are provided."
                ], 400);
            } else {
                if (is_object($jsonObject) && empty($jsonObject->$field)) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "School ".ucfirst($field)." cannot be blank."
                    ], 400);
                }
            }
        }
        // Après la route : ajouter un header
        return $handler->handle($request);
    }
}
