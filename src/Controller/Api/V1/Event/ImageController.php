<?php

/**
 * File ImageController
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App\Controller\Api\V1\Event
 * @package  App\Controller\Api\V1\Event
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */

namespace App\Controller\Api\V1\Event 
{

    use App\Database\Connexion;
    use App\Repository\EventRepository;
    use App\Repository\ImageRepository;
    use App\Exception\ImageException;
    use App\Exception\EventException;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use App\Model\Image;
    use App\Model\Event;

    use function PHPSTORM_META\type;

    class ImageController extends \App\Controller\ApiController
    {
        /**
         * Method getEventImagesAction [GET]
         * 
         * Il permet de recupère les écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getEventImagesAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'] ?? null;
            $event_id  = (int) $args['evenementid'] ?? null;

            // Check Parameter School Id
            if ($this->checkArguments($event_id) === false) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Event ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }
            
            try 
            {
                $connexionRead = Connexion::Read();
                $repository = new ImageRepository($connexionRead);
                $images = $repository->retrieve(
                    eventid: $event_id, 
                    schoolid: $school_id, 
                    type: 'E'
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Images Not Found."
                    ], 500);
                }
    
                $returnData['rows_returned'] = $rowCounted;
                $returnData['images'] = $images;

                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);
            } catch (ImageException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }

         /**
         * Method postEventImagesAction [POST]
         * 
         * Il permet d'ajouter des images
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function postEventImagesAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'];
            $event_id  = (int) $args['evenementid'] ?? null;


            // Check Parameter School Id
            if ($this->checkArguments($event_id) === false) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Event ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }
            $data = $request->getParsedBody();
            $attributesRow = $data['attributes']?? false;
            $attributes = json_decode($attributesRow);

            // Check FIle
            $uploadedFiles = $request->getUploadedFiles();
            $uploadedFile = $uploadedFiles['imagefile'] ?? false;

            try 
            {
                $connexionRead = Connexion::read();
                $repository = new EventRepository($connexionRead);
                $eventRows =  $repository->retrieve(id: $event_id, schoolid: $school_id);
                $rowCounted =  $repository->getTempRowCounted();
                
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Event Not Found."
                    ], 500);
                }
                $eventRow = current($eventRows);
                $event = Event::fromState(data: $eventRow);

            } catch (EventException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }

            $connexionWrite = Connexion::write();
            $repository = new ImageRepository($connexionWrite);
            try 
            {
                if ($event && (!is_null($event->getMaximage()) && $event->isMaximunImage())) {
                    $msg = "You can't add this image, the maximum ";
                    $msg .= "number of images has been reached.";
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => $msg
                    ], 400);
                }
    
                $extension = $this->extensionFile(
                    $uploadedFile->getClientFilename()
                );
                $uniquedFile = $this->imageFileUnique(
                    $school_id, $attributes->filename
                );
                $newFilename = $uniquedFile.'.'. $extension;
                $tmpName = $uploadedFile
                    ->getStream()
                    ->getMetadata('uri');
            
                $image = new Image(
                    id: null, 
                    title: $attributes->title, 
                    filename: $newFilename,
                    mimetype: $uploadedFile->getClientMediaType(),
                    type: 'E',
                    ecoleid: $school_id,
                    evenementid: $event_id, 
                    location: "evenements"
                );
                
                //Start Transaction
                $repository->beginTransaction();
                $repository->add(image: $image);

                if ($repository->rowCount() === 0) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'File to upload image'
                    ], 500);
                }
                
                $lastImageID = (int) $repository->lastInsertId();
                $maxima = 1 + intval($event->getMaximage());

                $event->setMaximage(maximage: $maxima);
                $repository->updateEvent(event: $event);

                $rowCounted = $repository->rowCount();
                if ($rowCounted == 0) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Failed to update info Event'
                    ], 500);
                }

                $imageRows = $repository->retrieve(
                    id: $lastImageID,
                    eventid: $event_id,
                    schoolid: $school_id,
                    type: "E"
                );
                $rowCounted = $repository->rowCount();
                if ($rowCounted === 0) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                    $msg = "Failed to retrieve image attributes after upload";
                    $msg.= " - try uploading image aigin";
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => $msg
                    ], 500);
                }
                $imageRow = current($imageRows);
                $newImage = Image::fromState($imageRow);
                $newImage->saveImageFile($tmpName);
                $repository->commit();

                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['images'] = $newImage->toArray();

                return $this->jsonResponse([
                    "success" => true,
                    "data" =>  $returnData
                ], 201);

            } catch (ImageException $ex) {
                if ($repository->inTransaction()) {
                    $repository->rollBack();
                }
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }
       
        /**
         * Method getEventImageAction [GET]
         * 
         * Il permet de recupère les écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function getEventImageAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'] ?? null;
            $event_id  = (int) $args['evenementid'] ?? null;
            $image_id  = (int) $args['imageid'] ?? null;
            // Check Parameter Event ID AND Image ID
            if (!$this->checkArguments($event_id, $image_id)) {
               $msg = 'Event ID or Image ID cannot be blank or string. ';
               $msg.= 'It\'s must be numeric';
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $msg
                ], 400);
            }
            try{
                // Establish the connection Database
                $connexionRead = Connexion::read();
                $repository = new ImageRepository($connexionRead);
                $imageRows = $repository->retrieve(
                    id: $image_id, 
                    eventid: $event_id,
                    schoolid: $school_id,
                    type: 'E'
                );

                if ($repository->rowCount() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Images Not Found"
                    ], 500);
                }

               
                $imageRow = current($imageRows);
                $image = Image::fromState($imageRow);
                $image->returnImageFile();

            } catch (ImageException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }
        /**
         * Method deleteEventImageAction [DELETE]
         * 
         * Il permet de recupère les écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function deleteEventImageAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'] ?? null;
            $event_id  = (int) $args['evenementid'] ?? null;
            $image_id  = (int) $args['imageid'] ?? null;
            // Check Parameter EVENT ID AND IMAGE ID
            if (!$this->checkArguments($event_id, $image_id)) {
                $msg = 'EVENT ID or Image ID cannot be blank or string. ';
                $msg.= 'It\'s must be numeric';
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $msg
                ], 400);
            }

            try 
            {
                $connexionRead = Connexion::read();
                $repository = new EventRepository($connexionRead);
                $eventRows =  $repository->retrieve(
                    id: $event_id, 
                    schoolid: $school_id
                );
                $rowCounted =  $repository->getTempRowCounted();
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Event Not Found."
                    ], 500);
                }
                $eventRow = current($eventRows);
                $event = Event::fromState(data: $eventRow);

            } catch (EventException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
            $connexionWrite = Connexion::write();
            $repository = new ImageRepository($connexionWrite);
            
            try{
                // Start Transaction
                $repository->beginTransaction();
                $imageRows = $repository->retrieve(
                    id: $image_id, 
                    eventid: $event_id,
                    schoolid: $school_id,
                    type: "E"
                );
                $rowCounted = $repository->rowCount();
                if ($rowCounted == 0) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Image Not Found"
                    ], 404);
                }
                $imageRow = current($imageRows);
                $image = Image::fromState($imageRow);
         
                // Remove image
                $repository->remove(
                    id: $image_id, eventid: $event_id,
                    schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();
                if ($rowCounted === 0) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                     return $this->jsonResponse([
                        "success" => false,
                        "message" => "Failed to delete Image $image_id."
                    ], 500);
                }
                $valMAx = intval($event->getMaximage());
                $maxima = ($valMAx > 0)? $valMAx - 1 : $valMAx;
                $event->setMaximage(maximage: $maxima <= 0?  null: $maxima);
                $repository->updateEvent(event: $event);

                $rowCounted = $repository->rowCount();
                if ( $rowCounted == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Failed to update info Event'
                    ], 500);
                }
            
               $image->deleteImageFile();
               $repository->commit();
                return $this->jsonResponse([
                    "success" => true,
                    "message" => "Image $image_id Deleted"
                ], 204);

            } catch (ImageException $ex) {
                if ($repository->inTransaction()) {
                    $repository->rollBack();
                }
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 500);
            }
        }
        /**
         * Method getEventImageAttributesAction [GET]
         * 
         * Il permet de recupère les écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function getEventImageAttributesAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {

            $school_id = (int) $args['id'] ?? null;
            $event_id  = (int) $args['evenementid'] ?? null;
            $image_id  = (int) $args['imageid'] ?? null;

            // Check Parameter School ID
            if (!$this->checkArguments($event_id, $image_id)) {
               $msg = 'Event ID or Image ID cannot be blank or string. ';
               $msg.= 'It\'s must be numeric';
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $msg
                ], 400);
            }
           
            try{
                // Establish the connection Database
                $connexionRead = Connexion::read();
                $repository = new ImageRepository($connexionRead);
                $imageRows = $repository->retrieve(
                    id: $image_id, 
                    schoolid: $school_id,
                    eventid: $event_id
                );

                if ($repository->rowCount() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Images Not Found"
                    ], 500);
                }
                $imageRow = current($imageRows);
                $image = Image::fromState($imageRow);
             
                $returnData['attributes'] =  [
                    "title" =>  $image->getTitle(),
                    "filename" => $image->getFilename(),
                    "mimetype" => $image->getMimetype()
                ];
                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);

            } catch (ImageException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }
         /**
         * Method patchImageAttributesAction [PATCH]
         * 
         * Il permet une modification partiale
         *  
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function patchImageAttributesAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {

            $school_id = (int) $args['id'] ?? null;
            $event_id  = (int) $args['evenementid'] ?? null;
            $image_id  = (int) $args['imageid'] ?? null;
            // Check Parameter School ID
            if (!$this->checkArguments($event_id, $image_id)) {
               $msg = 'Event ID or Image ID cannot be blank or string. ';
               $msg.= 'It\'s must be numeric';
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $msg
                ], 400);
            }
            // Retrieve  Body
            $jsonObject = $request->getParsedBody();
             // Check Field to Update
            if (!isset($jsonObject->title) && !isset( $jsonObject->filename)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "No fields to update are provided."
                ], 400);
            }
            // Establish the connection Database
            $connexionWrite = Connexion::write();
            $repository = new ImageRepository($connexionWrite);

            try {
                // Start Transaction
                $repository->beginTransaction();
                $imageRows = $repository->retrieve(
                    id: $image_id, 
                    schoolid: $school_id,
                    eventid: $event_id
                );
                if ($repository->rowCount() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Images Not Found."
                    ], 500);
                }
                $imageRow = current($imageRows);
                $image = Image::fromState($imageRow);

                // Title
                (!is_null($jsonObject->title) ? $image->setTitle($jsonObject->title): false);
                $originalFilename = null;
                $newFilename = null;
                if (!is_null($jsonObject->filename)) {
                   $newFilename = $this->imageFileUnique(
                        $school_id, $jsonObject->filename
                    ).".".$this->extensionFile($image->getFilename());
                    $originalFilename = $image->getFilename();
                    $image->setFilename($newFilename);
                }
                $repository->update(image: $image);
                if ($repository->rowCount() === 0) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                    $msg  = 'Image attributes not updated - ';
                    $msg .= 'the given values may be the same as  the stored values ';
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => $msg
                    ], 400);
                }
                $imageRows = $repository->retrieve(
                    id: $image_id, 
                    eventid: $event_id,
                    schoolid: $school_id
                );
                if ($repository->rowCount() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "No Images found after Update attributes"
                    ], 500);
                }
                $imageRow = current($imageRows);
                $image = Image::fromState($imageRow);

                
                if (!is_null($originalFilename) && !is_null($jsonObject->filename)) {
                    $image->renameImageFile(
                        oldFilename: $originalFilename,
                        newFilename: $newFilename
                    );
                }
                $repository->commit();
                $returnData = [];
                $returnData['image'] =  $image->toArray();
      
                return $this->jsonResponse([
                    "success" => true,
                    "message" => 'Image attributes updated',
                    "data" => $returnData
                ], 200);

            } catch (ImageException $ex) {
                if ($repository->inTransaction()) {
                   $repository->rollBack();
                }
                return $this->jsonResponse([
                    "success" => false,
                    "message" =>  $ex->getMessage()
                ], 500);
            }
        }
    }
}
