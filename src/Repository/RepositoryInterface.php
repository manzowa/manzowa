<?php 

namespace App\Repository;

interface RepositoryInterface {
    public function find(int $id);
    public function save(Object $object);
}