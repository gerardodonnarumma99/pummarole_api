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

        $sql = 'SELECT t.id,t.user_id,t.start_date,t.end_date,t.status,t.timer_type,type.type,t.title,t.description,type.duration,t.first_cycle
        FROM timers as t,timer_type as type
        WHERE t.timer_type=type.id AND user_id=:id 
        ORDER BY t.id 
        DESC LIMIT 1;
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getTomatosFromUserId(int $id): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT t.id,t.user_id,t.start_date,t.end_date,t.status,t.timer_type,type.type,t.title,t.description,type.duration,t.first_cycle
        FROM timers as t,timer_type as type
        WHERE t.timer_type=type.id AND user_id=:id AND type.type="tomato"
        ORDER BY t.id 
        DESC LIMIT 1;
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getTomatosCycle(int $idUser): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT type.type,type.duration,t.status
                FROM timers as t,timer_type as type
                WHERE t.timer_type=type.id AND user_id=:idUser AND t.status!='broken' AND t.id >= (SELECT id FROM timers WHERE first_cycle='yes' ORDER BY id DESC LIMIT 1)
                ORDER BY t.id
                ASC LIMIT 6";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['idUser' => $idUser]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getCycle(int $idUser): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT type.type,type.duration,t.status
                FROM timers as t,timer_type as type
                WHERE t.timer_type=type.id AND user_id=:idUser AND t.status='done' AND t.id >= (SELECT id FROM timers WHERE first_cycle='yes' ORDER BY id DESC LIMIT 1)
                ORDER BY t.id
                ASC LIMIT 6";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['idUser' => $idUser]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getLastEvent(int $idUser): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT t.start_date,type.duration,t.status,t.title,t.description
                FROM timers as t,timer_type as type
                WHERE t.timer_type=type.id AND user_id=:idUser AND t.status!='doing'
                ORDER BY t.id
                DESC LIMIT 4";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['idUser' => $idUser]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    /**
     * @param int $idUser
     * @return true is the first of the day, else false
     */
    public function getTimerFirstDay(int $idUser) {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT t.start_date
        FROM timers as t
        WHERE user_id=:id AND DATE_FORMAT(start_date,\'%Y %M $d\')=DATE_FORMAT(CURDATE(),\'%Y %M $d\')
        GROUP BY t.start_date
        HAVING COUNT(t.start_date)>=1';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $idUser]);

        if(!$stmt->fetchAll()) {
            echo 'ok';
            return true;
        }

        return false;
    }
}
