<?php 

/**
 * File AddressController
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
    use App\Exception\AddressException;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use App\Repository\AddressRepository;
    use App\Model\Address;

    class AddressController extends \App\Controller\ApiController
    {
        /**
         * Method getAdressesAction [GET]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function getAdressesAction(
            Request $request,  
            Response $response,
            array $args
        ): Response
        {
            $school_id = (int) $args['id'];
            // Check Parameter School Id
            if (!$this->checkArguments($school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

            try {
                // Establish the connection Database
                $connexionRead = Connexion::read();
                $repository = new AddressRepository($connexionRead);
                $addresses = $repository->retrieve(schoolid: $school_id);
                $rowCounted = $repository->rowCount();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Address Not Found.'
                    ], 500);
                }
                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['adressses'] = $addresses;

                return $this->jsonResponse([
                    "success" => true,
                    "data"=> $returnData
                ], 200);

            } catch (AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method postAdressesAction [POST]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function postAdressesAction(
            Request $request, 
            Response $response, 
            array $args 
        ) : Response {
    
            $school_id = (int) $args['id'];
           // Check Parameter School Id
            if (!$this->checkArguments($school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }
            $jsonObject = $request->getParsedBody();
            try 
            {
                $connexionRead = Connexion::Read();
                $repository = new SchoolRepository($connexionRead);
                $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "School Not Found."
                    ], 500);
                }
            
                $address = new Address(
                    id: NULL, 
                    voie: $jsonObject->voie?? NULL, 
                    quartier:$jsonObject->quartier ?? NULL, 
                    commune: $jsonObject->commune?? NULL, 
                    district: $jsonObject->district ?? NULL, 
                    ville: $jsonObject->ville ?? NULL, 
                    reference: $jsonObject->reference?? NULL, 
                    ecoleid: $school_id
                );

                $connexionWrite = Connexion::write();
                $repository = new AddressRepository($connexionWrite);
                $repository->exists(address: $address);

                if ($repository->exists(address: $address)) {    
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Address already exists"
                    ], 409);
                }
            
                $repository->add(address: $address);
                if ($repository->rowCount() === 0) {  
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Failed to add address."
                    ], 400);
                }
                $lastInsertID = (int) $repository->lastInsertId();
                $rows = $repository->retrieve(id: $lastInsertID);

                if ($repository->rowCount() === 0) {   
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Failed to retrieve address after to create."
                    ], 400);
                }
                $row = current($rows);
                $stateAdresse= Address::fromState(data: $row);

                $returnData = [];
                $returnData['rows_returned'] = $repository->rowCount();
                $returnData['adressses'] = $stateAdresse->toArray();

                return $this->jsonResponse([
                    "success" => true,
                    "data" =>  $returnData
                ], 201);
            } catch (AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage(),
                ], 400);
            }
        }
        /**
         * Method getAdresseAction [GET]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function getAdresseAction(
            Request $request,
            Response $response, 
            array $args
        ): Response {
            
            $school_id = (int) $args['id'];
            $address_id = (int) $args['adresseid'];
            // Check Parameter School ID
            if (!$this->checkArguments($school_id, $address_id)) {
               $msg = 'School ID or Addres ID cannot be blank or string. ';
               $msg.= 'It\'s must be numeric';
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $msg
                ], 400);
            }

            try 
            {
                // Establish the connection Database
                $connexionRead = Connexion::read();
                $repository = new AddressRepository($connexionRead);
                $addresses = $repository->retrieve(
                    id: $address_id , schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" =>'Adresse Not Found.'
                    ], 500);
                }
                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['adressse'] = $addresses;

                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);
            } catch (AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method putAdresseAction [PUT]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function putAdresseAction(
            Request $request, 
            Response $response,
            array $args
        ): Response {
            
            $school_id = (int) $args['id'];
            $address_id = (int) $args['adresseid'];
            // Check Parameter School ID
            if (!$this->checkArguments($school_id, $address_id)) {
               $msg = 'School ID or Addres ID cannot be blank or string. ';
               $msg.= 'It\'s must be numeric';
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $msg
                ], 400);
            }
            $jsonObject = $request->getParsedBody();
            // Prepare Data
            try 
            {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new AddressRepository($connexionWrite);
                $addressRows = $repository->retrieve(
                    id: $address_id, schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" =>'Adresse Not Found.'
                    ], 500);

                }
                $addressRow = current($addressRows);
                $address = Address::fromState(data: $addressRow);
                $address
                    ->setVoie(voie: $jsonObject->voie ?? $address->getVoie())
                    ->setQuartier(quartier: $jsonObject->quartier ?? $address->getQuartier())
                    ->setDistrict(district: $jsonObject->district ?? $address->getDistrict())
                    ->setCommune(commune: $jsonObject->commune ?? $address->getCommune())
                    ->setVille(ville: $jsonObject->ville ?? $address->getVille());
                $repository->update(address: $address);

                if ($repository->rowCount() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Address not updated.'
                    ], 400);
                }
                // Fetch after Update
                $addressRows = $repository->retrieve(
                    id: $address_id, 
                    schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();
                
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'No Address found after update.'
                    ], 404);
                }

                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['adressse'] =$addressRows;

                 return $this->jsonResponse([
                    "success" => true,
                    "data"=> $returnData
                ], 200);
            } catch (AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method patchAdresseAction [PATCH]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function patchAdresseAction(
            Request $request, 
            Response $response,
            array $args
        ): Response {
           
            $school_id = (int) $args['id'];
            $address_id = (int) $args['adresseid'];
            // Check Parameter School ID
            if ((is_null($school_id)  || empty($school_id))
                || (is_null( $address_id) || empty( $address_id))
            ) {
               $msg = 'School ID or Addres ID cannot be blank or string. ';
               $msg.= 'It\'s must be numeric';
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $msg
                ], 400);
            }

            $jsonObject = $request->getParsedBody();
   
            $refClass = new \ReflectionClass(Address::class);
            $props = $refClass->getProperties();
            $field = null;
            $propExisted = true;
            foreach ($props as $prop) {
                if (property_exists($jsonObject, $prop->getName())) {
                    $propExisted = false;
                    $field = $prop->getName();
                } 
            }
            if ($propExisted) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "No fields to update are provided."
                ], 400);
            }

            // Prepare Data
            try {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new AddressRepository($connexionWrite);
                $addressRows = $repository->retrieve(
                    id: $address_id, schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Address Not Found.'
                    ], 500);
                }
                $addressRow = current($addressRows);
                $address = Address::fromState(data: $addressRow);
                $method ="set".ucfirst($field);
                $address->$method($jsonObject->$field);
                $repository->update(address: $address);

                if ($repository->rowCount() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Address not updated.'
                    ], 404);
                }
                // Fetch after Update
                $addressRows = $repository->retrieve(
                    id: $address_id, 
                    schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();
                
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'No Address found after update.'
                    ], 404);
                }
                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['adressse'] =$addressRows;
    
                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);
                
            } catch (AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method deleteAdresseAction [DELETE]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function deleteAdresseAction(
            Request $request, 
            Response $response,
            array $args
        ): Response {

            $school_id = (int) $args['id'];
            $address_id = (int) $args['adresseid'];
            // Check Parameter School ID
            if ((is_null($school_id)  || empty($school_id))
                || (is_null( $address_id) || empty( $address_id))
            ) {
               $msg = 'School ID or Addres ID cannot be blank or string. ';
               $msg.= 'It\'s must be numeric';
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $msg
                ], 400);
            }

            try {
                 // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new AddressRepository($connexionWrite);
                $addressRows = $repository->retrieve(
                    id: $address_id, schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();
                 
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Address Not Found.'
                    ], 500);
                }
                $addressRow = current($addressRows);
                $address = Address::fromState(data: $addressRow);

                // Start transaction
                $repository->beginTransaction();
                $stateId =  $address->getId();
                $repository->remove(id: $stateId);
                $rowCounted = $repository->rowCount();

                if ($rowCounted === 0) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'No Address found to delete.'
                    ], 404);
                }
                $repository->commit();

                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Address $address_id deleted"
                ], 204);

            } catch (AddressException $ex) {
                if ($repository->inTransaction()) {
                    $repository->rollBack();
                }
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }            
        }
    }
}