<?php

/**
 * File IndexController
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
    use App\Repository\SchoolRepository;
    use App\Exception\EventException;
    use App\Exception\SchoolException;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use App\Model\Event;

    class IndexController extends \App\Controller\ApiController
    {
        /**
         * Method getAllEventsAction [GET]
         * 
         * Il permet de recupère les événements
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getAllEventsAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $limitParam = (int) ($args['limit']?? 20);
            
            try 
            {
                $connexionRead = Connexion::read();
                $repository = new EventRepository($connexionRead);
                $events = $repository->retrieveAll(limit: $limitParam);
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->response(false, 'Events not found', null, 400);
                }
                return $this->response(true, 'Events retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "evenements" => $events,
                ], 200);
            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }

        /**
         * Method getAllEventByIdAction [GET]
         * 
         * Il permet de recupère les événements
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getAllEventByIdAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $event_id = (int) $args['id'] ?? null;
            $limitParam = (int) ($args['limit']?? 20);
            
            try 
            {
                $repository = new EventRepository(Connexion::read());
                $events = $repository->retrieveAll(
                    id: $event_id,
                    limit: $limitParam
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->response(false, 'Event not found', null, 400);
                }

                return $this->response(true, 'Events retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "evenements" => $events,
                ], 200);
            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method getAllEventTwoAction [GET]
         * 
         * Il permet de recupère les événements
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getAllEventTwoAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $event_id = (int) $args['id'] ?? null;
            $school_id = (int) $args['ecole_id'] ?? null;
            $limitParam = (int) ($args['limit']?? 20);
            $repository = new EventRepository(Connexion::read());
            
            try 
            {
                $events = $repository->retrieveAll(
                    id: $event_id,
                    ecoleid: $school_id,
                    limit: $limitParam
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->response(false, 'Events not found', null, 400);
                }

                return $this->response(true, 'Events retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "evenements" => $events,
                ], 200);
            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method getAllEventThreeAction [GET]
         * 
         * Il permet de recupère les événements
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getAllEventThreeAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $event_id = (int) $args['id'] ?? null;
            $school_id = (int) $args['ecole_id'] ?? null;
            $strNom = (string) $args['nom'] ?? null;
            $limitParam = (int) ($args['limit']?? 20);

            $repository = new EventRepository(Connexion::read());
            
            try 
            {
               
                $events = $repository->retrieveAll(
                    id: $event_id,
                    ecoleid: $school_id,
                    nomEcole: $strNom,
                    limit: $limitParam
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->response(false, 'Events not found', null, 400);
                }

                return $this->response(true, 'Events retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "evenements" => $events,
                ], 200);
                
            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method getAllEventFourAction [GET]
         * 
         * Il permet de recupère les événements
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getAllEventFourAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $event_id = (int) $args['id'] ?? null;
            $school_id = (int) $args['ecole_id'] ?? null;
            $strNom = (string) $args['nom'] ?? null;
            $strTitre = (string) $args['titre'] ?? null;
            $limitParam = (int) ($args['limit']?? 20);
            
            try 
            {
                $repository = new EventRepository(Connexion::read());
                $events = $repository->retrieveAll(
                    id: $event_id,
                    ecoleid: $school_id,
                    nomEcole: $strNom,
                    titre: $strTitre,
                    limit: $limitParam
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->response(false, 'Events not found', null, 400);
                }
                return $this->response(true, 'Events retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "evenements" => $events,
                ], 200);
            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method getAllEventFiveAction [GET]
         * 
         * Il permet de recupère les événements
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getAllEventFiveAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $event_id = (int) $args['id'] ?? null;
            $school_id = (int) $args['ecole_id'] ?? null;
            $strNom = (string) $args['nom'] ?? null;
            $strTitre = (string) $args['titre'] ?? null;
            $strLieu  = (string) $args['lieu'] ?? null;
            $limitParam = (int) ($args['limit']?? 20);
            
            try 
            {
                $repository = new EventRepository(Connexion::read());
                $events = $repository->retrieveAll(
                    id: $event_id,
                    ecoleid: $school_id,
                    nomEcole: $strNom,
                    titre: $strTitre,
                    lieu: $strLieu,
                    limit: $limitParam
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->response(false, 'Events not found', null, 400);
                }

                return $this->response(true, "Events retrieved successfully", [
                    "rows_returned" => $rowCounted,
                    "evenements" => $events,
                ], 200);
            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method getAllEventFilterByDateAction [GET]
         * 
         * Il permet de recupère les événements
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getAllEventFilterByDatetimeAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $datetimeString = urldecode($args['datetime']);
            $limitParam = (int) ($args['limit']?? 20);

            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetimeString)) {
                return $this->response(false, 'Invalid format', null, 400);
            }

            try 
            {
                $repository = new EventRepository(Connexion::read());
                $events = $repository->retrieveAll(
                    dateTime: $datetimeString,
                    limit: $limitParam
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->response(false, 'Events not found', null, 400);
                }

                return $this->response(true, 'Events retrieved successfully',[
                    "rows_returned" => $rowCounted,
                    "evenements" => $events,
                ], 200);

            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }

        /**
         * Method getAllEventFilterByDatetimeAndTownAction [GET]
         * 
         * Il permet de recupère les événements
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getAllEventFilterByDatetimeAndTownAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $strDatetime = urldecode($args['datetime']) ?? null;
            $strVille = urldecode($args['ville']) ?? null;
            $limitParam = (int) ($args['limit']?? 20);

            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $strDatetime)) {
                return $this->response(false, 'Invalid format', null, 400);
            }
            if (in_array($strVille, ["all", "*", "any"])) {
                $strVille = null;
            }
            try 
            {
                $repository = new EventRepository(Connexion::read());
                $events = $repository->retrieveAll(
                    dateTime: $strDatetime,
                    ville: $strVille,
                    limit: $limitParam
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->response(false, 'Events not found', null, 400);
                }

                return $this->response(true, 'Events retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "evenements" => $events,
                ], 200);
            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }

        /**
         * Method getEventsAction [GET]
         * 
         * Il permet de recupère les événements
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return mixed
         */
        public function getEventsAction(
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


            try 
            {
                $repository = new EventRepository(Connexion::read());
                $eventsRows = $repository->retrieve(schoolid: $school_id);

                $rowCounted = $repository->getTempRowCounted();
                if ($rowCounted == 0) {
                    $this->response(false, 'Events not found', null, 400);
                }

                return $this->response(true, "Events retrieved successfully", [
                    "rows_returned" => $rowCounted,
                    "evenements" => $eventsRows,
                ], 200);

            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method postEventsAction [POST]
         * 
         * Il permet d'ajouter un événement
         *
         * @param Request $request
         * @param Response $response
         * @param array $args
         * 
         * @return mixed
         */
        public function postEventsAction(
            Request $request, 
            Response $response, 
            array $args
        ) {
            $school_id = (int) $args['id'];
            // Check Parameter School Id
            $this->ensureValidArguments(
                "School ID number cannot be blank or string. It's must be numeric",
                $school_id
            );

            // Check if school exists
            try {
                $schoolRepository = new SchoolRepository(Connexion::read());
                $schoolRepository->retrieve(id: $school_id);
                $rowCounted = $schoolRepository->getTempRowCounted();
                if ($rowCounted == 0) {
                    return $this->response(false, 'School Not Found.', null, 400);
                }
            } catch (SchoolException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }

            // Retrieve  Body
            $jsonObject = $request->getParsedBody();

            try 
            {
                // Establish the connection Database
                $repository = new EventRepository(Connexion::write());

                // Create new Event
                $event = Event::fromObject(data: $jsonObject)
                    ->setEcoleid($school_id);

                // Add new Event
                $repository->add(event: $event);
                $rowCounted = $repository->rowCount();
                
                if ($rowCounted === 0) {
                    return $this->response(false, 'Failed to create event', null, 400);
                }
                $event_id = (int) $repository->lastInsertId();

                $events = $repository->retrieve(id: $event_id);
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->response(false, 'Failed to retrieve event after creation', null, 404);
                }
                return $this->response(true, 'Event created successfully', [
                    "rows_inserted" => $rowCounted,
                    "evenement" => $event,
                ], 201);

            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 400);
            }
        }
        /**
         * Method getEventAction [GET]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return void
         */
        public function getEventAction(
            Request $request, 
            Response $response, 
            array $args 
        ): Response { 
            $school_id = (int) $args['id'] ?? null;
            $event_id = (int) $args['evenementid'] ?? null;

            $this->ensureValidArguments(
                "Event ID number cannot be blank or string. It's must be numeric",
                $event_id
            );

            try {
                // Establish the connection Database
                $connexionRead = Connexion::read();
                $repository = new EventRepository($connexionRead);
                $eventRows = $repository->retrieve(id: $event_id, schoolid: $school_id);
                $rowCounted = $repository->getTempRowCounted();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Event Not Found."
                    ], 500);
                }
                $eventRow = current($eventRows);
                return $this->response(true, 'Event retrieved successfully', [
                    "rows_returned" => $rowCounted,
                    "evenement" => $eventRow,
                ], 200);

            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method putEventAction [PUT]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function putEventAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {           
            $school_id = (int) $args['id'];
            $event_id = (int) $args['evenementid'];

            // Check Parameter School Id
            $this->ensureValidArguments(
                "Event ID or School ID number cannot be blank or string. It's must be numeric", 
                $event_id, $school_id
            );
            //
            // Retrive data from Json
            $jsonObject = $request->getParsedBody();
            try 
            {
                // Establish the connection Database
                $repository = new EventRepository(Connexion::write());
                $eventRows = $repository->retrieve(id: $event_id, schoolid: $school_id);
                $rowCounted = $repository->rowCount();
        
                if ($rowCounted === 0) {
                    return $this->response(false, 'Event Not Found.', null, 400);
                }
                $eventRow = current($eventRows);
                $event = Event::fromState(data: $eventRow);
                $event
                    ->setTitre(titre: $jsonObject->titre ?? null)
                    ->setDescription(description: $jsonObject->description ?? null)
                    ->setDate(date: $jsonObject->date ?? null)
                    ->setLieu(lieu: $jsonObject->lieu ?? null);

                $repository->update(event: $event);
                if ($repository->rowCount() === 0) {
                    return $this->response(false, 'Event not updated.', null, 400);
                }
                // Fetch after Update
                $eventRows = $repository->retrieve(id: $event_id);
                $rowCounted = $repository->rowCount();
              
                if ($rowCounted === 0) {
                    return $this->response(false, 'No event fetched after update.', null, 404);
                }
                $eventRow = current($eventRows);

                return $this->response(true, 'Event updated successfully', [
                    "rows_updated" => $rowCounted,
                    "evenement" => $eventRow,
                ], 200);

            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method patchEventAction [PATCH]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function patchEventAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'];
            $event_id = (int) $args['evenementid'];
            // Check Parameter Event Id
            $this->ensureValidArguments(
                "Event ID or School ID number cannot be blank or string. It's must be numeric", 
                $event_id, $school_id
            );

            // Retrieve  Body
            $jsonObject = $request->getParsedBody();
            try {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new EventRepository($connexionWrite);
                $eventRows = $repository->retrieve(id: $event_id, schoolid: $school_id);
                $rowCounted = $repository->rowCount();
                
                if ($rowCounted === 0) {
                    return $this->response(false, 'Event Not Found.', null, 400);
                }
                $eventRow = current($eventRows);
                $refClass = new \ReflectionClass(Event::class);
                $props = $refClass->getProperties();
                $field = null;
                $propExisted = false;
                foreach ($props as $prop) {
                    if (property_exists($jsonObject, $prop->getName())) {
                        $propExisted = true;
                        $field = $prop->getName();
                    } 
                }
                $event = Event::fromState(data: $eventRow);
                if ($propExisted) {
                    $method ="set".ucfirst($field);
                    $event->$method($jsonObject->$field);
                }
                $repository->update(event: $event);
                if ($repository->rowCount() === 0) {
                    return $this->response(false, 'Event not updated.', null, 400);
                }

                // Fetch after Update
                $eventRows = $repository->retrieve(id: $event_id);
                $rowCounted = $repository->rowCount();
                if ($rowCounted === 0) {
                    return $this->response(false, 'No event fetched after update.', null, 404);
                }
                $eventRow = current($eventRows);

                return $this->response(true, 'Event updated successfully', [
                    "rows_updated" => $rowCounted,
                    "evenement" => $eventRow,
                ], 20);
            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
        /**
         * Method deleteEventAction  [DELETE]
         *
         * @param Request $request
         * @param Response $response
         * @param Array $args
         * 
         * @return mixed
         */
        public function deleteEventAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {
            $school_id = (int) $args['id'];
            $event_id = (int) $args['evenementid'];
            // Check Parameter Event ID or School Id
            $this->ensureValidArguments(
                "Event ID or School ID number cannot be blank or string. It's must be numeric", 
                $event_id, $school_id
            );

            try {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new EventRepository($connexionWrite);
                $repository->retrieve(id: $event_id, schoolid: $school_id);
                $rowCounted = $repository->rowCount();

                if ($rowCounted === 0) {
                    return $this->response(false, 'Event Not Found.', null, 500);
                }
                $repository->remove(id: $event_id);
                $rowCounted = $repository->rowCount();
                if ($repository->rowCount()=== 0) {
                    return $this->response(false, 'Event not deleted.', null, 400);
                }
                return $this->response(true, "Event $event_id deleted", [
                    "rows_deleted" => $rowCounted,
                ], 204);
            } catch (EventException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
    }
}
