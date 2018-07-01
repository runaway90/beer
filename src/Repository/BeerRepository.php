<?php

namespace App\Repository;

use App\Entity\Beer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Beer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Beer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Beer[]    findAll()
 * @method Beer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BeerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Beer::class);
    }

    /**
     * @return Beer[] Returns an array of Beer objects
     */

    public function findAllFilteredAndPaginated($findBy=array(), $limit, $offset)
    {
        $qb = $this->createQueryBuilder('b');

        foreach ($findBy as $field => $value) {
            if($field=="pricePerLitre"){
                $qb->andWhere('b.pricePerLitre BETWEEN :min AND :max')
                    ->setParameter('min', $value['min'])
                    ->setParameter('max', $value['max']);
            }
            elseif($field=="name"){
                $compareValue = $value;
                $compareValue = str_replace('*', '%', $compareValue);
                $compareValue = str_replace('?', '_', $compareValue);
                $qb->andWhere(sprintf('b.%s LIKE :%s', $field, $field))
                    ->setParameter($field, $compareValue);
            }
            else{
                $qb->andWhere(sprintf('b.%s = :%s', $field, $field))
                    ->setParameter($field, $value);
            }

        }

        $qb ->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $qb->getQuery();

        return $query->getResult();
    }


}
