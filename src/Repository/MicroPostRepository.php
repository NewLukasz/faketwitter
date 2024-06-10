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
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MicroPost::class);
    }

    /**
     * @param bool $withComments
     * @param bool $withLikes
     * @param bool $withAuthors
     * @param bool $withProfiles
     * @return QueryBuilder
     */
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

    /**
     * @param int|User $author
     * @return array
     */
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

    /**
     * @param Collection|array $authors
     * @return array
     */
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

    /**
     * @return array
     */
    public function findAllWithComments(): array
    {
        return
            $this->findAllQuery(withComments: true)
                ->getQuery()
                ->getResult();
    }

    /**
     * @param int $minLikes
     * @return array
     */
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
}
