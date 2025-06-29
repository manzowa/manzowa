<?php

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Model\School;
use App\Model\Address;

class JsonSchoolBodyMiddleware extends Middleware implements MiddlewareInterface
{
    public function process(
        Request $request, 
        Handler $handler
    ): Response {
        $data = [];
        $data['success'] = false;
        $data['messages'] = [];

        if (strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $body = (string) $request->getBody();
            $jsonObject = json_decode($body); // Or fa
            if (is_object($jsonObject)) {
                $refClass = new \ReflectionClass(School::class);
                $propSchools = $refClass->getProperties();
                $propExisted = false;

                foreach ($propSchools as $propSchool) {
                    $arrFields = ['id', 'images', 'maximage'];
                    if (!in_array($propSchool->getName(), $arrFields)) {
                        if (!property_exists($jsonObject, $propSchool->getName())) {
                            $propExisted = true;
                            $msg = "School field ".strtoupper($propSchool->getName());
                            $msg.= " is mandatory and must be provider";
                            $data['messages'][] = $msg;
                        } else {
                            if (($propSchool->getName() == "adresses")) {
                                $name = $propSchool->getName();
                                $property = $jsonObject->$name;
                                if (!is_array($property)) {
                                    $propExisted = true;
                                    $msg = "School field ".strtoupper($name);
                                    $msg.= " must be a array";
                                    $data['messages'][] = $msg;
                                }

                                if (is_array($property) 
                                    && count($jsonObject->adresses) == 0
                                ) {
                                    $propExisted = true;
                                    $message = "You need an address for this school ";
                                    $data['messages'][] = $message;
                                } else {
                                    foreach ($jsonObject->adresses as $adresse) {
                                        if (is_object($adresse)) {
                                            $refClass = new \ReflectionClass(Address::class);
                                            $propAdresses = $refClass->getProperties();
                                            foreach ($propAdresses as $propAdresse) {
                                                $name = $propAdresse->getName();
                                                $fields =[
                                                    'id', 'reference', 
                                                    'ecoleid', 'villeDefaut'
                                                ];
                                                if (!in_array($name, $fields)) {
                                                    if (!property_exists($adresse, $name)) {
                                                        $propExisted = true;
                                                        $msg = "Address field ".strtoupper($name);
                                                        $msg.= " is mandatory and must be provider";
                                                        $data['messages'][] = $msg;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ($propExisted) {
                    return $this->jsonResponse($data, 400);
                } 
            }
        } 
       return $handler->handle($request);
    }
}
