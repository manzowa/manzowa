<?php

namespace App\Repository;

use App\Model\Event;
use App\Model\Image;

class EventRepository extends Repository implements \Countable 
{
    public function retrieve(
        ?int $id = null, 
        ?int $schoolid =null, 
        int $limit=0): array
    {
        $events = [];
        $conditions = [];
        $params = [];
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;

        $command  = 'SELECT ev.* FROM evenements ev ';
        $command .= 'INNER JOIN ecoles e ON ev.ecoleid = e.id ';

        if (!is_null($id)) {
            $conditions[] = 'ev.id = :eventid ';
            $params[':eventid'] = [$id, \PDO::PARAM_INT];
        }
        if (!is_null($schoolid)) {
            $conditions[] = 'ev.ecoleid = :ecoleid ';
            $params[':ecoleid'] = [$schoolid, \PDO::PARAM_INT];
        }

        // Append WHERE clause if any condition exists
        if (count($conditions) > 0) {
            $command .= 'WHERE ' . implode(' AND ', $conditions);
        }
        $command .= ' ORDER BY ev.id desc LIMIT :limit ';
        $params[':limit'] = [$limit, \PDO::PARAM_INT];
        $this->prepare($command);
        foreach ($params as $param => [$value, $type]) {
            $this->bindParam($param, $value, $type);
        }
        $eventRows = $this->executeQuery()->getResults();
        $this->setTempRowCounted((int) $this->rowCount());

        if (count($eventRows) > 0) {
            foreach ($eventRows as $eventRow) {
                $event = $this->_getEvent(row: $eventRow);
                $events[] = $event->toArray();
            }
        }
        return $events;
    }
    public function add(Event $event): self
    {
        $titre = $event->getTitre();
        $description = $event->getDescription();
        $date = $event->getDate();
        $lieu = $event->getLieu();
        $ecoleid = $event->getEcoleid();
        $maximage = $event->getMaximage();

        $command  = 'INSERT INTO evenements ';
        $command .= '(titre, description, date, lieu, ecoleid, maximage)';
        $command .= 'VALUES (:titre, :description, :date, :lieu, :ecoleid, :maximage)';

        $this
            ->prepare($command)
            ->bindParam(':titre',  $titre, \PDO::PARAM_STR)
            ->bindParam(':description', $description, \PDO::PARAM_STR)
            ->bindParam(':date', $date, \PDO::PARAM_STR)
            ->bindParam(':lieu', $lieu, \PDO::PARAM_STR)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->bindParam(':maximage', $maximage, \PDO::PARAM_INT | \PDO::PARAM_NULL)
            ->executeInsert();

        return $this;
    }
    public function update(Event $event): self
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
    public function remove(int $id, ?int $ecoleid = null): self 
    {
        $command = 'DELETE FROM evenements WHERE id = :id ';
        if (!is_null($id) && !is_null($ecoleid)) {
            $command .= ' AND ecoleid = :ecoleid';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->bindParam(':ecoleid',  $ecoleid, \PDO::PARAM_INT)
                ->executeDelete();
        } else {
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->executeDelete();
        }
        return $this;
    }
    public function count(): int
    {
        $command = 'SELECT count(id) as totalCount FROM evenements';
        $data = $this->prepare($command)
            ->executeQuery()
            ->getResults();
        $result = current($data);
        
        return intval($result['totalCount']);
    }

    public function retrieveAll(
        ?int $id = null, 
        ?int $ecoleid =null, 
        ?string $titre=null,
        ?string $lieu=null,
        ?string $nomEcole=null,
        ?int $limit=null,
        ?int $offset=null
    ): array
    {
        $events = [];
        $conditions = [];
        $params = [];
      
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;

        $command  = 'SELECT ev.* FROM evenements ev ';
        $command .= 'INNER JOIN ecoles e on ev.ecoleid = e.id ';
       
        if (!is_null($id)) {
            $conditions[] = 'ev.id = :eventid ';
            $params[':eventid'] = [$id, \PDO::PARAM_INT];
        } 
        if (!is_null($ecoleid)) {
            $conditions[] = 'ev.ecoleid = :ecoleid ';
            $params[':ecoleid'] = [$ecoleid, \PDO::PARAM_INT];
        } 
        if (!is_null($titre)) {
            $likeTitre = "%$titre%";
            $conditions[] = 'ev.titre LIKE :titre ';
            $params[':titre'] = [$likeTitre, \PDO::PARAM_STR];
        } 
        if (!is_null($lieu)) {
            $likeLieu = "%$lieu%";
            $conditions[] = 'ev.lieu LIKE :lieu ';
            $params[':lieu'] = [$likeLieu, \PDO::PARAM_STR];
        } 
        if (!is_null($nomEcole)) {
            $likeNomEcole = "%$nomEcole%";
            $conditions[] = 'e.nom LIKE :nomEcole ';
            $params[':nomEcole'] = [$likeNomEcole, \PDO::PARAM_STR];
        } 
        // Append WHERE clause if any condition exists
        if (count($conditions) > 0) {
            $command .= 'WHERE ' . implode(' AND ', $conditions);
        }
        // Finalize query
        $command .= ' ORDER BY ev.id desc LIMIT :limit ';
        $params[':limit'] = [$limit, \PDO::PARAM_INT];
        if (!is_null($offset) && $offset >= 0) {
            $command .= ' OFFSET :offset ';
            $params[':offset'] = [$offset, \PDO::PARAM_INT];
        }
        
        $this->prepare($command);

        foreach ($params as $param => [$value, $type]) {
            $this->bindParam($param, $value, $type);
        }
        $eventRows = $this->executeQuery()->getResults();

        if (count($eventRows) > 0) {
            foreach ($eventRows as $eventRow) {
                $event = Event::fromState($eventRow);
                $events[] = $event->toArray();
            }
        }
        return $events;
    }

    private function _getEvent(array $row) {
        return new Event(
            id: $row['id'],
            titre: $row['titre'],
            description: $row['description'],
            date: $row['date'],
            lieu: $row['lieu'],
            ecoleid: $row['ecoleid'],
            maximage: $row['maximage'],
            images: $this->_retrieveImages($row['id'])
        );
    }

    private function _retrieveImages(?int $eventid = null): array
    {
        $images = [];
        if (!is_null($eventid)) {
            $command  = 'SELECT images.* FROM images ';
            $command .= 'WHERE images.evenementid = :eventid';
            $rows = $this->prepare($command)
            ->bindParam(':eventid', $eventid, \PDO::PARAM_INT)
            ->executeQuery()
            ->getResults();

            if (is_array($rows) && count($rows)> 0) {
                foreach ($rows as $row) {
                    $image = Image::fromState($row);
                    $images[] = $image->toArray();
                }
            }
        }

        return $images;
    }
}
