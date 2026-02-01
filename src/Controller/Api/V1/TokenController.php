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
            $repository = new UserRepository(Connexion::write());
            $jsonObject = $request->getParsedBody();
            // Start Transaction
            $repository->beginTransaction();

            try 
            {
                // Variables
                $username = $jsonObject->username;
                $password = $jsonObject->password;
                $user = $repository->findByUsernameOrEmail($username);

                if ($repository->rowCount() === 0) {
                    if ($repository->inTransaction()) {
                        $repository->rollBack();
                    }
                    
                    return $this->response(false, 'Username or Password is incorrect', null, 401);
                }

                if (!$user->isPassword($password)) {
                    $user->incrementAttempts(); // Update login attempts
                    $repository->update($user);
                    if ($repository->rowCount() === 0) {
                        if ($repository->inTransaction()) {
                            $repository->rollBack();
                        }
                        return $this->response(false, 'Username or Password is incorrect', null, 401);
                    }
                }
                $accessToken  = \App\generateToken();
                $refreshToken = \App\generateToken();

                $access_token_expiry_seconds = 1200;
                $refresh_token_expiry_seconds = 1209600;

               
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
               
                if ($repository->inTransaction()) {
                    $repository->commit();
                }
                
                return $this->response(true, 'Token created successfully', [
                        'session_id' => intval($lastSessionId),
                        'access_token' => $accessToken,
                        'access_token_expires_in' => $access_token_expiry_seconds,
                        'refresh_token' => $refreshToken,
                        'refresh_token_expires_in' => $refresh_token_expiry_seconds
                    ], 201
                );

            } catch (UserException $ex) {
                if ($repository->inTransaction()) {
                    $repository->rollBack();
                }
                return $this->response(false, $ex->getMessage(), null, 500);
            }
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
            $this->ensureValidArguments("Invalid Token ID", $session_id);

            try {
                // Establish the connection Database
                $accessToken = \App\beareToken( 
                    $request->getHeaderLine('HTTP_AUTHORIZATION')
                );
                $repository = new UserRepository(Connexion::write());
                $repository->removeToken($session_id, $accessToken);

                if ($repository->getTempRowCounted() === 0) {
                   
                    return $this->response(false, "Failed to log out of this sessions using access token provided", null, 400);
                }
        
                return $this->response(true, 'Logged out successfully', [
                    "session_id" => intval($session_id)
                ], 204);

            } catch (UserException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }
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
            $this->ensureValidArguments("Invalid Token ID", $session_id);
            $repository = new UserRepository(Connexion::write());

            try 
            {
                // Establish the connection Database
                $jsonObject = $request->getParsedBody();
                $accessToken = \App\beareToken( 
                    $request->getHeaderLine('HTTP_AUTHORIZATION')
                );
                $refreshToken = $jsonObject->refreshToken;

                
                $tokens = $repository->retrieveToken(
                    id: $session_id, 
                    accessToken:$accessToken, 
                    refreshToken: $refreshToken
                );
              
                if ($repository->getTempRowCounted() == 0) {
                    return $this->errorResponse(
                        'Access token or refresh token is invalid', 401
                    );
                }
                $token = current($tokens);
               
                $users = $repository->retrieve(id: $token->getUserId());
                $user = current($users);

                // Check if user is active
                if (!$user->isActive()) {
                    
                    return $this->response(false, 'User account is not active', null, 401);
                }
                // Check if user has exceeded maximum login attempts
                if ($user->isLocked()) {
                    return $this->response(false, 'User account is currently locked out', null, 401);
                }
                 // Check refresh token expiration
                if ($token->isRefreshTokenExpiry()) {
                    return $this->response(false, 'Refresh token has expired - please log in again', null, 401);
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
                    
                    return $this->response(false, 'Access token could not be refresh - please log in again', null, 401);
                }

                return $this->response(true, 'Access token refreshed successfully', [
                        'session_id' => intval($session_id),
                        'access_token' => $accessToken,
                        'access_token_expires_in' => $accessTokenExpirySeconds,
                        'refresh_token' => $refreshToken,
                        'refresh_token_expires_in' => $refreshTokenExpirySeconds
                    ], 200
                );

            } catch (UserException $ex) {
                return $this->response(false, $ex->getMessage(), null, 500);
            }

        }
        /**
         * Method optionsTokenAction [OPTIONS]
         */
        public function optionsTokenAction(Request $request, Response $response): Response
        {
            return $this->response(true, 'Options request successful', null, 204);
        }
    }
}