<?php

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Model\Address;

class JsonAddressBodyPartialMiddleware extends Middleware implements MiddlewareInterface
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
            $refClass = new \ReflectionClass(Address::class);
            $propAddresses = $refClass->getProperties();
            $propExisted = true;
            $field = null;
            foreach ($propAddresses as $propAddress) {
                $name = $propAddress->getName();
                $fields = ['id', 'villeDefaut', 'ecoleid'];
                if (!in_array($name, $fields)) {
                    if (property_exists($jsonObject, $name)) {
                        $propExisted = false;
                        $field = $name;
                        break;
                    } 
                }
            }
            if ($propExisted) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "No fields to update are provided."
                ], 400);
            } else {
                if (is_object($jsonObject) && empty($jsonObject->$field)) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Address ".ucfirst($field)." cannot be blank."
                    ], 400);
                }
            }
        }
        return $handler->handle($request);
    }
}
