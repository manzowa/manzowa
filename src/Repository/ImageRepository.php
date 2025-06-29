<?php

namespace App\Repository;

use App\Model\Image;
use App\Model\School;
use App\Repository\SchoolRepository;

class ImageRepository extends Repository implements \Countable 
{
    public function retrieve(
        ?int $id = null, 
        ?int $schoolid =null, 
        int $limit=0
    ): array {
        $images = [];
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;

        $command  = 'SELECT i.* FROM images i ';
        $command .= 'INNER JOIN ecoles e on i.ecoleid = e.id ';

        if (!is_null($id) && !is_null($schoolid)) {
            $command .= 'WHERE i.ecoleid = :ecoleid ';
            $command .= 'AND i.id = :imageid ORDER BY i.id desc LIMIT :limit';
            $this->prepare($command)
            ->bindParam(':imageid', $id, \PDO::PARAM_INT)
            ->bindParam(':ecoleid', $schoolid, \PDO::PARAM_INT)
            ->bindParam(':limit', $limit, \PDO::PARAM_INT);

        } elseif(!is_null($id) && is_null($schoolid)) {
            $command .= 'WHERE i.id = :id  ORDER BY i.id desc LIMIT :limit';
            $this->prepare($command)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->bindParam(':limit', $limit, \PDO::PARAM_INT);
        }elseif(!is_null($schoolid) && is_null($id)) {
            $command .= 'WHERE i.ecoleid = :ecoleid ORDER BY i.id desc LIMIT :limit ';
            $this->prepare($command)
            ->bindParam(':ecoleid', $schoolid, \PDO::PARAM_INT)
            ->bindParam(':limit', $limit, \PDO::PARAM_INT);
        }

        $imageRows = $this->executeQuery()
            ->getResults();
        if (count($imageRows) > 0) {
            foreach ($imageRows as $imageRow) {
                $image = Image::fromState($imageRow);
                $images[] = $image->toArray();
            }
        }


        return $images;
    }

    public function add(Image $image): self 
    {       
        $title = $image->getTitle();
        $filename = $image->getFilename();
        $mimetype = $image->getMimetype();
        $ecoleid= $image->getEcoleid();

        $command  = 'INSERT INTO images (title, filename, mimetype, ecoleid)  ';
        $command .= 'VALUES (:title, :filename, :mimetype, :ecoleid) ';

         $this
            ->prepare($command)
            ->bindParam(':title', $title, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':filename', $filename, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':mimetype', $mimetype, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->executInsert();
        return $this;
    }
    public function update(Image $image): self 
    {
        $title = $image->getTitle();
        $filename = $image->getFilename();
        $id = $image->getId();
    
        $command  = 'UPDATE images SET title= :title, filename= :filename ';
        $command .= 'WHERE id= :id ';
    
        $this
            ->prepare($command)
            ->bindParam(':title', $title, \PDO::PARAM_STR)
            ->bindParam(':filename', $filename, \PDO::PARAM_STR)
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
    public function remove(int $id, ?int $schoolid = null): self 
    {
        $command = 'DELETE FROM images WHERE id = :id ';
        if (!is_null($id) && !is_null($schoolid)) {
                $command .= ' AND ecoleid = :ecoleid';
                $this->prepare($command)
                    ->bindParam(':id', $id, \PDO::PARAM_INT)
                    ->bindParam(':ecoleid',  $schoolid, \PDO::PARAM_INT)
                    ->executeDelete();
        } else {
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->executeDelete();
        }
        return $this;
    }
    public function retrieveByName(?string $filename = null, ?int $schoolid = null): array 
    {
        $images = [];
        $command  = 'SELECT i.* FROM images i ';
        $command .= 'INNER JOIN ecoles e on i.ecoleid = e.id ';
        $parseFilename = "%".sprintf("img_%s_%s", $schoolid, $filename)."%";

        if (!is_null($filename) && !is_null($schoolid)) {
            $command .= 'WHERE `filename` LIKE :filename  ';
            $command .= ' AND i.ecoleid = :ecoleid';
            $this->prepare($command)
                ->bindParam(':filename',$parseFilename, \PDO::PARAM_STR)
                ->bindParam(':ecoleid',  $schoolid, \PDO::PARAM_INT);
        } 
        
        if(!is_null($filename) && is_null($schoolid)) {
            $command .= 'WHERE `filename` LIKE :filename   ORDER BY i.id desc';
            $this->prepare($command)
            ->bindParam(':filename', $parseFilename, \PDO::PARAM_STR);
        }
        
        if(!is_null($schoolid) && is_null($filename)) {
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
}