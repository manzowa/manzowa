<?php

namespace App\Controller\Api\V1;

use App\Controller\ApiController;
use App\Model\Comment;
use App\Repository\CommentRepository;
use App\Database\Connexion;
use App\Exception\CommentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CommentController extends ApiController
{
    /**
     * Method getCommentsAction [GET]
     * 
     * Il permet de recupère les commentaires
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return mixed
     */
    public function getCommentsAction(Request $request, Response $response, array $args): Response
    {
        $repository = new CommentRepository(Connexion::read());
        try 
        {   
            $comments = $repository->retrieve();
            $rowCounted = $repository->rowCount();

            if ($rowCounted == 0) {
                return $this->response(false, 'No comments found', null, 400);
            }
            return $this->response(true, 'List of comments retrieved successfully', [
                "rows_returned" => $rowCounted,
                "comments" => $comments
                ], 200
            );
        } catch (CommentException $ex) {
            return $this->response(false, $ex->getMessage(), null, 500);
        } 
    }

    /**
     * Method postCommentsAction [POST]
     * 
     * Il permet d'ajouter un commentaire
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return mixed
     */
    public function postCommentsAction(Request $request, Response $response, array $args): Response
    {
        $jsonObject = $request->getParsedBody();
        $repository = new CommentRepository(Connexion::write());
        try 
        {   
            $comment = Comment::fromObject(data: $jsonObject);
            $repository->beginTransaction();
            $repository->add($comment);
            $rowCounted = $repository->rowCount();

            if ($rowCounted == 0) {
                $repository->rollBack();
                return $this->response(false, 'Comment creation failed', null, 400);
            }
            $newCommentId = (int) $repository->lastInsertId();
            $repository->commit();
            return $this->response(true, 'Comment added successfully', [
                "comment_id" => $newCommentId
            ], 201);
        } catch (CommentException $ex) {
            $repository->rollBack();
            return $this->response(false, $ex->getMessage(), null, 500);
        }
    }

    /**
     * Method getCommentByIdAction [GET]
     * 
     * Il permet de recupère un commentaire par son ID
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * 
     * @return mixed
     */
    public function getCommentByIdAction(Request $request, Response $response, array $args): Response   
    {
        $commentId = (int) $args['id'];
        // Check Parameter Comment Id   
        $this->ensureValidArguments("Invalid Comment ID", $commentId);
        $repository = new CommentRepository(Connexion::read());

        try 
        {   
            $commentRows = $repository->retrieve(id:$commentId);
            $commentRow = current($commentRows);
            $comment = Comment::fromState($commentRow);
            $rowCounted = $repository->rowCount();

            if ($rowCounted == 0) {
                return $this->response(false, 'Comment not found', null, 404);
            }
            return $this->response(true, 'Comment retrieved successfully', [
                    'rows_returned' => $rowCounted, 
                    'comment' => $comment->toArray()
                ], 200
            );
        } catch (CommentException $ex) {
            return $this->response(false, $ex->getMessage(), null, 500);
        } 
    } 
    
    /**
     * Method PutCommentByIdAction [PUT]
     * 
     * Il permet de modifier un commentaire par son ID
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * 
     * @return mixed
     */
    public function putCommentByIdAction(Request $request, Response $response, array $args): Response
    {   
        $commentId = (int) $args['id'];
        $jsonObject = $request->getParsedBody();
        // Check Parameter Comment Id   
        $this->ensureValidArguments("Invalid Comment ID", $commentId);

        try 
        {   
            // Establish the connection Database
            $repository = new CommentRepository(Connexion::write());

            $commentRows = $repository->retrieve(id:$commentId);
            $commentRow = current($commentRows);
            $comment = Comment::fromState($commentRow);
            $rowCounted = $repository->rowCount();

            if ($rowCounted == 0) {
                return $this->response(false, 'Comment not found', null, 404);
            }

            // Update the comment content
            $oComment = Comment::fromObject(data: $jsonObject);
            !empty($oComment->getContent()) && $comment->setContent($oComment->getContent());

            $repository->update($comment);
            $rowCounted = $repository->rowCount();

            if ($rowCounted == 0) {
                return $this->response(false, 'Comment update failed', null, 400);
            }
            return $this->response(true, 'Comment updated successfully', null, 200);

        } catch (CommentException $ex) {
            return $this->response(false, $ex->getMessage(), null, 500);
        } 
    }
}
