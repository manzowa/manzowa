<?php 
namespace  App\Model 
{

    final class User
    {
        protected readonly ?int $id;
        protected ?string $fullname;
        protected ?string $username;
        protected ?string $email;
        protected ?string $password;
        protected ?string $active;
        protected ?int $attempts;

        public function __construct(
            ?int $id,
            ?string $fullname,
            ?string $username,
            ?string $email,
            ?string $password,
            ?string $active = 'Y',
            ?int $attempts = 0
        ) {
            $this
                ->setId($id)
                ->setFullname($fullname)
                ->setUsername($username)
                ->setEmail($email)
                ->setPassword($password)
                ->setActive($active)
                ->setAttempts($attempts);
        }
        /**
         * Get the value of id
         */
        public function getId(): ?int
        {
            return $this->id;
        }
        /**
         * Get the value of fullname
         *
         * @return ?string
         */
        public function getFullname(): ?string
        {
            return $this->fullname;
        }
        /**
         * Get the value of username
         *
         * @return ?string
         */
        public function getUsername(): ?string
        {
            return $this->username;
        }
        /**
         * Get the value of email
         *
         * @return ?string
         */
        public function getEmail(): ?string
        {
            return $this->email;
        }
        /**
         * Get the value of password
         *
         * @return ?string
         */
        public function getPassword(): ?string
        {
            return $this->password;
        }
        /**
         * Get the value of active
         *
         * @return ?string
         */
        public function getActive(): ?string
        {
            return $this->active;
        }
        /**
         * Get the value of attempts
         *
         * @return ?int
         */
        public function getAttempts(): ?int
        {
            return $this->attempts;
        }
        /**
         * Set the value of id
         */
        public function setId(?int $id): self
        {
            $this->id = $id;
            return $this;
        }
        /**
         * Set the value of fullname
         *
         * @param ?string $fullname
         *
         * @return self
         */
        public function setFullname(?string $fullname): self
        {
            $this->fullname = $fullname;
            return $this;
        }
        /**
         * Set the value of username
         *
         * @param ?string $username
         *
         * @return self
         */
        public function setUsername(?string $username): self
        {
            $this->username = $username;
            return $this;
        }
        /**
         * Set the value of email
         *
         * @param ?string $email
         *
         * @return self
         */
        public function setEmail(?string $email): self
        {
            $this->email = $email;
            return $this;
        }
        /**
         * Set the value of password
         *
         * @param ?string $password
         *
         * @return self
         */
        public function setPassword(?string $password): self
        {
            $this->password = $password;
            return $this;
        }
        /**
         * Set the value of active
         *
         * @param ?string $active
         *
         * @return self
         */
        public function setActive(?string $active): self
        {
            $active = empty($active)? $active : mb_strtoupper($active, 'UTF-8');
            $this->active = $active;
            return $this;
        }
        /**
         * Set the value of attempts
         *
         * @param ?int $attempts
         *
         * @return self
         */
        public function setAttempts(?int $attempts): self
        {
            $this->attempts = $attempts;
            return $this;
        }
        public function toArray(): array
        {
            return [
                'id'        => $this->getId(),
                'fullname'  => $this->getFullname(),
                'username'  => $this->getUsername(),
                'email'     => $this->getEmail(),
                'password'  => $this->getPassword(),
                'active'    => $this->getActive(),
                'attempts'  => $this->getAttempts()
            ];
        }
        public static function fromState(array $data = [])
        {
            return new static(
                id: $data['id'] ?? null,
                fullname: $data['fullname'] ?? null,
                username: $data['username'] ?? null,
                email: $data['email']?? null,
                password: $data['password'] ?? null,
                active: $data['active'] ?? null,
                attempts: $data['attempts'] ?? null
            );
        }
        public function isActive(): bool {
            return $this->getActive() === 'Y';
        }
        public function resetAttempts(): self {
            return $this->setAttempts(0);
        }
        public function incrementAttempts(): self {
            return $this->setAttempts($this->getAttempts() + 1);
        }
        public function isLocked(): bool {
            return $this->getAttempts() >= 3;
        }
        public static function nullUser() {
            return new static(null, null, null, null, null);
        }   

        public function isLoggedIn (): bool {
            return isset($_SESSION['user']);
        }
        function isAccount(): bool
        {
            return str_contains($_SERVER['REQUEST_URI'], 'compte');
        }
        
        public function isPassword(string $password): bool 
        {
            return password_verify($password, $this->getPassword());
        }
    }
}
