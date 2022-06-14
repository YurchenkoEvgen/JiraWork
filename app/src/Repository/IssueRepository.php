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

    private function createTree($collection):array
    {
        $tree = [];
        foreach ($collection as $value) {
            if ($value->getIssueFiled()->getIsArray()) {
                $tree[$value->getIssueFiled()->getKey()][] = $value;
            } else {
                $tree[$value->getIssueFiled()->getKey()] = $value;
            }
        }
        return  $tree;
    }
    
    private function compare($val1,$val2):bool
    {
        return gettype($val1) == gettype($val2) &&
            ((is_object($val1) && method_exists($val1,'getId')) ?
                $val1->getId() == $val2->getId() :
                $val1 == $val2);
    }

    private function mergeFields(Issue $managedEntity, array $newValuesCollection):void
    {
        $_em = $this->getEntityManager();
        $fields = $managedEntity->getIssueFieldValues();
        $fields->initialize();

        $bdvalues = $this->createTree($fields);

        foreach ($this->createTree($newValuesCollection) as $key=>$item) {
            if (array_key_exists($key,$bdvalues)) {
                $value = $bdvalues[$key];
                if (is_array($item)) {
                    $iteams = [];
                    $values = [];
                    foreach ($item as $i) {
                        $iteams[] = $i->getValue();
                    }
                    foreach ($value as $i) {
                        $values[] = $i->getValue();
                    }
                    foreach (array_diff($iteams,$values) as $key=>$i) {//find added element
                        $_em->merge($item[$key]);
                    }
                    foreach (array_diff($values,$iteams) as $key=>$i) {//delete element
                        $_em->remove($value[$key]);
                    }
                } else {
                    if (!$this->compare($item->getValue(),$value->getValue())) {
                        $_em->merge($value->setValue($item->getValue()));
                    }
                }
            } else {//is new field
                if (is_array($item)) {
                    foreach ($item as $i) {
                        $_em->merge($i);
                    }
                } else {
                    $_em->merge($item);
                }
            }
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
