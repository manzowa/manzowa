<?php

namespace App\Repository;

use App\Model\Image;
use App\Model\School;
use App\Model\Event;
use App\Repository\SchoolRepository;

use function DI\value;

class ImageRepository extends Repository implements \Countable
{
    public function retrieve(
        ?int $id=null,
        ?int $schoolid=null,
        ?int $eventid=null,
        int $limit=0,
        string $type='S'
    ): array {
        $images = [];
        $conditions = [];
        $params = [];
        $joins = [];

        // Enforce a default limit if invalid
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 5;
        $command  = 'SELECT i.* FROM images i ';

        if (!is_null($id)) {
            $conditions[] = 'i.id = :id';
            $params[':id'] = [$id, \PDO::PARAM_INT];
        }

        // Conditions and parameters
        if (!is_null($schoolid)) {
            $joins[] = 'INNER JOIN ecoles e ON i.ecoleid = e.id';
            $conditions[] = 'i.ecoleid = :ecoleid';
            $params[':ecoleid'] = [$schoolid, \PDO::PARAM_INT];
        }
        if (!is_null($eventid)) {
            $joins[] = 'INNER JOIN evenements ev ON i.evenementid = ev.id';
            $conditions[] = 'i.evenementid = :eventid';
            $params[':eventid'] = [$eventid, \PDO::PARAM_INT];
        }
        if (!empty($type)) {
           
            $conditions[] = 'i.type = :type';
            $params[':type'] = [$type, \PDO::PARAM_STR];
        }
        // Append joins
        if (!empty($joins)) {
            $command .= implode(' ', $joins) . ' ';
        }
        // WHERE clause
        if (!empty($conditions)) {
            $command .= 'WHERE ' . implode(' AND ', $conditions) . ' ';
        }

        $command .= 'ORDER BY i.id DESC LIMIT ' . (int)$limit;

        $this->prepare($command);

        // Bind params
        foreach ($params as $key => [$value, $type]) {
            $this->bindParam($key, $value, $type);
        }

        $imageRows = $this->executeQuery()->getResults();

        foreach ($imageRows as $imageRow) {
            $image = Image::fromState($imageRow);
            $images[] = $image->toArray();
        }

        return $images;
    }
    public function add(Image $image): self
    {
        $title = $image->getTitle();
        $filename = $image->getFilename();
        $mimetype = $image->getMimetype();
        $type = $image->getType();
        $ecoleid = $image->getEcoleid();
        $evenementid = $image->getEvenementid();

        $command = 'INSERT INTO images (title, filename, mimetype, type, ecoleid, evenementid)  ';
        $command .= 'VALUES (:title, :filename, :mimetype, :type, :ecoleid, :evenementid) ';

        $this
            ->prepare($command)
            ->bindParam(':title', $title, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':filename', $filename, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':mimetype', $mimetype, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':type', $type, \PDO::PARAM_STR)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->bindParam(':evenementid', $evenementid, \PDO::PARAM_INT)
            ->executeInsert();
        return $this;
    }
    public function update(Image $image): self
    {
        $title = $image->getTitle();
        $filename = $image->getFilename();
        $mimetype = $image->getMimetype();
        $type = $image->getType();
        $ecoleid = $image->getEcoleid();
        $eventid = $image->getEvenementid();
        $id = $image->getId();

        $command  = 'UPDATE images SET title= :title, filename= :filename, mimetype= :mimetype, type= :type, ecoleid= :ecoleid, evenementid= :eventid ';
        $command .= 'WHERE id= :id ';

        $this
            ->prepare($command)
            ->bindParam(':title', $title, \PDO::PARAM_STR)
            ->bindParam(':filename', $filename, \PDO::PARAM_STR)
            ->bindParam(':mimetype', $mimetype, \PDO::PARAM_STR)
            ->bindParam(':type', $type, \PDO::PARAM_STR)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT | \PDO::PARAM_NULL)
            ->bindParam(':eventid', $eventid, \PDO::PARAM_INT | \PDO::PARAM_NULL)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->executeUpdate();

