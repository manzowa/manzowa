<?php 

namespace App\Model;
use App\Exception\CommentException;

final class Comment
{
    protected ?int $id;
    protected ?int $userId;
    protected ?int $schoolId;
    protected ?string $content;
    protected ?\DateTimeInterface $createdAt;

    public function __construct(
        ?int $id,
        ?int $userId,
        ?int $schoolId,
        ?string $content,
        string|\DateTimeInterface|null $createdAt = null
    ) {
        $this->setId($id)
             ->setUserId($userId)
             ->setSchoolId($schoolId)
             ->setContent($content)
             ->setCreatedAt($createdAt);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getContent(): ?string {
        return $this->content;
    }

    public function getSchoolId(): ?int {
        return $this->schoolId;
    }

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->createdAt;
    }
    public function setId(?int $id): self {
        if ((!is_null($id)) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807)) {
            throw new CommentException("Comment ID error");
        }
        $this->id = $id;
        return $this;
    }
    public function setContent(?string $content): self {
        if (is_null($content) || empty($content)) {
            throw new CommentException("Comment Content error");
        }
        $this->content = $content;
        return $this;
    }
    public function setSchoolId(?int $schoolId): self {
        if ((!is_null($schoolId)) && (!is_numeric($schoolId) || $schoolId <= 0 || $schoolId > 9223372036854775807)) {
            throw new CommentException("Comment School ID error");
        }
        $this->schoolId = $schoolId;
        return $this;
    }
    public function setUserId(?int $userId): self {
        if ((!is_null($userId)) && (!is_numeric($userId) || $userId <= 0 || $userId > 9223372036854775807)) {
            throw new CommentException("Comment User ID error");
        }
        $this->userId = $userId;
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
                throw new CommentException('Invalid createdAt format');
            }
        }

        $this->createdAt = $createdAt;
        return $this;
    }
    public function toArray(): array {
        return [
            'id'        => $this->id,
            'user_id'    => $this->userId,
            'school_id'  => $this->schoolId,
            'content'   => $this->content,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
    public static function fromState(array $data = []): Comment {
        return new static(
            id: $data['id'] ?? null,
            userId: $data['user_id'] ?? throw new CommentException('userId missing'),
            schoolId: $data['school_id'] ?? throw new CommentException('schoolId missing'),
            content: $data['content'] ?? throw new CommentException('content missing'),
            createdAt: $data['created_at'] ?? null
        );
    }

    public static function fromObject(object $data): Comment {
        return new static(
            id: $data->id ?? null,
            userId: $data->user_id ?? throw new CommentException('userId missing'),
            schoolId: $data->school_id ?? throw new CommentException('schoolId missing'),
            content: $data->content ?? throw new CommentException('content missing'),
            createdAt: $data->created_at ?? null
        );
    }
    public static function fromJson(string $json): Comment {
        $data = json_decode($json, true);
        return self::fromState($data);
    }
}