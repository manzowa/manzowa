<?php 

namespace App\Repository;
use App\Model\Comment;

class CommentRepository extends Repository implements \Countable
{
    public function retrieve(?int $id = null, int $limit = 0): array
    {
        $comments = [];
        $conditions = [];
        $params = [];
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;
        $command = "SELECT * FROM comments ";

        if (!is_null($id)) {
            $conditions[] = "id = :id";
            $params[':id'] = [$id, \PDO::PARAM_INT];
        }

        if (count($conditions) > 0) {   
            $command .= " WHERE " . implode(" AND ", $conditions);
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
        $commentRows = $this->executeQuery()
            ->getResults();
        if (count($commentRows) > 0) {
            foreach ($commentRows as $commentRow) {
                $comment = Comment::fromState($commentRow);
                $comments[] = $comment->toArray();
            }
        }

        return $comments;
    }
    public function add(Comment $comment): self
    {
        
        $userId = $comment->getUserId();
        $schoolId = $comment->getSchoolId();
        $content = $comment->getContent();
        $createdAt = $comment->getCreatedAt()->format('Y-m-d H:i:s');

        $command = "INSERT INTO comments (user_id, school_id, content, created_at) ";
        $command .= "VALUES (:userId, :schoolId, :content, :createdAt)";

        $this->prepare($command);
        $this->bindParam(':content', $content, \PDO::PARAM_STR);
        $this->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $this->bindParam(':schoolId', $schoolId, \PDO::PARAM_INT);
        $this->bindParam(':createdAt', $createdAt, \PDO::PARAM_STR);

        $this->executeQuery();

        return $this;
    }

    public function update(Comment $comment): self
    {
        $id = $comment->getId();
        $content = $comment->getContent();

        $command = "UPDATE comments SET content = :content WHERE id = :id";

        $this->prepare($command);
        $this->bindParam(':content', $content, \PDO::PARAM_STR);
        $this->bindParam(':id', $id, \PDO::PARAM_INT);

        $this->executeQuery();

        return $this;
    }

    public function remove(int $id): self
    {
        $command = "DELETE FROM comments WHERE id = :id";

        $this->prepare($command);
        $this->bindParam(':id', $id, \PDO::PARAM_INT);
        $this->executeQuery();

        return $this;
    }

    public function count(): int
    {
        $command = 'SELECT count(id) as totalCount FROM comments';
        $data = $this->prepare($command)
            ->executeQuery()
            ->getResults();
        $result = current($data);
        
        return intval($result['totalCount']);
    }
}