<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;

/**
 * @extends ServiceEntityRepository<MicroPost>
 */
class MicroPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MicroPost::class);
    }

    private function findAllQuery(
        bool $withComments = false,
        bool $withLikes = false,
        bool $withAuthors = false,
        bool $withProfiles = false
    ): QueryBuilder
    {
        $query = $this->createQueryBuilder('m'); //m like micropost
        if ($withComments) {
            $query->leftJoin('m.comments', 'c')
                ->addSelect('c');
        }
        if ($withLikes) {
            $query->leftJoin('m.likedBy', 'l')
                ->addSelect('l');
        }
        if ($withAuthors || $withProfiles) {
            $query->leftJoin('m.author', 'a')
                ->addSelect('a');
        }
        if ($withProfiles) {
            $query->leftJoin('a.userProfile', 'up')
                ->addSelect('up');
        }
        return $query->orderBy('m.created', 'DESC');
    }

    public function findAllByAuthor(int|User $author): array
    {
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )
            ->where('m.author = :author')
            ->setParameter('author', $author instanceof User ? $author->getId() : $author)
            ->getQuery()
            ->getResult();
    }

    public function findAllByAuthors(Collection|array $authors): array
    {
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )
            ->where('m.author IN (:authors)')
            ->setParameter('authors', $authors)
            ->getQuery()
            ->getResult();
    }

    public function findAllWithComments(): array
    {
        return
            $this->findAllQuery(withComments: true)
                ->getQuery()
                ->getResult();
    }

    public function findAllWithMinLikes(int $minLikes): array
    {
        //just ids of posts
        $idList = $this->findAllQuery(
            withLikes: true
        )
            ->select('m.id')
            ->groupBy('m.id') //micropost
            ->having('COUNT(l) >= :minLikes') //likes
            ->setParameter('minLikes', $minLikes)
            ->getQuery()->getResult(Query::HYDRATE_SCALAR_COLUMN);

        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )
            ->where('m.id in (:idList)')
            ->setParameter(':idList', $idList)
            ->getQuery()->getResult();
    }

    //    /**
    //     * @return MicroPost[] Returns an array of MicroPost objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MicroPost
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
