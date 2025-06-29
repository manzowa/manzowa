<?php

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Model\User;

class JsonUserBodyMiddleware extends Middleware implements MiddlewareInterface
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
            $jsonObject = json_decode($body); 

            // Check username and password 
            if (is_object($jsonObject)) {
                $refClass = new \ReflectionClass(User::class);
                $propUsers = $refClass->getProperties();
                $propExisted = false;
                foreach ($propUsers as $propUser) {
                    $arrFields = ['id', 'fullname', 'email', 'active', 'attempts'];
                    $nameProp = $propUser->getName();
                    if (!in_array($nameProp, $arrFields)) {
                        if (!property_exists($jsonObject, $nameProp)) {
                            $propExisted = true;
                            $msg = "User field ".strtoupper($nameProp). " not supplied";
                            $data['messages'][] = $msg;
                        } else {
                            if (isset($jsonObject->$nameProp) 
                                && (
                                    empty($jsonObject->$nameProp)
                                    || (strlen($jsonObject->$nameProp) < 1)
                                )
                            ) {
                                $propExisted = true;
                                $msg = "User field ".strtoupper($nameProp);
                                $msg.= " cannot be empty";
                                $data['messages'][] = $msg;
                            }

                            if ((strlen($jsonObject->$nameProp) > 255)) {
                                $propExisted = true;
                                $msg = "User field ".strtoupper($nameProp);
                                $msg.= " must be less than 255 characters";
                                $data['messages'][] = $msg;
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
