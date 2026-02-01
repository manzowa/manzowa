<?php 

namespace App\Controller\Api\V1;

use App\Controller\ApiController;
use App\Model\Rating;
use App\Repository\RatingRepository;
use App\Database\Connexion;
use App\Exception\RatingException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RatingController extends ApiController
{
    /**
     * Method getRatingsAction [GET]
     * 
     * Il permet de recupère les évaluations
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return mixed
     */
    public function getRatingsAction(Request $request, Response $response, array $args): Response
    {
        try 
        {   
            $repository = new RatingRepository(Connexion::read());
            $ratings = $repository->retrieve();
            $rowCounted = $repository->rowCount();

            if ($rowCounted == 0) {
                return $this->response(false, 'No ratings found', null, 400);
            }

            return $this->response(true, 'List of ratings retrieved successfully', [
                "rows_returned" => $rowCounted,
                "ratings" => $ratings,
            ], 200);

        } catch (RatingException $ex) {
            return $this->response(false, $ex->getMessage(), null, 500);
        } 
    }

    /**
     * Method postRatingAction [POST]
     * 
     * Il permet de créer une évaluation
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * 
     * @return mixed
     */
    public function postRatingAction(Request $request, Response $response, array $args): Response
    {
        // Establish the connection Database
        $repository = new RatingRepository(Connexion::write());
        try 
        {   
            $jsonObject = $request->getParsedBody();
           
            $rating = Rating::fromObject(data: $jsonObject);

            $repository->beginTransaction();
            $repository->add($rating);
            $rowCounted = $repository->rowCount();

            if ($rowCounted == 0) {
                if ($repository->inTransaction()) {
                    $repository->rollBack();
                }
                return $this->response(false, 'Rating creation failed', null, 400);
            }
            $newRatingId = (int) $repository->lastInsertId();
            $repository->commit();
        
            return $this->response(true, 'Rating created successfully', [
                "rows_inserted" => $rowCounted,
                "rating_id" => $newRatingId,
            ], 201);

        } catch (RatingException $ex) {
            if ($repository->inTransaction()) {
                $repository->rollBack();
            }
            return $this->response(false, $ex->getMessage(), null, 500);
        } 
    }

    /**
     * Method getRatingByIdAction [GET]
     * 
     * Il permet de recupère une évaluation par son identifiant
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * 
     * @return mixed
     */
    public function getRatingByIdAction(Request $request, Response $response, array $args): Response
    {
        $rating_id = (int) $args['id'];
        // Check Parameter Rating Id   
        $this->ensureValidArguments("Invalid Rating ID", $rating_id);
        $repository = new RatingRepository(Connexion::read());

        try {
            $ratingRows = $repository->retrieve(id: $rating_id);
            $ratingRow = current($ratingRows);
            $rating = Rating::fromState($ratingRow);
            $rowCounted = $repository->rowCount();
            
            if ($rowCounted == 0) {
                return $this->response(false, 'Rating not found', null, 404);
            }
            return $this->response(true, 'Rating retrieved successfully', [
                "rows_returned" => $rowCounted,
                "rating" => $rating->toArray(),
            ], 200);

        } catch (RatingException $ex) {
            return $this->response(false, $ex->getMessage(), null, 500);
        }
    }

    /**
     * Method putRatingByIdAction [PUT]
     * 
     * Il permet de modifier une évaluation par son identifiant
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * 
     * @return mixed
     */
    public function putRatingByIdAction(Request $request, Response $response, array $args): Response
    {
        $rating_id = (int) $args['id']; 
        $jsonObject = $request->getParsedBody();
        // Check Parameter Rating Id   
        $this->ensureValidArguments("Invalid Rating ID", $rating_id);
        // Establish the connection Database
        $repository = new RatingRepository(Connexion::write());
        try 
        {
            $RatingRows = $repository->retrieve(id: $rating_id);
            $RatingRow = current($RatingRows);
            $rowCounted = $repository->rowCount();
            if ($rowCounted == 0) {;
                return $this->response(false, 'Rating not found', null, 404);
            }
            $oRating = Rating::fromObject(data: $jsonObject);
            $rating = Rating::fromState($RatingRow);

            //Update fields
            !empty($oRating->getScore()) && $rating->setScore($oRating->getScore());
            !empty($oRating->getCreatedAt()) && $rating->setCreatedAt($oRating->getCreatedAt());

            $repository->beginTransaction();
        
            $repository->update(rating: $rating);
            $rowCounted = $repository->rowCount();

            if ($rowCounted == 0) {
                $repository->rollBack();
                return $this->response(false, 'Rating update failed', null, 400);
            }
            $repository->commit();
            return $this->response(true, 'Rating updated successfully', [
                "rows_updated" => $rowCounted,
            ], 200);

        } catch (RatingException $ex) {
            $repository->rollBack();
            return $this->response(false, $ex->getMessage(), null, 500);
        }
    }
}