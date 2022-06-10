<?php

namespace App\Repository;

use App\DTO\Traits\EntityReposytory;
use App\Entity\Issue;
use App\Entity\IssueFieldValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Issue>
 *
 * @method Issue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Issue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Issue[]    findAll()
 * @method Issue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IssueRepository extends ServiceEntityRepository
{
    use EntityReposytory;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Issue::class);
    }

    public function add(Issue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Issue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function merge(Issue $entity, bool $flush = false): Issue
    {
        $managedEntity = $this->getEntityManager()->merge($entity);
        $this->mergeFields(
            $managedEntity,
            $entity->getIssueFieldValues()->toArray()
        );

        if ($flush) {
            $this->flush();
        }

        return $managedEntity;
    }

    private function mergeFields(Issue $managedEntity, array $newValuesCollection):void
    {
        $_em = $this->getEntityManager();
        $fields = $managedEntity->getIssueFieldValues();
        $fields->initialize();
        foreach ($newValuesCollection as $item) {
            $_em->merge($item);
        }
    }


//    /**
//     * @return Issue[] Returns an array of Issue objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Issue
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
