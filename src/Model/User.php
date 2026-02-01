<?php 
namespace  App\Model 
{

    use App\Exception\UserException;
    use App\Helper\UserRole; 
    use App\Helper\UserStatus; 
    use App\Helper\Metadata; 

    final class User
    {
        protected readonly ?int $id;
        protected ?string $fullname;
        protected ?string $username;
        protected ?string $email;
        protected ?string $password;
        protected UserStatus $status;
        protected ?int $attempts;
        protected UserRole $role;
        protected ?array $metadata = [];
        protected ?\DateTimeInterface $createdAt;
        protected ?\DateTimeInterface $updatedAt;


        public function __construct(
            ?int $id,
            ?string $fullname,
            ?string $username,
            ?string $email,
            ?string $password,
            UserStatus $status = UserStatus::INACTIVE,
            ?int $attempts = 0,
            UserRole $role = UserRole::STANDARD,
            array $metadata = [],
            string|\DateTimeInterface|null $createdAt = null,
            string|\DateTimeInterface|null $updatedAt = null
        ) {
            $this
                ->setId($id)
                ->setFullname($fullname)
                ->setUsername($username)
                ->setEmail($email)
                ->setPassword($password)
                ->setStatus($status)
                ->setAttempts($attempts)
                ->setRole($role)
                ->setMetadata($metadata)
                ->setCreatedAt($createdAt)
                ->setUpdatedAt($updatedAt);
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
        public function getStatus(): UserStatus
        {
            return $this->status;
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
         * Get the value of role
         *
         * @param string|null $role
         * @return self
         */
        public function getRole(): UserRole
        {
            return $this->role;
        }
        /**
         * Get the value of metadata
         *
         * @return array|null
         */
        public function getMetadata(): ?array
        {
            return $this->metadata;
        }
        public function getCreatedAt(): ?\DateTimeImmutable
        {
            return $this->createdAt;
        }
        public function getUpdatedAt(): ?\DateTimeImmutable
        {
            return $this->updatedAt;
        }
        public function getMetadataToJson(): string
        {
            return json_encode($this->getMetadata());
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
        public function setStatus(UserStatus $status): self
        {
            $this->status = $status;
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

        /**
         * Set the value of role 
         * 
         * @param UserRole $role
         * 
         * @return self
         */
        public function setRole(UserRole $role): self
        {
            $this->role = $role;
            return $this;
        }

        /** Set the value of metadata
         *
         * @param array|null $metadata
         *
         * @return self
         */
        public function setMetadata(?array $metadata): self
        {
            foreach ($metadata as $m) {
                if ($m instanceof Metadata) {
                    $this->metadata[] = $m;
                } elseif (is_array($m)) {
                    $this->metadata[] = Metadata::fromArray($m);
                }
            }
            return $this;
        }
        public function setCreatedAt(string|\DateTimeInterface|null $createdAt = null): self
        {
            if ($createdAt === null) {
                $this->createdAt = new \DateTimeImmutable();
                return $this;
            }

            if (is_string($createdAt)) {
                try {
                    $createdAt = new \DateTimeImmutable($createdAt);
                } catch (\Exception) {
                    throw new UserException('Invalid createdAt format');
                }
            }
            $this->createdAt = $createdAt;
            return $this;
        }
        public function setUpdatedAt(string|\DateTimeInterface|null $updatedAt = null): self
        {
            if ($updatedAt === null) {
                $this->updatedAt = new \DateTimeImmutable();
                return $this;
            }

            if (is_string($updatedAt)) {
                try {
                    $updatedAt = new \DateTimeImmutable($updatedAt);
                } catch (\Exception) {
                    throw new UserException('Invalid updatedAt format');
                }
            }
            $this->updatedAt = $updatedAt;
            return $this;
        }
        public function toArray(): array
        {
            return [
                'id'        => $this->getId(),
                'fullname'  => $this->getFullname(),
                'username'  => $this->getUsername(),
                'email'     => $this->getEmail(),
                'status'    => $this->getStatus()?->value,
                'attempts'  => $this->getAttempts(),
                'role'      => $this->getRole()?->value,
                'metadata'  => $this->getMetadata(),
                'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
                'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
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
                status: UserStatus::tryFrom($data['status'] ?? UserStatus::INACTIVE->value) ?? UserStatus::INACTIVE,
                attempts: $data['attempts'] ?? 0,
                role: UserRole::tryFrom($data['role_id'] ?? UserRole::STANDARD->value) ?? UserRole::STANDARD,
                metadata: $data['metadata'] ?? [],
                createdAt: $data['created_at'] ?? null,
                updatedAt: $data['updated_at'] ?? null
            );
        }
        public static function fromObject(object $data): User
        {   
            return new static(
                id: $data->id ?? null,
                fullname: $data->fullname ?? null,
                username: $data->username ?? null,
                email: $data->email ?? null,
                password: $data->password ?? null,
                status: UserStatus::tryFrom($data->status ?? UserStatus::INACTIVE->value) ?? UserStatus::INACTIVE,
                attempts: $data->attempts ?? 0,
                role: UserRole::tryFrom($data->role_id ?? UserRole::STANDARD->value) ?? UserRole::STANDARD,
                metadata: $data->metadata ?? [],
                createdAt: $data->created_At ?? null,
                updatedAt: $data->updated_At ?? null
            );
        }
        public static function fromJson(string $json): User
        {
            $data = json_decode($json, true);
            return self::fromState($data);
        }

        public function isActive(): bool {
            return $this->getStatus() === UserStatus::ACTIVE;
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
        public function isAccount(): bool
        {
            return str_contains($_SERVER['REQUEST_URI'], 'compte');
        }
        public function addMetadata(Metadata $metadata): void
        {
            $this->metadata[] = $metadata;
        }
        public function isPassword(string $password): bool 
        {
            return password_verify($password, $this->getPassword());
        }
        public function hashPassword(): string{
            return password_hash($this->getPassword(), PASSWORD_BCRYPT);
        }
    }
}
