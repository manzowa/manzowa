<?php 

namespace App\Security;
use App\Model\User;
use App\Model\Token;
use App\Repository\UserRepository;
use App\Session\Session;
use App\Database\Connexion;

class Auth
{
    private ?string $token;
    private Session $session;
    private UserRepository $repository;

    public function __construct(?string $token = null)
    {
        $this->token = $token;
        $this->session = new Session();
        $this->repository = new UserRepository(Connexion::read());
       
    }
    public function user(): User|bool
    {
        if ($this->session->has('user')) {
            return $this->session->get('user');
        }
        return false;
    }
    public function check(): bool 
    {
        return $this->session->has('user');
    }

    public function attempt($username, $password)
    {
        $user = $this->repository->findByUsernameOrEmail($username);
        if ($user && is_null($user->getId())) {
            return false;
        }
        if (password_verify($password, $user->getPassword())) {
            $this->session->set('user', $user);
            $this->session->regenerate();
            return true;
        }
        return false;
    }
    public function deconnecter(): void
    {
        $this->session->destroy();
    }
    public function getToken(): ?Token 
    {
        if (!is_null($this->token) && @strlen($this->token) > 1) {
            $tokenRows = $this->repository
                ->retrieveToken(accessToken: $this->token);
            $token = current($tokenRows);
            return $token;
        }
        return null;
    }

    public function hasToken(): bool {
        return (!is_null($this->token) && is_object($this->getToken()));
    }
    public function getUserByToken(): ?User 
    {
        if ($this->hasToken()) {
            $id = $this->getToken()->getUserId();
            $users = $this->repository->retrieve(id: $id);
            $user = current($users);
            return $user;
        }
        return null;
    }
}