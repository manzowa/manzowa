<?php

namespace App\Repository;

use App\Model\Address;
use App\Model\School;

class AddressRepository extends Repository implements \Countable 
{
    public function retrieve(?int $id = null, ?int $schoolid =null, int $limit=0): array
    {
        $addresses = [];
        $conditions = [];
        $params = [];
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;
  
        $command  = 'SELECT a.* FROM adresses a ';
        $command .= 'INNER JOIN ecoles e on a.ecoleid = e.id ';

        if (!is_null($id)) {
            $conditions[] = 'a.id = :id';
            $params[':id'] = [$id, \PDO::PARAM_INT];
        }
        if (!is_null($schoolid)) {
            $conditions[] = 'a.ecoleid = :ecoleid';
            $params[':ecoleid'] = [$schoolid, \PDO::PARAM_INT];
        }

        // Append WHERE clause if any condition exists
        if (count($conditions) > 0) {
            $command .= 'WHERE ' . implode(' AND ', $conditions);
        }
        $command .= ' ORDER BY a.id desc ';

        if (!is_null($limit) && is_null($id)) {
            $command .= ' LIMIT :limit ';
            $params[':limit'] = [$limit, \PDO::PARAM_INT];
        }
        $this->prepare($command);
        // Bind parameters
        foreach ($params as $param => [$value, $type]) {
            $this->bindParam($param, $value, $type);
        }
        $addressRows = $this->executeQuery()
            ->getResults();
        if (count($addressRows) > 0) {
            foreach ($addressRows as $addressRow) {
                $address = Address::fromState($addressRow);
                $addresses[] = $address->toArray();
            }
        }

        return $addresses;
    }
    public function add(Address $address): self 
    {       
        $voie = $address->getVoie();
        $quartier = $address->getQuartier();
        $reference = $address->getReference();
        $commune = $address->getCommune();
        $district = $address->getDistrict();
        $ville = $address->getVille();
        $ecoleid = $address->getEcoleid();

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
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->executeInsert();
        return $this;
    }
    public function update(Address $address): self
    {
        $voie = $address->getVoie();
        $quartier = $address->getQuartier();
        $reference = $address->getReference();
        $commune = $address->getCommune();
        $district = $address->getDistrict();
        $ville = $address->getVille();
        $ecoleid = $address->getEcoleid();
        $id = $address->getId();

        $command  = 'UPDATE adresses SET voie= :voie, quartier = :quartier, ';
        $command .= 'reference = :reference, commune = :commune, district = :district, ';
        $command .= 'ville = :ville WHERE id = :id AND ecoleid = :ecoleid';
          
        $this->prepare($command)
            ->bindParam(':voie', $voie, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':quartier', $quartier, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':reference', $reference, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':commune', $commune, \PDO::PARAM_STR)
            ->bindParam(':district', $district, \PDO::PARAM_STR)
            ->bindParam(':ville', $ville, \PDO::PARAM_STR)
            ->bindParam(':id', $id, \PDO::PARAM_INT)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->executeUpdate();

        return $this;
    }
    public function remove(int $id, ?int $ecoleid = null): self 
    {
        $command = 'DELETE FROM adresses WHERE id = :id ';
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
        $command = 'SELECT count(id) as totalCount FROM adresses';
        $data = $this->prepare($command)
            ->executeQuery()
            ->getResults();
        $result = current($data);
        
        return intval($result['totalCount']);
    }
    public function exists(Address $address): bool
    {
        $voie = $address->getVoie();
        $quartier = $address->getQuartier();
        $commune = $address->getCommune();
        $district = $address->getDistrict();
        $ville = $address->getVille();
        $ecoleid = $address->getEcoleid();

        $command  = 'SELECT 1 FROM adresses a ';
        $command .= 'WHERE a.voie = :voie ';
        $command .= 'AND a.quartier = :quartier OR a.quartier IS NULL ';
        $command .= 'AND a.commune = :commune AND a.district = :district ';
        $command .= 'AND a.ville = :ville ';
        $command .= 'AND a.ecoleid = :ecoleid ';


        $rows = $this
            ->prepare($command)
            ->bindParam(':voie', $voie, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':quartier', $quartier, \PDO::PARAM_STR|\PDO::PARAM_NULL)
            ->bindParam(':commune', $commune, \PDO::PARAM_STR)
            ->bindParam(':district', $district, \PDO::PARAM_STR)
            ->bindParam(':ville', $ville, \PDO::PARAM_STR)
            ->bindParam(':ecoleid', $ecoleid, \PDO::PARAM_INT)
            ->executeQuery()
            ->getResults();

        $result = (count($rows)> 0) ? true: false;

       return $result;
    }
}
