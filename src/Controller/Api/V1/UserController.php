<?php 

/**
 * File UserController
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
    use \Psr\Http\Message\ResponseInterface as Response;
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use App\Database\Connexion;
    use App\Repository\UserRepository;
    use App\Exception\UserException;
    use App\Model\User;
    use App\Helper\UserRole;
    use App\Helper\UserStatus;



    class UserController extends \App\Controller\ApiController
    {
        /**
         * Method postUser [POST]
         *
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Response
         */
        public function postUserAction(
            Request $request,
            Response $response,
            array $args
        ) : Response {
            sleep(1); // Important
            // Establish the connection Database
            $repository = new UserRepository(Connexion::write());
            $jsonObject = $request->getParsedBody();

            // Start Transaction
            $repository->beginTransaction();
            try
            {
                $oUser = User::fromObject(data: $jsonObject);
                
                if ($repository->checkUserExist($oUser->getUsername(), $oUser->getEmail())) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                    return $this->response(false, 'User already exists', null, 400);
                }
                $hashedPassword = $oUser->hashPassword();
                $oUser->setPassword($hashedPassword);
                $oUser->setRole(UserRole::STANDARD);
                $oUser->setStatus(UserStatus::ACTIVE);
                $oUser->setAttempts(0);
                $oUser->setMetadata([]);
      
                $repository->add($oUser);
                if ($repository->inTransaction()) {
                    $repository->commit();
                }
                return $this->response(true, 'User created successfully', null, 201);
            } catch (UserException $ex) {
                if ($repository->inTransaction()) {
                    $repository->rollBack();
                }
                return $this->response(false, $ex->getMessage(), null, 500);
            }
        }
    }
}