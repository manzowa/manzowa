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
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Events not found'
                    ], 400);
                }

                return $this->jsonResponse([
                    "success" => true,
                    "data" => [
                        "rows_returned" => $rowCounted,
                        "events" => $events,
                    ]
                ], 200);
            } catch (EventException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
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
            if (!$this->checkArguments($school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }
            try 
            {
                $connexionRead = Connexion::read();
                $repository = new EventRepository($connexionRead);
                $eventsRows = $repository->retrieve(schoolid: $school_id);

                $rowCounted = $repository->getTempRowCounted();
                if ($rowCounted == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Events not found'
                    ], 400);
                }

                $returnData['rows_returned'] = $rowCounted;
                $returnData['events'] = $eventsRows;
                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);
            } catch (EventException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
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
            if (!$this->checkArguments($school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

            // Check if school exists
            try {
                $connexionRead = Connexion::Read();
                $schoolRepository = new SchoolRepository($connexionRead);
                $schoolRepository->retrieve(id: $school_id);
                $rowCounted = $schoolRepository->getTempRowCounted();
                if ($rowCounted == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "School Not Found."
                    ], 500);
                }
            } catch (SchoolException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }

            // Retrieve  Body
            $jsonObject = $request->getParsedBody();

            try 
            {
                // Establish the connection Database
                $connexionWrite = Connexion::Write();
                $repository = new EventRepository($connexionWrite);

                // Create new Event
                $event = new Event(
                    id: NULL,
                    titre: $jsonObject->titre?? null,
                    description: $jsonObject->description?? null,
                    date: $jsonObject->date?? null,
                    lieu: $jsonObject->lieu?? null,
                    ecoleid: $school_id,
                );

                // Add new Event
                $repository->add(event: $event);
                $rowCounted = $repository->rowCount();
                
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Failed to create event'
                    ], 500);
                    
                }
                $event_id = (int) $repository->lastInsertId();
                $events = $repository->retrieve($event_id);
                $rowCounted = $repository->rowCount();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Failed to retrieve event after creation'
                    ], 404);
                }

                $returnData = [];
                $returnData['rows_inserted'] =  $rowCounted;
                $returnData['event'] = current($events);

                return $this->jsonResponse([
                    "success" => true,
                    "data" =>  $returnData
                ], 201); 

            } catch (EventException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
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

            // Check Parameter School Id
            if ($this->checkArguments($event_id) === false) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Event ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

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

                return $this->jsonResponse([
                    "success" => true,
                    "data" =>[
                        "rows_returned" => $rowCounted,
                        "event" => $eventRow,
                    ]
                ], 200);

            } catch (EventException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
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
            if (!$this->checkArguments($event_id, $school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Event ID or School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }
            // Retrive data from Json
            $jsonObject = $request->getParsedBody();
            try 
            {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new EventRepository($connexionWrite);
                $eventRows = $repository->retrieve(id: $event_id, schoolid: $school_id);
                $rowCounted = $repository->rowCount();
        
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Event Not Found.'
                    ], 500);
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
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Event not updated.'
                    ], 400);
                }
                // Fetch after Update
                $eventRows = $repository->retrieve(id: $event_id);
                $rowCounted = $repository->rowCount();
              
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'No event fetched after update.'
                    ], 404);
                }
                $eventRow = current($eventRows);
                $returnData = [];
                $returnData['rows_counted'] =  $rowCounted;
                $returnData['event'] = $eventRow;

                return $this->jsonResponse([
                    "success" => true,
                    "data"=> $returnData
                ], 200);
            } catch (EventException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
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
            if (!$this->checkArguments($event_id, $school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Event ID or School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }
            // Retrieve  Body
            $jsonObject = $request->getParsedBody();
            try {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new EventRepository($connexionWrite);
                $eventRows = $repository->retrieve(id: $event_id, schoolid: $school_id);
                $rowCounted = $repository->rowCount();
                
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Event Not Found.'
                    ], 500);
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
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Event not updated.'
                    ], 400);
                }

                // Fetch after Update
                $eventRows = $repository->retrieve(id: $event_id);
                $rowCounted = $repository->rowCount();
                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'No event fetched after update.'
                    ], 404);
                }
                $eventRow = current($eventRows);

                $returnData = [];
                $returnData['rows_counted'] = $rowCounted;
                $returnData['event'] =  $eventRow;
                return $this->jsonResponse([
                    "success" => true,
                    "data"=> $returnData
                ], 200);


            } catch (EventException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
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
            if (!$this->checkArguments($event_id, $school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Event ID or School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

            try {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $repository = new EventRepository($connexionWrite);
                $eventRows = $repository->retrieve(id: $event_id, schoolid: $school_id);
                $rowCounted = $repository->rowCount();

                if ($rowCounted === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Event Not Found.'
                    ], 500);

                }
                $repository->remove(id: $event_id);
                $rowCounted = $repository->rowCount();
                if ($repository->rowCount()=== 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Event not found for to delete.'
                    ], 404);

                }
                $returnData = [];
                $returnData['rows_deleted'] = $rowCounted;

                return $this->jsonResponse([
                    "success" => true,
                    "message" => "Event $event_id deleted",
                    "data" => $returnData,
                ], 204);
            } catch (EventException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
        }
    }
}
