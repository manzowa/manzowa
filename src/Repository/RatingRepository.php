<?php 

namespace App\Repository;

use App\Model\Rating;

class RatingRepository extends Repository implements \Countable
{
    public function retrieve(?int $id = null, int $limit = 0): array
    {
        $ratings = [];
        $params = [];
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;
        $command = "SELECT * FROM ratings ";

        if (!is_null($id)) {
            $command .= " WHERE id = :id ";
            $params[':id'] = [$id, \PDO::PARAM_INT];
        }
        $command .= " ORDER BY id DESC ";
        if (!is_null($limit)) {
            $command .= " LIMIT :limit ";
            $params[':limit'] = [$limit, \PDO::PARAM_INT];
        }
        $this->prepare($command);
        // Bind parameters
        foreach ($params as $param => [$value, $type]) {
            $this->bindParam($param, $value, $type);
        }
        $ratingRows = $this->executeQuery()
            ->getResults();
        if (count($ratingRows) > 0) {
            foreach ($ratingRows as $ratingRow) {
                $rating = Rating::fromState($ratingRow);
                $ratings[] = $rating->toArray();
            }
        }

        return $ratings;
    }
    public function add(Rating $rating): self
    {
        
        $userId = $rating->getUserId();
        $schoolId = $rating->getSchoolId();
        $score = $rating->getScore();
        $createdAt = $rating->getCreatedAt()->format('Y-m-d H:i:s');

        $command = "INSERT INTO ratings (user_id, school_id, score, created_at) ";
        $command .= "VALUES (:userId, :schoolId, :score, :createdAt)";

        $this->prepare($command);
        $this->bindParam(':score', $score, \PDO::PARAM_INT);
        $this->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $this->bindParam(':schoolId', $schoolId, \PDO::PARAM_INT);
        $this->bindParam(':createdAt', $createdAt, \PDO::PARAM_STR);

        $this->executeQuery();

        return $this;
    }
    public function update(Rating $rating): self
    {
        $id = $rating->getId();
        $score = $rating->getScore();

        $command = "UPDATE ratings SET score = :score ";
        $command .= "WHERE id = :id";

        $this->prepare($command);
        $this->bindParam(':id', $id, \PDO::PARAM_INT);
        $this->bindParam(':score', $score, \PDO::PARAM_INT);

        $this->executeQuery();

        return $this;
    }
    public function remove(int $id): self
    {
        $command = "DELETE FROM ratings WHERE id = :id";

        $this->prepare($command);
        $this->bindParam(':id', $id, \PDO::PARAM_INT);
        $this->executeQuery();

        return $this;
    }
    public function count(): int
    {
        $command = 'SELECT count(id) as totalCount FROM ratings';
        $data = $this->prepare($command)
            ->executeQuery()
            ->getResults();
        $result = current($data);
        
        return intval($result['totalCount']);
    }
}