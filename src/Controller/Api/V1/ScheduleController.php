<?php

/**
 * File ScheduleController
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
    use App\Model\ScheduleModel;
    use App\Exception\ScheduleException;
    use App\Model\Schedule;
    use App\Repository\ScheduleRepository;
    use App\Repository\SchoolRepository;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    class ScheduleController extends \App\Controller\ApiController
    {
        /**
         * Get All Schedule Action
         *
         * @return Response
         */
        public function getSchedulesAction(
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
                $connexionRead = Connexion::Read();
                $repository = new ScheduleRepository($connexionRead);
                $schedules = $repository->retrieve(schoolid: $school_id);
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Schedules Not Found."
                    ], 500);
                }
    
                $returnData['rows_returned'] = $rowCounted;
                $returnData['horaires'] = $schedules;

                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 200);
            } catch (ScheduleException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }

         /**
         * Method postScheduleAction [POST]
         * 
         * Il permet d'ajouter horaire écoles
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function postSchedulesAction(
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
            } catch (ScheduleException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }

            // Récupération des données JSON
            $jsonObject = $request->getParsedBody();

            // Insertion dans la base de données
            try {
                $dt = new \DateTime('00:00:00');
                $schedule  = new Schedule(
                    id: null,
                    jour: $jsonObject['jour'] ?? null,
                    debut: $jsonObject['debut'] ?? $dt->format('H:i:s'),
                    fin: $jsonObject['fin'] ?? $dt->format('H:i:s'),
                    ecoleid: $school_id
                );
                $connexionWrite = Connexion::Write();
                $repository = new ScheduleRepository($connexionWrite);
                $repository->add($schedule);

                return $this->jsonResponse([
                    "success" => true,
                    "message" => "Schedule added successfully."
                ], 201);

            } catch (ScheduleException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }

         /**
         * Get one Schedule Action
         *
         * @return Response
         */
        public function getOneScheduleAction(
            Request $request,
            Response $response,
            array $args
        ): Response {

            $school_id = (int) $args['id'];
            $schedule_id = (int) $args['horaireid'];

            // Check Parameter Schedule Id
            if (!$this->checkArguments($schedule_id, $schedule_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Schedule ID or Schedule ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

            // Récupération des données
            try {
                $connexionRead = Connexion::Read();
                $repository = new ScheduleRepository($connexionRead);
                $schedule = $repository->retrieve(
                    id: $schedule_id, 
                    schoolid: $school_id
                );
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Schedule Not Found."
                    ], 500);
                }

                return $this->jsonResponse([
                    "success" => true,
                    "data" => $schedule
                ], 200);
            } catch (ScheduleException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }
            
        /**
         *  Method deleteScheduleAction [DELETE]
         * 
         * Il permet de supprimer un horaire d'école
         * 
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Reponse
         */
        public function deleteScheduleAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {

            $school_id = (int) $args['id'];
            $schedule_id = (int) $args['horaireid'];

            // Check Parameter Schedule Id
            if (!$this->checkArguments($schedule_id, $school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Schedule ID or School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

            // Suppression dans la base de données
            try {
                $connexionWrite = Connexion::Write();
                $repository = new ScheduleRepository($connexionWrite);
                $repository->remove(id: $schedule_id, schoolid: $school_id);
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Schedule Not Found."
                    ], 500);
                }

                return $this->jsonResponse([
                    "success" => true,
                    "message" => "Schedule deleted successfully."
                ], 200);

            } catch (ScheduleException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }

        /**
         *  Method putScheduleAction [PUT]
         * 
         *  Il permet de mettre à jour un horaire d'école
         *
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Response
         */
        public function putScheduleAction(
            Request $request, 
            Response $response, 
            array $args
        ): Response {

            $school_id = (int) $args['id'];
            $schedule_id = (int) $args['horaireid'];

            // Check Parameter Schedule Id
            if (!$this->checkArguments($schedule_id, $school_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Schedule ID or School ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

            // Retrieve  Body
            $jsonObject = $request->getParsedBody();
            // Mise à jour dans la base de données
            try {
                $connexionWrite = Connexion::Write();
                $repository = new ScheduleRepository($connexionWrite);
                $scheduleRows =$repository->retrieve(id: $schedule_id, schoolid: $school_id);
                $rowCounted = $repository->rowCount();

                if ($rowCounted == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => "Schedule Not Found."
                    ], 500);
                }
                $scheduleRow = current($scheduleRows);
                $schedule = Schedule::fromState($scheduleRow);
                $schedule->setJour(jour: $jsonObject->jour ?? $schedule->getJour());
                $schedule->setDebut(debut: $jsonObject->debut ?? $schedule->getDebut());
                $schedule->setFin(fin: $jsonObject->fin ?? $schedule->getFin());

                return $this->jsonResponse([
                    "success" => true,
                    "message" => "Schedule updated successfully."
                ], 200);

            } catch (ScheduleException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    'message' => $ex->getMessage(),
                ], 400);
            }
        }
    }
}