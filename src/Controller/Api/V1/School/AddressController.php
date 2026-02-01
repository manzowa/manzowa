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
            $this->ensureValidArguments(
                "School ID number cannot be blank or string. It's must be numeric",
                $school_id
            );

            try {
                // Establish the connection Database
                $repository = new AddressRepository(Connexion::read());
                $addresses = $repository->retrieve(schoolid: $school_id);
                $rowCounted = $repository->rowCount();

                if ($rowCounted === 0) {
                    return $this->response(false, 'No addresses found', null, 404);
                }
                return $this->response(true, 'Addresses retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "adressses" => $addresses
                ], 200);

            } catch (AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments(
                "School ID number cannot be blank or string. It's must be numeric",
                $school_id
            );
            
            $jsonObject = $request->getParsedBody();
            try 
            {
                $repository = new SchoolRepository(Connexion::read());
                $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->response(false, 'School Not Found', null, 404);
                }
        
                $address = Address::fromObject(data: $jsonObject)
                    ->setEcoleId(ecoleid: $school_id);

                $repository = new AddressRepository(Connexion::write());
                $repository->exists(address: $address);

                if ($repository->exists(address: $address)) {    
                    return $this->response(false, 'Address already exists', null, 409);
                }
            
                $repository->add(address: $address);
                if ($repository->rowCount() === 0) {  
                    return $this->response(false, 'Failed to add address', null, 400);
                }
                $lastInsertID = (int) $repository->lastInsertId();
                $rows = $repository->retrieve(id: $lastInsertID);

                if ($repository->rowCount() === 0) {   
                    return $this->response(false, 'Failed to retrieve address after creation', null, 400);
                }
                $row = current($rows);
                $stateAdresse= Address::fromState(data: $row);

                return $this->response(true, 'Address added successfully', [
                    "rows_returned" => $repository->rowCount(),
                    "adressses" => $stateAdresse->toArray()
                ], 201);
            } catch (AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments(
                "School ID or Addres ID cannot be blank or string. It's must be numeric",
                $school_id, $address_id
            );
        
            try 
            {
                // Establish the connection Database
                $repository = new AddressRepository(Connexion::read());
                $addresses = $repository->retrieve(
                    id: $address_id , schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();
                if ($rowCounted === 0) {
                    return $this->response(false, 'Address not found', null, 404);
                }
                
                return $this->response(true, 'Address retrieved successfully', [
                    "rows_returned" => $repository->rowCount(),
                    "adressses" => $addresses
                ], 200);
            } catch (AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments(
                "School ID or Addres ID cannot be blank or string. It's must be numeric",
                $school_id, $address_id
            );
        
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
                    return $this->response(false, 'Address not found', null, 404);
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
                    return $this->response(false, 'Address not updated', null, 409);
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
                    return $this->response(false, 'Address not found after update', null, 404);
                }

                return $this->response(true, 'Address updated successfully', [
                    "rows_returned" => $repository->rowCount(),
                    "adressses" => $addressRows
                ], 200);
            } catch (AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments(
                "School ID or Addres ID cannot be blank or string. It's must be numeric",
                $school_id, $address_id
            );
        
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
                return $this->response(false, 'No updatable fields were provided', null, 400);
            }

            // Prepare Data
            try {
                // Establish the connection Database
                $repository = new AddressRepository(Connexion::write());
                $addressRows = $repository->retrieve(
                    id: $address_id, schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted === 0) {
                    return $this->response(false, 'Address not found', null, 404);
                }
                $addressRow = current($addressRows);
                $address = Address::fromState(data: $addressRow);
                $method ="set".ucfirst($field);
                $address->$method($jsonObject->$field);
                $repository->update(address: $address);

                if ($repository->rowCount() === 0) {
                    return $this->response(false, 'Address not updated', null, 409);
                }
                // Fetch after Update
                $addressRows = $repository->retrieve(
                    id: $address_id, 
                    schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();
                
                if ($rowCounted === 0) {
                    return $this->response(false, 'Address not found after update', null, 404);
                }
                return $this->response(true, 'Address updated successfully', [
                    "rows_returned" => $rowCounted,
                    "adressses" => $addressRows
                ], 200);
                
            } catch (AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments(
                "School ID or Addres ID cannot be blank or string. It's must be numeric",
                $school_id, $address_id
            );
            
            $repository = new AddressRepository(Connexion::write());
            try {
                 // Establish the connection Database
                
                $addressRows = $repository->retrieve(
                    id: $address_id, schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();
                 
                if ($rowCounted === 0) {
                    return $this->response(false, 'Address not found', null, 404);
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
                    return $this->response(false, 'Address not found', null, 404);
                }
                $repository->commit();

                return $this->response(true, "Address {$address_id} deleted successfully", null, 200);


            } catch (AddressException $ex) {
                if ($repository->inTransaction()) {
                    $repository->rollBack();
                }
                return $this->response(false, $ex->getMessage(), null, 500);
            }            
        }
    }
}