        return $this;
    }
    public function count(): int
    {
        $command = 'SELECT count(id) as totalCount FROM images';
        $data = $this->prepare($command)
            ->executeQuery()
            ->getResults();
        $result = current($data);
        return intval($result['totalCount']);
    }
    public function remove(
        int $id, ?int $schoolid = null, 
        ?int $eventid = null
    ): self {
        $conditions = [];
        $params = [];
        $command = 'DELETE FROM images ';

        if (!is_null($id)) {
            $conditions[] = 'images.id = :id';
            $params[':id'] = [$id, \PDO::PARAM_INT];
        }

        if (!is_null($schoolid)) {
            $conditions[] = 'images.ecoleid = :ecoleid';
            $params[':ecoleid'] = [$schoolid, \PDO::PARAM_INT];
        }

        if (!is_null($eventid)) {
            $conditions[] = 'images.evenementid = :eventid';
            $params[':eventid'] = [$eventid, \PDO::PARAM_INT];
        }

        if (count($conditions) > 0) {
            $command .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $this->prepare($command);
        foreach ($params as $key => [$value, $type]) {
            $this->bindParam($key, $value, $type);
        }
        $this->executeDelete();

        return $this;
    }
    public function retrieveByName(?string $filename = null, ?int $schoolid = null): array
    {
        $images = [];
        $command  = 'SELECT i.* FROM images i ';
        $command .= 'INNER JOIN ecoles e on i.ecoleid = e.id ';
        $parseFilename = "%" . sprintf("img_%s_%s", $schoolid, $filename) . "%";

        if (!is_null($filename) && !is_null($schoolid)) {
            $command .= 'WHERE `filename` LIKE :filename  ';
            $command .= ' AND i.ecoleid = :ecoleid';
            $this->prepare($command)
                ->bindParam(':filename', $parseFilename, \PDO::PARAM_STR)
                ->bindParam(':ecoleid',  $schoolid, \PDO::PARAM_INT);
        }

        if (!is_null($filename) && is_null($schoolid)) {
            $command .= 'WHERE `filename` LIKE :filename   ORDER BY i.id desc';
            $this->prepare($command)
                ->bindParam(':filename', $parseFilename, \PDO::PARAM_STR);
        }

        if (!is_null($schoolid) && is_null($filename)) {
            $command .= 'WHERE i.ecoleid = :ecoleid ORDER BY i.id desc';
            $this->prepare($command)
                ->bindParam(':ecoleid', $schoolid, \PDO::PARAM_INT);
        }
        $imageRows = $this->executeQuery()
            ->getResults();
        if (is_array($imageRows) && count($imageRows) > 0) {
            foreach ($imageRows as $imageRow) {
                $image = Image::fromState($imageRow);
                $images[] = $image->toArray();
            }
        }
        return $images;
    }

    public function imageExists(string $filename, int $schoolid): bool
    {
        $this->retrieveByName(filename: $filename, schoolid: $schoolid);
        return $this->rowCount() > 0;
    }
    public function updateSchool(School $school): self
    {
        $repository = new SchoolRepository($this->connexion());
        $repository->update($school);
        $rowCount = (int) $repository->rowCount();
        $this->setTempRowCounted($rowCount);
        return $this;
    }
    public function updateEvent(Event $event): self
    {
        $titre = $event->getTitre();
        $description = $event->getDescription();
        $date = $event->getDate();
        $lieu = $event->getLieu();
        $id = $event->getId();
        $maximage = $event->getMaximage();
        $command  = 'UPDATE evenements SET titre = :titre, date = :date, ';
        $command .= 'description = :description, lieu = :lieu, maximage = :maximage ';
        $command .= 'WHERE id = :id';

        $this->prepare($command)
            ->bindParam(':titre', $titre, \PDO::PARAM_STR)
            ->bindParam(':date', $date, \PDO::PARAM_STR)
            ->bindParam(':description', $description, \PDO::PARAM_STR)
            ->bindParam(':lieu', $lieu, \PDO::PARAM_STR)
            ->bindParam(':maximage', $maximage, \PDO::PARAM_INT | \PDO::PARAM_NULL)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->executeUpdate();

        return $this;
    }
}
