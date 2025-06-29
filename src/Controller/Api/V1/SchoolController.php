<?php

/**
 * File SchoolController
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App\Controller\Api\V1
 * @package  App\Controller\Api\V1
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */

namespace App\Controller\Api\V1 
{

    use App\Database\Connexion;
    use App\Repository\SchoolRepository;
    use App\Exception\SchoolException;
    use App\Exception\AddressException;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use App\Model\School;

    class SchoolController extends \App\Controller\ApiController
    {
        /**
         * Method getSchoolsAction [GET]
         * 
         * Il permet de recupère les écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getSchoolsAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            try 
            {
                $connexionRead = Connexion::read();
                $repository = new SchoolRepository($connexionRead);
                $schools = $repository->retrieve();
                $rowCounted = $repository->getTemprowCounted();
                if ($rowCounted == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'School not found'
                    ], 400);
                }

                $returnData['rows_returned'] = $rowCounted;
                $returnData['schools'] = $schools;
                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);
            } catch (SchoolException | AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }
        /**
         * Method postSchoolsAction [POST]
         * 
         * Il permet d'ajouter une école
         *
         * @param Request $request
         * @param Response $response
         * @param array $args
         * 
         * @return mixed
         */
        public function postSchoolsAction(
            Request $request, 
            Response $response, 
            array $args
        ) {
            $jsonObject = $request->getParsedBody();
            try 
            {
                $school = new School(
                    id: NULL,
                    nom: $jsonObject->nom ?? NULL,
                    email: $jsonObject->email ?? NULL,
                    telephone: $jsonObject->telephone ?? NULL,
                    type: $jsonObject->type ?? NULL,
                    site: $jsonObject->site ?? NULL,
                    adresses: $jsonObject->adresses?? []
                );
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new SchoolRepository($connexionWrite);
                $repository->retrieveByName(nom: $school->getNom());
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted !== 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'School already exists'
                    ], 422);
                }
                // Add new Ecole
                $repository->add(school: $school);
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Failed to create school'
                    ], 500);
                    
                }
                $school_id = (int) $repository->getStockId();
                $schools = $repository->retrieve($school_id);
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Failed to retrieve school after creation'
                    ], 404);
                }

                $returnData = [];
                $returnData['rows_inserted'] =  $rowCounted;
                $returnData['school'] = current($schools);

                return $this->jsonResponse([
                    "success" => true,
                    "data" =>  $returnData
                ], 201);

            } catch (SchoolException| AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }
        /**
         * Method getSchoolsByPageAction[GET]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function getSchoolsByPageAction(
            Request $request, 
            Response $response, 
            array $args
        ) : Response {

            $page = (int) $args['page'];
            // Check Parameter Page
            if (!$this->checkArguments($page)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Page number cannot be blank or string. It's must be numeric"
                ], 400);
            }
            try 
            {
                // Establish the connection Database
                $connexionRead = Connexion::read();
                $repository = new SchoolRepository($connexionRead);
                $counter = $repository->count();
                // Limit par page;
                $limitPerPage = 10;
                $ecolesCount  = intval($counter);
                $numOfPages   = intval(ceil($ecolesCount / $limitPerPage));
                // First Page
                if ($numOfPages == 0)  $numOfPages = 1;
                if ($numOfPages < $page || 0 == $page) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Page not found."
                    ], 400);
                }
                // Offset Page
                $offset = (($page == 1) ? 0 : ($limitPerPage * ($page - 1)));
                $schoolRows = $repository->retrieveByLimitAndOffset(
                    limit: $limitPerPage, offset: $offset
                );
                $rowCounted = $repository->getTemprowCounted();
                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['total_rows'] = $ecolesCount;
                $returnData['total_pages'] = $numOfPages;
                $returnData['has_next_page'] =  ($page < $numOfPages) ? true : false;
                $returnData['has_privious_page'] =  ($page > 1) ? true : false;
                $returnData['schools'] = $schoolRows;
                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);

            } catch (SchoolException | AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method getSchoolAction [GET]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return void
         */
        public function getSchoolAction(
            Request $request, 
            Response $response, 
            array $args 
        ): Response {
            $school_id = (int) $args['id'];
            // Check Parameter School Id
            if (is_null($school_id) || empty($school_id) || !is_numeric($school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

            try {
                // Establish the connection Database
                $connexionRead = Connexion::read();
                $repository = new SchoolRepository($connexionRead);
                $schools = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "School Not Found."
                    ], 500);
                }
                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['school'] = current($schools);

                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);

            } catch (SchoolException | AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method putSchoolAction [PUT]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function putSchoolAction(
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
            // Retrive data from Json
            $jsonObject = $request->getParsedBody();
            try 
            {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new SchoolRepository($connexionWrite);
                $schoolRows = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();
                $schoolRow = current($schoolRows);

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'School Not Found.'
                    ], 500);
                }
                $school = School::fromState(data: $schoolRow);
                $school
                    ->setNom(nom: $jsonObject->nom)
                    ->setEmail(email: $jsonObject->email) 
                    ->setTelephone(telephone: $jsonObject->telephone) 
                    ->setType(type: $jsonObject->type)
                    ->setSite(site: $jsonObject->site);
                $repository->update(school: $school);
                if ($repository->rowCount() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'School not updated.'
                    ], 400);
                }
                // Fetch after Update
                $schoolRows = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();
                $schoolRow = current($schoolRows);

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'No school fetched after update.'
                    ], 404);
                }
                $returnData = [];
                $returnData['rows_counted'] =  $rowCounted;
                $returnData['school'] = $schoolRow;

                return $this->jsonResponse([
                    "success" => true,
                    "data"=> $returnData
                ], 200);
            } catch (SchoolException | AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method patchSchoolAction [PATCH]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function patchSchoolAction(
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
            // Retrieve  Body
            $jsonObject = $request->getParsedBody();
            try {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new SchoolRepository($connexionWrite);
                $schoolRows = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();
                $schoolRow = current($schoolRows);

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'School Not Found.'
                    ], 500);
                }
                $refClass = new \ReflectionClass(School::class);
                $props = $refClass->getProperties();
                $field = null;
                $propExisted = false;
                foreach ($props as $prop) {
                    if (property_exists($jsonObject, $prop->getName())) {
                        $propExisted = true;
                        $field = $prop->getName();
                    } 
                }
                $school = School::fromState(data: $schoolRow);
                if ($propExisted) {
                    $method ="set".ucfirst($field);
                    $school->$method($jsonObject->$field);
                }
                $repository->update(school: $school);
                if ($repository->rowCount() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'School not updated.'
                    ], 400);
                }

                // Fetch after Update
                $schoolRows = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();
                $schoolRow = current($schoolRows);

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'No school fetched after update.'
                    ], 404);
                }

                $returnData = [];
                $returnData['rows_counted'] = $rowCounted;
                $returnData['school'] =  $schoolRow;
                return $this->jsonResponse([
                    "success" => true,
                    "data"=> $returnData
                ], 200);
            } catch (SchoolException | AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method deleteSchoolAction  [DELETE]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function deleteSchoolAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'];
            // Check Parameter School Id
            // Check Parameter School Id
            if (!$this->checkArguments($school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

            try {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new SchoolRepository($connexionWrite);
                $schoolRows = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'School Not Found.'
                    ], 500);

                }
                $repository->remove(id: $school_id);
                $rowCounted = $repository->rowCount();
                if ($repository->rowCount()=== 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'School not found for to delete.'
                    ], 404);

                }
                $returnData = [];
                $returnData['rows_deleted'] = $rowCounted;

                return $this->jsonResponse([
                    "success" => true,
                    "message" => "School $school_id deleted",
                    "data" => $returnData,
                ], 204);
            } catch (SchoolException | SchoolException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method getNameAction  [GET]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function getNameAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response  {
            $nom = urldecode(htmlspecialchars($args['nom'], ENT_QUOTES));
            // Check Parameter School Name
            if (is_null($nom) || empty($nom)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "School name cannot be blank"
                ], 400);
            }
            try {
                // Establish the connection Database
                $connexionRead = Connexion::read();
                $repository = new SchoolRepository($connexionRead);
                $schools = $repository->searchByName(nom: $nom);
                $rowCounted = $repository->getTemprowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "School Not Found."
                    ], 500);
                }

                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['schools'] =  $schools;

                return $this->jsonResponse([
                    "success" => false,
                    "data" => $returnData
                ], 200);
            } catch (SchoolException | AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
        /**
         * Method getNameLimitAction  [GET]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function getNameLimitAction(
            Request $request, 
            Response $response,
            array $args
        ) : Response {

            $nom = urldecode(htmlspecialchars($args['nom'], ENT_QUOTES));
            $limit = (int) ($args['limit']?? 10);
            // Check Parameter School Name
            if (is_null($nom) || empty($nom)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => 'School name cannot be blank'
                ], 400);
            }

            try {
                // Establish the connection Database
                $connexionRead = Connexion::read();
                $repository = new SchoolRepository($connexionRead);
                $schools = $repository->searchByName(nom: $nom, limit: $limit);
                $rowCounted = $repository->getTemprowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "School Not Found."
                    ], 500);
                }
                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['schools'] =  $schools;

                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);
            } catch (SchoolException | AddressException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
    }
}
