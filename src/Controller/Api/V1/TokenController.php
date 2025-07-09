<?php 

/**
 * File TokenController
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
    use App\Exception\TokenException;
    use App\Model\User;
    use App\Model\Token;

    class TokenController extends \App\Controller\ApiController
    {
        /**
         * Method postTokenAction [POST]
         *
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return Response
         */
        public function postTokenAction(
            Request $request,
            Response $response,
            array $args
        ) : Response {
            sleep(1); // Important
            // Establish the connection Database
            $connexionWrite = Connexion::write();
            $repository = new UserRepository($connexionWrite);
            $jsonObject = $request->getParsedBody();

            try 
            {
                // Variables
                $username = $jsonObject->username;
                $password = $jsonObject->password;
                $user = $repository->findByUsernameOrEmail($username);

                if ($repository->rowCount() === 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Username or Password is incorrect'
                    ], 401);
                }

                if (!$user->isPassword($password)) {
                    $user->incrementAttempts(); // Update login attempts
                    $repository->update($user);
                    if ($repository->rowCount() === 0) {
                        return $this->jsonResponse([
                            "success" => false,
                            "message" => 'Username or Password is incorrect'
                        ], 401);
                    }
                }
                $accessToken  = \App\generateToken();
                $refreshToken = \App\generateToken();

                $access_token_expiry_seconds = 1200;
                $refresh_token_expiry_seconds = 1209600;

                // Start Transaction
                $repository->beginTransaction();
                $user->resetAttempts();
                $repository->update($user);

                $token = new Token(
                    id: null,
                    userid: $user->getId(),
                    accessToken: $accessToken,
                    accessTokenExpiry: $access_token_expiry_seconds,
                    refreshToken: $refreshToken,
                    refreshTokenExpiry: $refresh_token_expiry_seconds
                );
                $repository->addToken($token);
                $lastSessionId = $repository->getStockId();
                $repository->commit();

                $returnData = [];
                $returnData['session_id'] = intval($lastSessionId);
                $returnData['access_token'] = $accessToken;
                $returnData['access_token_expires_in'] = $access_token_expiry_seconds;
                $returnData['refresh_token'] = $refreshToken;
                $returnData['refresh_token_expires_in'] = $refresh_token_expiry_seconds;

                
                return $this->jsonResponse([
                    "success" => true,
                    "data" => $returnData
                ], 201);

            } catch (UserException $ex) {
                if ($repository->inTransaction()) {
                    $repository->rollBack();
                }
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }
            return $response;
        }
        /**
         * Method deleteTokenAction [DELETE]
         *
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return  Response
         */
        public function deleteTokenAction(
            Request $request,
            Response $response,
            array $args
        ):  Response {
            
            $session_id = (int) $args['id'];
            // Check Parameter ID
            if (!$this->checkArguments($session_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Token ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }
            try {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $accessToken = \App\beareToken( 
                    $request->getHeaderLine('HTTP_AUTHORIZATION')
                );
                $repository = new UserRepository($connexionWrite);
                $repository->removeToken($session_id, $accessToken);

                if ($repository->getTempRowCounted() === 0) {
                    $msg = "Failed to log out of this sessions using ";
                    $msg.= "access token provided";
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => $msg
                    ], 400);
                }
                $returnData = [];
                $returnData['session_id'] = intval($session_id);

                return $this->jsonResponse([
                    "success" => true,
                    "message" => "Logged out ",
                    "data" =>  $returnData
                ], 204);

            } catch (UserException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }

            return $response;
        }
        /**
         * Method patchTokenAction [PATCH]
         *
         * @param Request $request
         * @param Response $response
         * @param array $args
         *
         * @return  Response
         */
        public function patchTokenAction(
            Request $request,
            Response $response,
            array $args
        ):  Response {

            $session_id = (int) $args['id'];
            // Check Parameter ID
            if (!$this->checkArguments($session_id)) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => "Token ID number cannot be blank or string. It's must be numeric"
                ], 400);
            }

            try 
            {
                // Establish the connection Database
                $connexionWrite = Connexion::write();
                $jsonObject = $request->getParsedBody();
                $accessToken = \App\beareToken( 
                    $request->getHeaderLine('HTTP_AUTHORIZATION')
                );
                $refreshToken = $jsonObject->refreshToken;

                $repository = new UserRepository($connexionWrite);
                $tokens = $repository->retrieveToken(
                    id: $session_id, 
                    accessToken:$accessToken, 
                    refreshToken: $refreshToken
                );
              
                if ($repository->getTempRowCounted() == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Access token or refresh token is invalid'
                    ], 401);
                }
                $token = current($tokens);
               
                $users = $repository->retrieve(id: $token->getUserId());
                $user = current($users);

                // Check if user is active
                if (!$user->isActive()) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'User account is not active'
                    ], 401);
                }
                // Check if user has exceeded maximum login attempts
                if ($user->isLocked()) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'User account is currently locked out'
                    ], 401);
                }
                 // Check refresh token expiration
                if ($token->isRefreshtokenexpiry()) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Refresh token has expired - please log in again'
                    ], 401);
                }
                $accessToken=\App\generateToken();
                $refreshToken=\App\generateToken();
                $accessTokenExpirySeconds= 1200;
                $refreshTokenExpirySeconds= 1209600;
                // update Token
                $token
                    ->setAccessToken(accessToken: $accessToken)
                    ->setAccessTokenExpiry(accessTokenExpiry: $accessTokenExpirySeconds)
                    ->setRefreshToken(refreshToken: $refreshToken)
                    ->setRefreshTokenExpiry(refreshTokenExpiry: $refreshTokenExpirySeconds);

                $repository->updateToken(token: $token);
            
                if ($repository->getTempRowCounted() == 0) {
                    return $this->jsonResponse([
                        "success" => false,
                        "message" => 'Access token could not be refresh - please log in again'
                    ], 401);
                }
                $returnData = [];
                $returnData['session_id'] = $session_id;
                $returnData['access_token'] = $accessToken;
                $returnData['access_token_expiry'] = $accessTokenExpirySeconds;
                $returnData['refresh_token'] = $refreshToken;
                $returnData['refresh_token_expiry'] = $refreshTokenExpirySeconds;

                return $this->jsonResponse([
                    "success" => true,
                    "message" => 'Access token refreshed',
                    'data' => $returnData
                ], 200);

            } catch (UserException $ex) {
                return $this->jsonResponse([
                    "success" => false,
                    "message" => $ex->getMessage()
                ], 400);
            }

        }
        /**
         * Method optionsTokenAction [OPTIONS]
         */
        public function optionsTokenAction(Request $request, Response $response): Response
        {
            return $this->jsonResponse([
                "success" => true,
                "message" => 'Options request successful'
            ], 204);
        }
    }
}