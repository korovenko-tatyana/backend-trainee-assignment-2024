<?php

namespace App\Repository;

use App\Entity\SearchBanner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SearchBanner>
 *
 * @method SearchBanner|null find($id, $lockMode = null, $lockVersion = null)
 * @method SearchBanner|null findOneBy(array $criteria, array $orderBy = null)
 * @method SearchBanner[]    findAll()
 * @method SearchBanner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchBannerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchBanner::class);
    }

    public function delete(int $featureId, array $tagIds)
    {
        return $this->createQueryBuilder('sb')
          ->delete()
          ->where('sb.feature_id = :feature')
          ->setParameter('feature', $featureId)
          ->andWhere('sb.tag_id IN (:tags)')
          ->setParameter('tags', $tagIds)
          ->getQuery()
          ->execute();
    }
}
