<?php 

/**
 * Rating Model
 * 
 * php version 8.2
 *
 * @category App\Model
 * @package  App\Model
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App\Model;

use App\Exception\RatingException;

final class Rating {
    private ?int $id;
    private ?int $userId;
    private ?int $schoolId;
    private ?int $score;
    private ?\DateTimeInterface $createdAt;

    public function __construct(
        ?int $id,
        ?int $userId,
        ?int $schoolId,
        ?int $score,
        string|\DateTimeInterface|null $createdAt = null
    ) {
        $this->setId($id)
             ->setUserId($userId)
             ->setSchoolId($schoolId)
             ->setScore($score)
             ->setCreatedAt($createdAt);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUserId(): ?int {
        return $this->userId;
    }
    public function getSchoolId(): ?int {
        return $this->schoolId;
    }
    public function getScore(): ?int {
        return $this->score;
    }
    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->createdAt;
    }

    public function setId(?int $id): self {
        $this->id = $id;
        return $this;
    }
    public function setUserId(?int $userId): self {
        $this->userId = $userId;
        return $this;
    }
    public function setSchoolId(?int $schoolId): self {
        $this->schoolId = $schoolId;
        return $this;
    }
    public function setScore(?int $score): self {
        if (is_null($score) || !is_numeric($score) || $score < 1 || $score > 5) {
            throw new RatingException("Rating Score error");
        }
        $this->score = $score;
        return $this;
    }
    public function setCreatedAt(string|\DateTimeInterface|null $createdAt = null): self {
        if ($createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
            return $this;
        }

        if (is_string($createdAt)) {
            try {
                $createdAt = new \DateTimeImmutable($createdAt);
            } catch (\Exception) {
                throw new RatingException('Invalid createdAt format');
            }
        }

        $this->createdAt = $createdAt;
        return $this;;
    }
    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'school_id' => $this->schoolId,
            'score' => $this->score,
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
        ];
    }
    public static function fromState(array $data = []): Rating {
        return new static(
            id: $data['id'] ?? null,
            userId: $data['user_id'] ?? null,
            schoolId: $data['school_id'] ?? null,
            score: $data['score'] ?? null,
            createdAt: $data['created_at'] ?? null
        );
    }

    public static function fromObject(object $data): Rating {
        return new static(
            id: $data->id ?? null,
            userId: $data->user_id ?? null,
            schoolId: $data->school_id ?? null,
            score: $data->score ?? null,
            createdAt: $data->created_at ?? null
        );
    }
    public static function fromJson(string $json): Rating {
        $data = json_decode($json, true);
        return self::fromState($data);
    }
    
}