<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use App\Model\Schedule;

class JsonScheduleBodyMiddleware extends Middleware implements MiddlewareInterface
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
            if (is_object($jsonObject)) {
                $refClass = new \ReflectionClass(Schedule::class);
                $propAddresses = $refClass->getProperties();
                $propExisted = false;
                foreach ($propAddresses as $propAddress) {
                    $named = $propAddress->getName();
                    $fields = ['id', 'ecoleid'];
                    if (!in_array($named, $fields)) {
                        if (!property_exists($jsonObject, $named)) {
                            $propExisted = true;
                            $msg = "Schedule field ".strtoupper($named);
                            $msg.= " is mandatory and must be provided";
                            $data['messages'][] = $msg;
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