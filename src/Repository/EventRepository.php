<?php

namespace App\Repository;

use App\Model\Address;
use App\Model\Event;
use App\Model\Image;
use App\Model\School;

class EventRepository extends Repository implements \Countable
{
    public function retrieve(
        ?int $id = null,
        ?int $schoolid = null,
        int $limit = 0
    ): array {
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
        ?int $ecoleid = null,
        ?string $nomEcole = null,
        ?string $titre = null,
        ?string $lieu = null,
        ?string $dateTime = null,
        ?string $ville = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $events = [];
        $conditions = [];
        $params = [];

        // Limit par défaut
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;
        // Sélection des champs spécifiques avec alias
        $command = <<<SQL
        SELECT 
            ev.id AS evenement_id,
            ev.titre, ev.description,
            ev.date, ev.lieu,
            ev.maximage AS evenement_maximage,
            e.id AS ecole_id,
            e.nom, e.email,
            e.telephone, e.type, e.site,
            e.maximage AS ecole_maximage,
            a.id AS adresse_id,
            a.voie, a.quartier,
            a.commune, a.district,
            a.ville, a.reference,
            i.id AS image_id,
            i.title, i.filename, i.mimetype,
            i.type AS image_type
        FROM evenements ev
        INNER JOIN ecoles e ON ev.ecoleid = e.id
        INNER JOIN adresses a ON e.id = a.ecoleid
        LEFT JOIN images i ON i.evenementid = ev.id
        SQL;

        // Conditions dynamiques
        if (!is_null($id)) {
            $conditions[] = 'ev.id = :eventid';
            $params[':eventid'] = [$id, \PDO::PARAM_INT];
        }
        if (!is_null($ecoleid)) {
            $conditions[] = 'ev.ecoleid = :ecoleid';
            $params[':ecoleid'] = [$ecoleid, \PDO::PARAM_INT];
        }
        if (!is_null($titre)) {
            $conditions[] = 'ev.titre LIKE :titre';
            $params[':titre'] = ['%' . $titre . '%', \PDO::PARAM_STR];
        }
        if (!is_null($lieu)) {
            $conditions[] = 'ev.lieu LIKE :lieu';
            $params[':lieu'] = ['%' . $lieu . '%', \PDO::PARAM_STR];
        }

        if (!is_null($dateTime)) {
            $conditions[] = 'ev.date >= :date';
            $params[':date'] = [$dateTime, \PDO::PARAM_STR];
        }

        if (!is_null($nomEcole)) {
            $conditions[] = 'e.nom LIKE :nomEcole';
            $params[':nomEcole'] = ['%' . $nomEcole . '%', \PDO::PARAM_STR];
        }

        if (!is_null($ville)) {
            $conditions[] = 'a.ville LIKE :ville';
            $params[':ville'] = ['%' . $ville . '%', \PDO::PARAM_STR];
        }

        // Ajout de la clause WHERE si nécessaire
        if (!empty($conditions)) {
            $command .= ' WHERE ' . implode(' AND ', $conditions);
        }

        // Tri + LIMIT et OFFSET
        $command .= ' ORDER BY ev.id DESC LIMIT :limit';
        $params[':limit'] = [$limit, \PDO::PARAM_INT];

        if (!is_null($offset) && $offset >= 0) {
            $command .= ' OFFSET :offset';
            $params[':offset'] = [$offset, \PDO::PARAM_INT];
        }

        // Préparation de la requête
        $this->prepare($command);

        // Liaison des paramètres
        foreach ($params as $param => [$value, $type]) {
            $this->bindParam($param, $value, $type);
        }
        // Exécution de la requête
        $rows = $this->executeQuery()->getResults();
        // Construction du résultat final
        foreach ($rows as $row) 
        {
            $address = new Address(
                id: $row['adresse_id'],
                voie: $row['voie'],
                quartier: $row['quartier'],
                commune: $row['commune'],
                district: $row['district'],
                ville: $row['ville'],
                reference: $row['reference']
            );

            $school = new School(
                id: $row['ecole_id'],
                nom: $row['nom'],
                email: $row['email'],
                telephone: $row['telephone'],
                type: $row['type'],
                site: $row['site'],
                maximage: $row['ecole_maximage'],
                adresses: $address->toArray(),
                horaires: [],
                evenements: []
            );

            $image = new Image(
                id: $row['image_id'],
                title: $row['title'],
                filename: $row['filename'],
                mimetype: $row['mimetype'],
                type: $row['image_type'],
                ecoleid: $row['ecole_id'],
                evenementid: $row['evenement_id'],
            );

            $events[] = [
                'id' => $row['evenement_id'],
                'titre' => $row['titre'],
                'description' => $row['description'],
                'date' => $row['date'],
                'lieu' => $row['lieu'],
                'school' => $school->toArray(),
                'images' => [$image->toArray()],
            ];
        }

        return $events;
    }


    private function _getEvent(array $row)
    {
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

            if (is_array($rows) && count($rows) > 0) {
                foreach ($rows as $row) {
                    $image = Image::fromState($row);
                    $images[] = $image->toArray();
                }
            }
        }

        return $images;
    }
}
