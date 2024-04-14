<?php

namespace App\Repository;

use App\DTO\GetListDTO;
use App\Entity\Banner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Banner>
 *
 * @method Banner|null find($id, $lockMode = null, $lockVersion = null)
 * @method Banner|null findOneBy(array $criteria, array $orderBy = null)
 * @method Banner[]    findAll()
 * @method Banner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BannerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Banner::class);
    }

    public function search(GetListDTO $getListDTO): array
    {
        $qb = $this->createQueryBuilder('b')
        ->orderBy('b.id', 'ASC')
        ->setFirstResult($getListDTO->getOffset())
        ->setMaxResults($getListDTO->getLimit())
        ->innerJoin('App\Entity\SearchBanner', 'sb', Join::WITH, 'b.id = sb.banner');

        if ($getListDTO->getFeatureId()) {
            $qb->andWhere('sb.feature_id = :feature')
                ->setParameter('feature', $getListDTO->getFeatureId());
        }

        if ($getListDTO->getTagId()) {
            $qb->andWhere('sb.tag_id = :tag')
                ->setParameter('tag', $getListDTO->getTagId());
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function searchByFeature(int $feature): array
    {
        $qb = $this->createQueryBuilder('b')
        ->orderBy('b.id', 'ASC')
        ->innerJoin('App\Entity\SearchBanner', 'sb', Join::WITH, 'b.id = sb.banner')
        ->andWhere('sb.feature_id = :feature')
        ->setParameter('feature', $feature);

        return $qb->getQuery()
            ->execute();
    }

    public function delete(int $id)
    {
        return $this->createQueryBuilder('b')
          ->delete()
          ->where('b.id = :id')
          ->setParameter('id', $id)
          ->getQuery()
          ->execute();
    }
}
