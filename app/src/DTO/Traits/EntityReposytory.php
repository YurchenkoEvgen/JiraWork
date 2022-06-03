<?php

namespace App\DTO\Traits;

trait EntityReposytory {
    public function merge($entity, bool $flush = false): void
    {
        if ($this->find($entity->getId())){
            $this->getEntityManager()->merge($entity);
        } else {
            $this->getEntityManager()->persist($entity);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(){
        $this->getEntityManager()->flush();
    }
}
