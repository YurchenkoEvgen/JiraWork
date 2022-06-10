<?php

namespace App\DTO\Traits;

trait EntityReposytory {
    public function merge($entity, bool $flush = false): void
    {
        $this->getEntityManager()->merge($entity);

        if ($flush) {
            $this->flush();
        }
    }

    public function flush():void
    {
        $this->getEntityManager()->flush();
    }
}
