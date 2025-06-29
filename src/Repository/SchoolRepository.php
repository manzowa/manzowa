<?php

namespace App\Repository;

use App\Model\School;
use App\Model\Address;
use App\Model\Image;

class SchoolRepository extends Repository implements \Countable 
{
    public function retrieve(?int $id = null, int $limit=0): array
    {
        $schools = [];
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;
        $command = 'SELECT * FROM `ecoles` e ';
        if (!is_null($id)) {
            $command .="WHERE e.id = :id ORDER BY id desc LIMIT $limit";
            $rows = $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT)
                ->executeQuery()
                ->getResults();
            $this->setTempRowCounted((int) $this->rowCount());
            if (is_array($rows) & count($rows) > 0) {
                $row = current($rows);
                $school = $this->_getSchool(row: $row);
                $schools[] = $school->toArray();
            }
        } else {
            $command .="ORDER BY id desc LIMIT $limit";
            $rows = $this->prepare($command)
                ->executeQuery()
                ->getResults();
            $this->setTempRowCounted((int) $this->rowCount());
            if (is_array($rows) & count($rows) > 0) {
                foreach ($rows as $row) {
                    $school = $this->_getSchool(row: $row);
                    $schools[] = $school->toArray();
                }
            }
        }
        return $schools;
    }
    public function searchByName(?string $nom = null, int $limit=0): array 
    {
        $schools = [];
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;
        $command  = 'SELECT * FROM `ecoles` WHERE MATCH(nom) AGAINST(:nom IN BOOLEAN MODE) ';
        $command .= 'ORDER BY nom desc LIMIT :limit';
        if (!is_null($nom)) {
            $parseNom = sprintf('%s'.$nom."%s", "+", "*");
            $rows = $this->prepare($command)
                ->bindParam(':nom', $parseNom, \PDO::PARAM_STR)
                ->bindParam(':limit', $limit, \PDO::PARAM_INT)
                ->executeQuery()
                ->getResults();
            $this->setTempRowCounted((int) $this->rowCount());
            if (is_array($rows) & count($rows) > 0) {
                foreach ($rows as $row) {
                    $school = $this->_getSchool(row: $row);
                    $schools[] = $school->toArray();
                }
            }
        }
        return $schools;
    }
    public function retrieveByName(?string $nom = null): array 
    {
        $schools = [];
        $command  = 'SELECT * FROM `ecoles` WHERE nom = :nom LIMIT 1';
        if (!is_null($nom)) {
            $rows = $this->prepare($command)
                ->bindParam(':nom', $nom, \PDO::PARAM_STR)
                ->executeQuery()
                ->getResults();
            $this->setTempRowCounted((int) $this->rowCount());
            if (is_array($rows) & count($rows) > 0) {
                foreach ($rows as $row) {
                    $school = $this->_getSchool(row: $row);
                    $schools[] = $school->toArray();
                }
            }
        }
        return $schools;
    }
    public function add(School $school): self 
    {
        $nom = $school->getNom();
        $email = $school->getEmail();
        $telephone = $school->getTelephone();
        $type=$school->getType();
        $site =$school->getSite();
        $maximage =$school->getMaximage();
        $command  = 'INSERT INTO ecoles (nom, email, telephone,type, site, maximage)  ';
        $command .= 'VALUES (:nom, :email, :telephone, :type, :site, :maximage) ';
        $this
            ->prepare($command)
            ->bindParam(':nom', $nom, \PDO::PARAM_STR)
            ->bindParam(':email', $email, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':telephone', $telephone, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':type', $type, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':site', $site, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':maximage', $maximage, \PDO::PARAM_INT|\PDO::PARAM_NULL)
            ->executInsert();
        $school_id = (int) $this->lastInsertId();
        $this->setTempRowCounted((int) $this->rowCount())
            ->setStockId(stockId: $school_id);
       
        $rows = $school->getAdresses();
        if (count($rows) >0 && is_numeric($school_id)) {
            for ($index=0; $index < count($rows); $index++) { 
                $row = $rows[$index];
                $voie = $row['voie'];
                $quartier =  $row['quartier'];
                $commune = $row['commune'];
                $district = $row['district'];
                $ville = $row['ville'];
                $reference =$row['reference'];

                $command  = 'INSERT INTO adresses ';
                $command .= '(voie, quartier, reference, commune, district, ville, ecoleid)';
                $command .= 'VALUES (:voie, :quartier, :reference, :commune, ';
                $command .=' :district, :ville, :ecoleid)';
                $this
                    ->prepare($command)
                    ->bindParam(':voie', $voie, \PDO::PARAM_STR|\PDO::PARAM_NULL)
                    ->bindParam(':quartier', $quartier, \PDO::PARAM_STR|\PDO::PARAM_NULL)
                    ->bindParam(':reference', $reference, \PDO::PARAM_STR|\PDO::PARAM_NULL)
                    ->bindParam(':commune', $commune, \PDO::PARAM_STR)
                    ->bindParam(':district', $district, \PDO::PARAM_STR)
                    ->bindParam(':ville', $ville, \PDO::PARAM_STR)
                    ->bindParam(':ecoleid', $school_id, \PDO::PARAM_INT)
                    ->executInsert();
            }
        }
        return $this;
    }
    public function update(School $school): self{
        $nom =  $school->getNom();
        $email =  $school->getEmail();
        $telephone =  $school->getTelephone();
        $type=  $school->getType();
        $site =  $school->getSite();
        $maximage =  $school->getMaximage();
        $id =  $school->getId();

        $command  = 'UPDATE ecoles SET nom= :nom, email= :email, telephone= :telephone, ';
        $command .= 'type= :type, site= :site, maximage = :maximage ';
        $command .= 'WHERE id= :id ';
    
        $this->prepare($command)
            ->bindParam(':nom', $nom, \PDO::PARAM_STR)
            ->bindParam(':email', $email, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':telephone', $telephone, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':type', $type, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':site', $site, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':maximage', $maximage, \PDO::PARAM_INT|\PDO::PARAM_NULL)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->executeUpdate();
        return $this;
    }
    public function remove(int $id): self {
        $this
            ->prepare( 'DELETE FROM ecoles WHERE id = :id ')
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->executeDelete();
        return $this;
    }
    public function schoolExists(string $nom): bool {
        $this->retrieveByName($nom);
        return $this->getTempRowCounted() > 0;
    }
    public function schoolNotFound(int $id): bool {
        $this->retrieve(id: $id);
        return $this->getTempRowCounted() == 0;
    }

    private function _retrieveAddresses(?int $school_id = null): array 
    {
        $addresses = [];
        if (!is_null($school_id)) {
            $command  = 'SELECT adresses.* FROM adresses ';
            $command .= 'WHERE adresses.ecoleid = :ecoleid ';
            $rows = $this->prepare($command)
                ->bindParam(':ecoleid', $school_id, \PDO::PARAM_INT)
                ->executeQuery()
                ->getResults();
            if (is_array($rows) && count($rows)> 0) {
                foreach ($rows as $row) {
                    $address = Address::fromState($row);
                    $addresses[] = $address->toArray();
                }
            }

        }

        return $addresses;
    }
    private function _retrieveImages(?int $school_id = null): array
    {
        $images = [];
        if (!is_null($school_id)) {
            $command  = 'SELECT images.* FROM images ';
            $command .= 'WHERE images.ecoleid = :ecoleid ';
            $rows = $this->prepare($command)
                ->bindParam(':ecoleid', $school_id, \PDO::PARAM_INT)
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
    private function _getSchool(array $row) {
        return new School(
            id: $row['id'],
            nom: $row['nom'],
            email: $row['email'],
            telephone: $row['telephone'],
            type: $row['type'],
            site: $row['site'],
            maximage: $row['maximage'],
            images: $this->_retrieveImages(school_id: $row['id']),
            adresses: $this->_retrieveAddresses(school_id: $row['id'])
        );

    }
    public function count(): int
    {
        $command = 'SELECT count(id) as totalCount FROM ecoles';
        $data = $this->prepare($command)
            ->executeQuery()
            ->getResults();
        $result = current($data);
        
        return intval($result['totalCount']);
    }
    public function retrieveByLimitAndOffset(int $limit, int $offset): array
    {
        $schools = [];
        $command= 'SELECT ecoles.* FROM ecoles ORDER BY id desc LIMIT :limit  OFFSET :offset ';
        $rows = $this->prepare($command)
            ->bindParam(':limit', $limit, \PDO::PARAM_INT)
            ->bindParam(':offset', $offset, \PDO::PARAM_INT)
            ->executeQuery()
            ->getResults();
        $this->setTempRowCounted((int) $this->rowCount());
        if (is_array($rows) & count($rows) > 0) {
            foreach ($rows as $row) {
                $school = $this->_getSchool(row: $row);
                $schools[] = $school->toArray();
            }
        }
        return $schools;
    }
}
