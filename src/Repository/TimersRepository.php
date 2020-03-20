<?php

namespace App\Repository;

use App\Entity\Timers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Timers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Timers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Timers[]    findAll()
 * @method Timers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Timers::class);
    }

    // /**
    //  * @return Timers[] Returns an array of Timers objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Timers
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param int $id
     * @return mixed
     */
    public function getTimersFromUserId(int $id): array
    {
       /* $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT t
            FROM App\Entity\Timers t
            WHERE t.user_id = :id
            ORDER BY id DESC '
        )->setParameter('id', $id);

        return $query->getResult(); */

        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT t.id,t.user_id,t.start_date,t.end_date,t.status,t.timer_type,type.type,t.title,t.description,type.duration
        FROM timers as t,timer_type as type
        WHERE t.timer_type=type.id AND user_id=1 
        ORDER BY id 
        DESC LIMIT 1;
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }
}
