<?php

namespace App\Repository;

use App\DTO\Traits\EntityReposytory;
use App\Entity\IssueField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IssueField>
 *
 * @method IssueField|null find($id, $lockMode = null, $lockVersion = null)
 * @method IssueField|null findOneBy(array $criteria, array $orderBy = null)
 * @method IssueField[]    findAll()
 * @method IssueField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IssueFieldRepository extends ServiceEntityRepository
{
    use EntityReposytory;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssueField::class);
    }

    public function add(IssueField $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(IssueField $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return IssueField[] Returns an array of IssueField objects
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

//    public function findOneBySomeField($value): ?IssueField
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
