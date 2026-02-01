<?php

/**
 * File IndexController
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
    use App\Exception\SchoolException;
    use App\Exception\AddressException;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use App\Model\School;

    class IndexController extends \App\Controller\ApiController
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
                $repository = new SchoolRepository(Connexion::read());
                $schools = $repository->retrieve();
                $rowCounted = $repository->getTemprowCounted();
                if ($rowCounted == 0) {
                    return $this->response(false, 'School not found', null, 404);
                }

                return $this->response(true, 'Schools retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "schools" => $schools
                ], 200);

            } catch (SchoolException | AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
                // Establish the connection Database
                $repository = new SchoolRepository(Connexion::write());
                $school = School::fromObject(data: $jsonObject);
                $repository->retrieveByName(nom: $school->getNom());
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted !== 0) {
                    return $this->response(false, 'School already exists', null, 409);
                }
                // Add new Ecole
                $repository->add(school: $school);
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->response(false, 'Invalid school data', null, 422);
                }
                $school_id = (int) $repository->getStockId();
                $schools = $repository->retrieve($school_id);
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->response(false, 'Failed to retrieve school after creation', null, 500);
                }

                return $this->response(true, 'School added successfully', [
                    "rows_inserted" => $rowCounted,
                    "school" => current($schools)
                ], 201);
          
            } catch (SchoolException| AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments(
                'Page number cannot be blank or string. It\'s must be numeric', $page
            );
            try 
            {
                // Establish the connection Database
                $repository = new SchoolRepository(Connexion::read());
                $counter = $repository->count();
                // Limit par page;
                $limitPerPage = 10;
                $ecolesCount  = intval($counter);
                $numOfPages   = intval(ceil($ecolesCount / $limitPerPage));
                // First Page
                if ($numOfPages == 0)  $numOfPages = 1;
                if ($numOfPages < $page || 0 == $page) {
                    return $this->response(false, 'Page not found', null, 404);
                }
                // Offset Page
                $offset = (($page == 1) ? 0 : ($limitPerPage * ($page - 1)));
                $schoolRows = $repository->retrieveByLimitAndOffset(
                    limit: $limitPerPage, offset: $offset
                );
                $rowCounted = $repository->getTemprowCounted();
                
                return $this->response(true, "Schools retrieved successfully", [
                    "rows_returned" => $rowCounted,
                    "total_rows" => $ecolesCount,
                    "total_pages" => $numOfPages,
                    "has_next_page" => ($page < $numOfPages) ? true : false,
                    "has_privious_page" => ($page > 1) ? true : false,
                    "schools" => $schoolRows
                ], 200);

            } catch (SchoolException | AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
         /**
         * Method getSchoolsByPageAndLimitAction [GET]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function getSchoolsByPageAndLimitAction(
            Request $request, 
            Response $response, 
            array $args
        ) : Response {

            $page = (int) $args['page'];
            $limitParam = (int) ($args['limit']?? 5);
            // Check Parameter Page
            $this->ensureValidArguments(
                "Page number cannot be blank or string. It's must be numeric", $page
            );

            try 
            {
                // Establish the connection Database
                $repository = new SchoolRepository(Connexion::read());
                $counter = $repository->count();
                // Limit par page;
                $limitPerPage =  $limitParam;
                $ecolesCount  = intval($counter);
                $numOfPages   = intval(ceil($ecolesCount / $limitPerPage));
                // First Page
                if ($numOfPages == 0)  $numOfPages = 1;
                if ($numOfPages < $page || 0 == $page) {
                    return $this->response(false, 'Page not found', null, 404);
                }
                // Offset Page
                $offset = (($page == 1) ? 0 : ($limitPerPage * ($page - 1)));
                $schoolRows = $repository->retrieveByLimitAndOffset(
                    limit: $limitPerPage, offset: $offset
                );
                $rowCounted = $repository->getTemprowCounted();
                

                return $this->response(true, "Schools retrieved successfully", [
                    "rows_returned" => $rowCounted,
                    "total_rows" => $ecolesCount,
                    "total_pages" => $numOfPages,
                    "has_next_page" => ($page < $numOfPages) ? true : false,
                    "has_privious_page" => ($page > 1) ? true : false,
                    "schools" => $schoolRows
                ], 200);
            } catch (SchoolException | AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method getSchoolsByPageAndLimitAction [GET]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function getSchoolsByAction(
            Request $request, 
            Response $response, 
            array $args
        ) : Response {

            $page = (int) $args['page'];
            $offsetParam = (int) ($args['offset']?? 5);
            $nomParam = urldecode(htmlspecialchars($args['nom']?? '', ENT_QUOTES));
            $typeParam = urldecode(htmlspecialchars($args['type']?? '', ENT_QUOTES));
            $nom = isset($nomParam) ? trim($nomParam) : null;
            $type = isset($typeParam) ? trim($typeParam) : null;

            // Check Parameter Page
            $this->ensureValidArguments(
                "Page number cannot be blank or string. It's must be numeric", $page
            );

            try 
            {
                // Establish the connection Database
                $repository = new SchoolRepository(Connexion::read());
                $counter = $repository->count();
                // Limit par page;
                $limitPerPage = $offsetParam;
                $ecolesCount  = intval($counter);
                $numOfPages   = intval(ceil($ecolesCount / $limitPerPage));

                
                // First Page
                if ($numOfPages == 0)  $numOfPages = 1;
                if ($numOfPages < $page || 0 == $page) {
                    return $this->response(false, 'Page not found', null, 404);
                }
                // Offset Page
                $offset = (($page == 1) ? 0 : ($limitPerPage * ($page - 1)));
                $schoolRows = $repository->retrieveBy(
                    limit: $limitPerPage, offset: $offset,
                    nom: $nom, type: $type
                );
                $rowCounted = $repository->getTemprowCounted();
               
                return $this->response(true, "Schools retrieved successfully", [
                    "rows_returned" => $rowCounted,
                    "total_rows" => $ecolesCount,
                    "total_pages" => $numOfPages,
                    "has_next_page" => ($page < $numOfPages) ? true : false,
                    "has_privious_page" => ($page > 1) ? true : false,
                    "schools" => $schoolRows
                ], 200);

            } catch (SchoolException | AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments(
                "School ID number cannot be blank or string. It's must be numeric",
                $school_id
            );

            try {
                // Establish the connection Database
                $repository = new SchoolRepository(Connexion::read());
                $schools = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();

                if ($rowCounted === 0) {
                    return $this->response(false, 'School Not Found', null, 404);
                }
                
                return $this->response(true, 'School retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "school" => current($schools)
                ], 200);

            } catch (SchoolException | AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments("School ID number cannot be blank or string. It's must be numeric", $school_id);

            // Retrive data from Json
            $jsonObject = $request->getParsedBody();
            try 
            {
                // Establish the connection Database
                $repository = new SchoolRepository(Connexion::write());
                $schoolRows = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();
                $schoolRow = current($schoolRows);

                if ($rowCounted === 0) {
                    return $this->response(false, 'School Not Found', null, 404);
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
                    return $this->response(false, 'School not updated', null, 409);
                }
                // Fetch after Update
                $schoolRows = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();
                $schoolRow = current($schoolRows);

                if ($rowCounted === 0) {
                    return $this->response(false, 'No school fetched after update', null, 404);
                }
                
                return $this->response(true, 'School updated successfully', [
                    "rows_updated" => $rowCounted,
                    "school" => current($schoolRows)
                ], 200);
            } catch (SchoolException | AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments("School ID number cannot be blank or string. It's must be numeric", $school_id);

            // Retrieve  Body
            $jsonObject = $request->getParsedBody();
            try {
                // Establish the connection Database
                $repository = new SchoolRepository(Connexion::write());
                $schoolRows = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();
                $schoolRow = current($schoolRows);

                if ($rowCounted === 0) {
                    return $this->response(false, 'School Not Found', null, 404);
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
                    return $this->response(false, 'School not updated', null, 409);
                }

                // Fetch after Update
                $schoolRows = $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();
                $schoolRow = current($schoolRows);

                if ($rowCounted === 0) {
                    return $this->response(false, 'No school fetched after update', null, 404);
                }

                return $this->response(true, 'School updated successfully', [
                    "rows_updated" => $rowCounted,
                    "school" => current($schoolRows)
                ], 200);

            } catch (SchoolException | AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
            $this->ensureValidArguments("School ID number cannot be blank or string. It's must be numeric", $school_id);

            try {
                // Establish the connection Database
                $repository = new SchoolRepository(Connexion::write());
                $repository->retrieve(id: $school_id);
                $rowCounted = $repository->getTemprowCounted();

                if ($rowCounted === 0) {
                    return $this->response(false, 'School Not Found', null, 404);
                }
                $repository->remove(id: $school_id);
                $rowCounted = $repository->rowCount();
                if ($repository->rowCount()=== 0) {
                    return $this->response(false, 'School not found for to delete', null, 404);
                }
                
                return $this->response(true, 'School deleted successfully', [
                    "rows_deleted" => $rowCounted
                ], 204);
            } catch (SchoolException | SchoolException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
                return $this->response(false, 'School name cannot be blank', null, 400);
            }
            try {
                // Establish the connection Database
                $repository = new SchoolRepository(Connexion::read());
                $schools = $repository->searchByName(nom: $nom);
                $rowCounted = $repository->getTemprowCounted();

                if ($rowCounted === 0) {
                    return $this->response(false, "School Not Found.", null, 404);
                }

                return $this->response(true, "Schools found", [
                    "rows_returned" => $rowCounted,
                    "schools" => $schools
                ], 200);
            } catch (SchoolException | AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
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
                return $this->response(false, 'School name cannot be blank', null, 400);
            }


            try {
                // Establish the connection Database
                $repository = new SchoolRepository( Connexion::read());
                $schools = $repository->searchByName(nom: $nom, limit: $limit);
                $rowCounted = $repository->getTemprowCounted();

                if ($rowCounted === 0) {
                    return $this->response(false, "School Not Found.", null, 404);
                }
                
                return $this->response(true, "Schools found", [
                    "rows_returned" => $rowCounted,
                    "schools" => $schools
                ], 200);
            } catch (SchoolException | AddressException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
    }
}
