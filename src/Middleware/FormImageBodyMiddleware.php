<?php 

namespace App\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as Handler;
use \Psr\Http\Message\ResponseInterface as Response;

class FormImageBodyMiddleware extends Middleware implements  MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        if (strpos($request->getHeaderLine('Content-Type'), "multipart/form-data; boundary=") === false) 
        {
            $msg = 'Content type header not set to multipart/form-data ';
            $msg .= 'with a boundary';
            return $this->jsonResponse([
                'success' => false,
                'message' => $msg,
            ], 400);
        }

        $data = $request->getParsedBody();
        $attributesRow = $data['attributes']?? false;
        if (!$attributesRow) {
            return $this->jsonResponse([
                "success" => false,
                "message" => 'Attributes value missing from body of request'
            ], 400);
        }

        // Check Attribute Value if it's Json
        if (!$attributes = json_decode($attributesRow)) {
            return $this->jsonResponse([
                "success" => false,
                "message" => 'Attribute field is not valid JSON.'
            ], 400);
        }

        // Check Attribute Value filename and title
        if ((!isset($attributes->title) || empty($attributes->title))  
                || (!isset($attributes->filename) || empty($attributes->filename))
        ) {
            return $this->jsonResponse([
                "success" => false,
                "message" => 'Title and Filename fields are mandatory'
            ], 400);
        }

        if (strpos($attributes->filename, '.') > 0) {
            return $this->jsonResponse([
                "success" => false,
                "message" => 'Filename must not contain a file extension'
            ], 400);
        }

        // Check FIle
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['imagefile'] ?? false;
         if (!$uploadedFile) {
            return $this->jsonResponse([
                "success" => false,
                "message" => 'Imagefile upload unsuccessful '
            ], 500);
        }
        
        if (!$uploadedFile->getClientFilename()) {
            return $this->jsonResponse([
                "success" => false,
                "message" => 'Make sure you select a file'
            ], 500);
        }
            // MaxSize : Limite à 2 Mo (2 * 1024 * 1024)
        $maxSize = 2 * 1024 * 1024;
        if ($uploadedFile->getSize() > $maxSize) {
            return $this->jsonResponse([
                "success" => false,
                "message" => 'Image too large. Maximum 2 MB allowed. '
            ], 400);
        }

        // Check MIME FILE
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($uploadedFile->getClientMediaType(), $allowedTypes)) {
            return $this->jsonResponse([
                "success" => false,
                "message" => 'File type not supported'
            ], 400);
        }

        return $handler->handle($request);
    }
}