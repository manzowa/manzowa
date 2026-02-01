<?php 

namespace App\Repository;

use App\Model\User;
use App\Model\Token;
use App\Repository\TokenRepository;

class UserRepository extends Repository implements \Countable 
{
    public function retrieve(?int $id = null, int $limit=0): array 
    {
        $users = [];
        $conditions = [];
        $params = [];

        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;
        $command = "SELECT * FROM users ";

        if (!is_null($id)) {
            $conditions[] = "id = :id";
            $params[':id'] = $id;
        }
        if (count($conditions) > 0) {   
            $command .= " WHERE " . implode(" AND ", $conditions);
        }
        $command .= " ORDER BY id DESC ";

        if (!is_null($limit) ) {
            $command .= " LIMIT :limit ";
            $params[':limit'] = $limit;
        }

        $this->prepare($command);
        foreach ($params as $param => $value) {
            $this->bindParam($param, $value, \PDO::PARAM_STR);
        }
        $userRows = $this->executeQuery()
            ->getResults();
        if (count($userRows) > 0) {
            foreach ($userRows as $userRow) {
                $user = User::fromState($userRow);
                $users[] = $user;
            }
        }
        
        return $users;
    }
    public function add(User $user): self {
    
        $fullname = $user->getFullName();
        $username = $user->getUsername();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $attempts = $user->getAttempts();
        $status = $user->getStatus()?->value;
        $role_id = $user->getRole()?->value;
        $createdAt = $user->getCreatedAt();
        $updatedAt = $user->getUpdatedAt();
        $metadata = $user->getMetadata();

        $command  = 'INSERT INTO users (fullname, username, email, password, attempts, status, role_id, created_at, updated_at, metadata) ';
        $command .= 'VALUES (:fullname, :username, :email, :password, :attempts, :status, :role_id, :created_at, :updated_at, :metadata)';

        $this
            ->prepare($command)
            ->bindParam(':fullname', $fullname, \PDO::PARAM_STR)
            ->bindParam(':username', $username, \PDO::PARAM_STR)
            ->bindParam(':email', $email, \PDO::PARAM_STR)
            ->bindParam(':password', $password, \PDO::PARAM_STR)
            ->bindParam(':attempts', $attempts, \PDO::PARAM_INT)
            ->bindParam(':status', $status, \PDO::PARAM_STR)
            ->bindParam(':role_id', $role_id, \PDO::PARAM_INT)
            ->bindParam(':created_at', $createdAt, \PDO::PARAM_STR)
            ->bindParam(':updated_at', $updatedAt, \PDO::PARAM_STR)
            ->bindParam(':metadata', $metadata, \PDO::PARAM_STR)
            ->executeInsert();
        return $this;
    }
    public function update(User $user): self{
        $id = $user->getId();
        $fullname = $user->getFullName();
        $username = $user->getUsername();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $attempts = $user->getAttempts();
        $status = $user->getStatus()?->value;
        $role_id = $user->getRole()?->value;
        $createdAt = $user->getCreatedAt();
        $updatedAt = $user->getUpdatedAt();
        $metadata = $user->getMetadata();

        $command  = 'UPDATE users SET fullname= :fullname, username= :username, email= :email, ';
        $command .= 'password= :password, attempts= :attempts, status= :status, role_id= :role_id, ';
        $command .= 'reated_at= :created_at, updated_at= :updated_at, metadata= :metadata '; 
        $command .= 'WHERE id= :id ';

        $this 
            ->prepare($command)
            ->bindParam(':fullname', $fullname, \PDO::PARAM_STR)
            ->bindParam(':username', $username, \PDO::PARAM_STR)
            ->bindParam(':email', $email, \PDO::PARAM_STR)
            ->bindParam(':password', $password, \PDO::PARAM_STR)
            ->bindParam(':attempts', $attempts, \PDO::PARAM_INT)
            ->bindParam(':status', $status, \PDO::PARAM_STR)
            ->bindParam(':role_id', $role_id, \PDO::PARAM_INT)
            ->bindParam(':created_at', $createdAt, \PDO::PARAM_STR)
            ->bindParam(':updated_at', $updatedAt, \PDO::PARAM_STR)
            ->bindParam(':metadata', $metadata, \PDO::PARAM_STR)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->executeUpdate();
        return $this;
    } 
    public function remove(int $id): self {
        if (!is_null($id)) {
            $command = 'DELETE FROM users WHERE id = :id';
            $this
                ->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->executeDelete();
        }
        return $this;
    }   
    public function count(): int
    {
        $command = 'SELECT count(id) as totalCount FROM users';
        $data = $this->prepare($command)
            ->executeQuery()
            ->getResults();
        $result = current($data);
        
        return intval($result['totalCount']);
    }
    public function findByUsernameOrEmail(string $usernameOrEmail): User {
        $command = "SELECT * FROM users WHERE username = :username or email = :email";
        $row = $this->prepare($command)
            ->bindParam(':username', $usernameOrEmail, \PDO::PARAM_STR)
            ->bindParam(":email", $usernameOrEmail, \PDO::PARAM_STR)
            ->executeQuery()
            ->getResults();
        $rowed = current($row);
        if (is_array($rowed) && count($rowed)> 0) {
            return User::fromState($rowed);
        } else {
            return User::nullUser();
        }
    }
    public function findUserByUsername(string $username):? User {
        $command = 'SELECT users.* FROM users WHERE username = :username';
        $row = $this->prepare($command)
            ->bindParam(':username', $username, \PDO::PARAM_STR)
            ->executeQuery()
            ->getResults();
        $rowed = current($row);
        if (is_array($rowed) && count($rowed)> 0) {
            return User::fromState($rowed);
        } else {
            return User::nullUser();
        }
    }
    public function retrieveToken(
        ?int $id = null, 
        ?int $userid = null,
        ?string $accessToken = null, 
        ?string $accessTokenExpiry = null,
        ?string $refreshToken = null,
        ?string $refreshTokenExpiry = null
    ): array 
    {
        $tokens = [];
        $repository = new TokenRepository($this->connexion());
        $tokens = $repository->retrieve(
            $id, $userid, $accessToken, 
            $accessTokenExpiry, $refreshToken,
            $refreshTokenExpiry
        );
        $this->setTempRowCounted((int) $repository->rowCount());
        return $tokens;
    }
    public function addToken(Token $token): self 
    {
        $repository = new TokenRepository($this->connexion());
        $repository->add($token);
        $this->setTempRowCounted((int) $repository->rowCount());
        $this->setStockId((int) $repository->lastInsertId());

        return $this;
    }
    public function updateToken(Token $token): self 
    {
        $repository = new TokenRepository($this->connexion());
        $repository->update(session: $token);
        $this->setTempRowCounted((int) $repository->rowCount());
        return $this;
    }
    public function removeToken(int $id, ?string $accessToken = null): self 
    {
        $repository = new TokenRepository($this->connexion());
        $repository->remove($id, $accessToken);
        $this->setTempRowCounted((int) $repository->rowCount());
        return $this;
    }
}