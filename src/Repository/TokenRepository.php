<?php 

namespace App\Repository;

use App\Model\Token;

class TokenRepository extends Repository implements \Countable 
{
    public function retrieve(
        ?int $id = null, 
        ?int $userid = null,
        ?string $accessToken = null, 
        ?string $accessTokenExpiry = null,
        ?string $refreshToken = null,
        ?string $refreshTokenExpiry = null
    ): array {
        $tokens = [];
        $hasId = !is_null($id) && $id > 0;
        $hasUserId = !is_null($userid) && $userid > 0;
        $hasAccessToken = !is_null($accessToken) && $accessToken !== '';
        $hasAccessTokenExpiry = !is_null($accessTokenExpiry) && $accessTokenExpiry !== '';
        $hasRefreshToken = !is_null($refreshToken) && $refreshToken !== '';
        $hasRefreshTokenExpiry = !is_null($refreshTokenExpiry) && $refreshTokenExpiry !== '';

        $command = 'SELECT sessions.* FROM sessions ';

        if ($hasId && !$hasUserId 
            && !$hasAccessToken && !$hasAccessTokenExpiry 
            && !$hasRefreshToken && !$hasRefreshTokenExpiry
        ) {
            $command .= 'WHERE id = :id';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);

        } elseif ($hasUserId && !$hasId
            && !$hasAccessToken && !$hasAccessTokenExpiry 
            && !$hasRefreshToken && !$hasRefreshTokenExpiry
        ) {
            $command .= ' WHERE userid = :userid';
            $this->prepare($command)
                ->bindParam(':userid', $userid, \PDO::PARAM_INT);

        } elseif ($hasAccessToken && !$hasId 
            && !$hasUserId && !$hasAccessTokenExpiry 
            && !$hasRefreshToken && !$hasRefreshTokenExpiry
        ) {
            $command .= 'WHERE accessToken = :accessToken';
            $this->prepare($command)
                ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR);

        } elseif ($hasId && $hasUserId 
            && !$hasAccessToken && !$hasAccessTokenExpiry 
            && !$hasRefreshToken && !$hasRefreshTokenExpiry
        ) {
            $command .= 'WHERE id = :id AND userid = :userId';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':userid', $userid, \PDO::PARAM_INT);

        } elseif ($hasId && $hasAccessToken 
            && $hasRefreshToken && !$hasUserId 
            && !$hasAccessTokenExpiry && !$hasRefreshTokenExpiry
        ) {
            $command .= 'WHERE id = :id AND accessToken = :accessToken AND refreshtoken = :refreshToken';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR)
                ->bindParam(':refreshToken', $refreshToken, \PDO::PARAM_STR);
        } else {
            $this->prepare($command);
        }

        $tokenRows = $this->executeQuery()
            ->getResults();
        if (count($tokenRows) > 0) {
            foreach ($tokenRows as $tokenRow) {
                $token = Token::fromState($tokenRow);
                $tokens[] = $token;
            }
        }
        return $tokens;
    }
    public function add(Token $session): self
    {
        $userid = $session->getUserId();
        $accessToken = $session->getAccessToken();
        $accessTokenExpiry = $session->getAccessTokenExpiry();
        $refreshToken = $session->getRefreshToken();
        $refreshTokenExpiry = $session->getRefreshTokenExpiry();

        $command  = 'INSERT INTO sessions (userid, accesstoken, ';
        $command .= 'accesstokenexpiry, refreshtoken, refreshtokenexpiry) ';
        $command .= 'VALUES ( ';
        $command .= ':userid, ';
        $command .= ':accessToken, date_add(NOW(), INTERVAL :accessTokenExpiry SECOND),';
        $command .= ':refreshToken, date_add(NOW(), INTERVAL :refreshTokenExpiry SECOND)';
        $command .= ')';

        $this
            ->prepare($command)
            ->bindParam(':userid', $userid, \PDO::PARAM_INT)
            ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR)
            ->bindParam(':accessTokenExpiry', $accessTokenExpiry, \PDO::PARAM_STR)
            ->bindParam(':refreshToken', $refreshToken, \PDO::PARAM_STR)
            ->bindParam(':refreshTokenExpiry', $refreshTokenExpiry, \PDO::PARAM_STR)
            ->executInsert();

        return $this;
    }
    public function update(Token $session): self
    {
        $userid = $session->getUserId();
        $accessToken = $session->getAccessToken();
        $accessTokenExpiry = $session->getAccessTokenExpiry();
        $refreshToken = $session->getRefreshToken();
        $refreshTokenExpiry = $session->getRefreshTokenExpiry();
        $id = $session->getId();

        $command = 'UPDATE sessions SET userid= :userid, accesstoken= :accessToken, ';
        $command.= 'accesstokenexpiry= :accessTokenExpiry, refreshtoken= :refreshToken, ';
        $command.= 'refreshtokenexpiry= :refreshTokenExpiry WHERE id= :id ';

        $this
            ->prepare($command)
            ->bindParam(':userid', $userid, \PDO::PARAM_INT)
            ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR)
            ->bindParam(':accessTokenExpiry', $accessTokenExpiry, \PDO::PARAM_STR)
            ->bindParam(':refreshToken', $refreshToken, \PDO::PARAM_STR)
            ->bindParam(':refreshTokenExpiry', $refreshTokenExpiry, \PDO::PARAM_STR)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->executeUpdate();

        return $this;
    }
   public function remove(int $id, ?string $accessToken = null): self
    {
        $command = 'DELETE FROM sessions WHERE id = :id';
        $this->prepare($command)->bindParam(':id', $id, \PDO::PARAM_INT);

        if (!is_null($accessToken) && $accessToken !== '') 
        {
            $command .= ' AND accessToken = :accessToken';
            $this->prepare($command)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->bindParam(':accessToken', $accessToken, \PDO::PARAM_STR);
        }
        $this->executeDelete();

        return $this;
    }
    public function count(): int
    {
        $command = 'SELECT count(id) as totalCount FROM sessions';
        $data = $this->prepare($command)
            ->executeQuery()
            ->getResults();
        $result = current($data);
        
        return intval($result['totalCount']);
    }
}