<?php

/**
 * File ImageController
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App\Controller\Api\V1\School
 * @package  App\Controller\Api\V1\School
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */

namespace App\Controller\Api\V1\School
{

    use App\Database\Connexion;
    use App\Repository\SchoolRepository;
    use App\Repository\ImageRepository;
    use App\Exception\ImageException;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use App\Model\Image;
    use App\Model\School;

    class ImageController extends \App\Controller\ApiController
    {
        /**
         * Method getImagesAction [GET]
         * 
         * Il permet de recupère les écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getImagesAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'];
            // Check Parameter School Id
            if (!$this->checkArguments($school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }
            try 
            {
                $connexionRead = Connexion::Read();
                $repository = new ImageRepository($connexionRead);
                $images = $repository->retrieve(schoolid: $school_id);
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
         * Method postImagesAction [POST]
         * 
         * Il permet d'ajouter des images
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function postImagesAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'];

            // Check Parameter School Id
            if (!$this->checkArguments($school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "School ID number cannot be blank or string. It's must be numeric"
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
                $connexionWrite = Connexion::write();
                $repositorySchool = new SchoolRepository($connexionWrite);
                $schoolRows = $repositorySchool->retrieve(id: $school_id);
                $rowCounted = $repositorySchool->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "School Not Found."
                    ], 500);
                }
                
                $schoolRow = current($schoolRows);
                $school = School::fromState($schoolRow);

                if (!is_null($school->getMaximage()) && $school->isMaximunImage()) {
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
                    ecoleid: $school_id
                );
                $repository = new ImageRepository($connexionWrite);
                if ($repository->imageExists($attributes->filename, $school_id)) {
                    $msg = 'A file with that filename already exists ';
                    $msg.= '- try a different filename';
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => $msg
                    ], 409);
                }

                // Start Transaction
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
                $maxima = 1 + intval($school->getMaximage());
                $school->setMaximage(maximage: $maxima);
                $repository->updateSchool(school: $school);
                if ($repository->getTempRowCounted() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Failed to update info School'
                    ], 500);
                }

                $imageRows = $repository->retrieve(id: $lastImageID);
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
                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['images'] = $newImage->toArray();

                return $this->jsonResponse([
                    "success" => true,
                    "data" =>  $returnData
                ], 201);
            } catch (ImageException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }

        }
       
        /**
         * Method getImageAction [GET]
         * 
         * Il permet de recupère les écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function getImageAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'];
            $image_id  = (int) $args['imageid'];
            // Check Parameter School ID
            if (!$this->checkArguments($school_id, $image_id)) {
               $msg = 'School ID or Image ID cannot be blank or string. ';
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
                $imageRows = $repository->retrieve($image_id, $school_id);

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
         * Method deleteImageAction [DELETE]
         * 
         * Il permet de recupère les écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function deleteImageAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'];
            $image_id  = (int) $args['imageid'];
            // Check Parameter School ID
            if (!$this->checkArguments($school_id, $image_id)) {
               $msg = 'School ID or Image ID cannot be blank or string. ';
               $msg.= 'It\'s must be numeric';
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $msg
                ], 400);
            }
            // Establish the connection Database
            $connexionRead = Connexion::read();
            try{
                
                $repositorySchool = new SchoolRepository($connexionRead);
                $schoolRows = $repositorySchool->retrieve(id: $school_id);
                $rowCounted = $repositorySchool->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "School Not Found."
                    ], 500);
                }
                
                $schoolRow = current($schoolRows);
                $school = School::fromState($schoolRow);
                $repository = new ImageRepository($connexionRead);

                // Start Transaction
                $repository->beginTransaction();
                $imageRows = $repository->retrieve(id: $image_id, schoolid: $school_id);
                if ($repository->rowCount() === 0) {
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

                $repository->remove(id: $image_id, schoolid: $school_id);

                if ($repository->rowCount() === 0) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                     return $this->jsonResponse([
                        "success" => false,
                        "message" => "Failed to delete Image $image_id."
                    ], 500);
                }
                $valMAx = intval($school->getMaximage());
                $maxima = ($valMAx > 0)? $valMAx - 1 : $valMAx;
                $school->setMaximage(maximage: $maxima);
                $repository->updateSchool(school: $school);

                if ( $repository->getTempRowCounted() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Failed to update info School'
                    ], 500);
                }
                $image->deleteImageFile();
                $repository->commit();

                return $this->jsonResponse([
                    "success" => true,
                    "message" =>"Image $image_id Deleted"
                ], 204);
            } catch (ImageException $ex) {
                if ($connexionRead->inTransaction()) {
                    $connexionRead->rollBack();
                }
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 500);
            }
        }

        /**
         * Method getImageAttributesAction [GET]
         * 
         * Il permet de recupère les écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function getImageAttributesAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {

            $school_id = (int) $args['id'];
            $image_id  = (int) $args['imageid'];
            // Check Parameter School ID
            if (!$this->checkArguments($school_id, $image_id)) {
               $msg = 'School ID or Image ID cannot be blank or string. ';
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
                $imageRows = $repository->retrieve($image_id, $school_id);

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

            $school_id = (int) $args['id'];
            $image_id  = (int) $args['imageid'];
            // Check Parameter School ID
            if (!$this->checkArguments($school_id, $image_id)) {
               $msg = 'School ID or Image ID cannot be blank or string. ';
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
                    schoolid: $school_id
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
