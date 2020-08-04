<?php

namespace App\Repository;

use App\Entity\ParsingId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method ParsingId|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParsingId|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParsingId[]    findAll()
 * @method ParsingId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParsingIdRepository extends ServiceEntityRepository
{

    protected $em;


    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ParsingId::class);
        $this->em = $em;
        $this->conn = $this->em->getConnection();
    }


    public function findAllIdsWithoutInformations() {
        
   
        $sql = "
                SELECT urlid 
                FROM informations as i
                RIGHT JOIN parsing_id as p
                 ON i.relation_id = p.id
                 WHERE i.relation_id is NULL
                 AND p.is_active is NULL
               ";

        $users =$this->conn->prepare($sql);
        $users->execute();

        $arrayUsers = $users->fetchAll();
        return $arrayUsers;

    }


    public function getId($id)
    {

        $sql = "
                SELECT *
                FROM informations as i
                RIGHT JOIN parsing_id as p
                 ON i.relation_id = p.id
                 WHERE p.urlid = :id
               ";

        $users =$this->conn->prepare($sql);
        $users->execute(array(":id" => $id));

        $arrayUser = $users->fetch();
        return $arrayUser;

    }

    // public function findInformations() {
    //     $sql = "
    //             SELECT urlid 
    //             FROM informations as i
    //             RIGHT JOIN parsing_id as p
    //              ON i.relation_id = p.id
    //              WHERE i.relation_id is NULL
    //              AND p.is_active is NULL
    //            ";

    //     $users =$this->conn->prepare($sql);
    //     $users->execute();

    //     $arrayUsers = $users->fetchAll();
    //     return $arrayUsers;

    // }

    public function findAllIdsWithoutBinaryImage() {
    
        $sql = "
                SELECT urlid 
                FROM images as i
                RIGHT JOIN parsing_id as p
                 ON i.relation_id = p.id
                 WHERE i.relation_id is NULL
                 LIMIT 1
               ";

        $users = $this->conn->prepare($sql);
        $users->execute();

        $arrayUsers = $users->fetchAll();
        return $arrayUsers;

   }

   public function findAllIdsWithInformations()
   {
        $sql = "
            SELECT p.id
            FROM informations as i
            RIGHT JOIN parsing_id as p
             ON i.relation_id = p.id
             WHERE i.relation_id is not NULL
            
             
        ";


        $users = $this->conn->prepare($sql);
        $users->execute();
        $arrayUsers = $users->fetchAll();
        return $arrayUsers;

   }

   public function getAllIds()
   {
        $sql = "
                SELECT p.urlid, p.city, p.pseudo, p.age, p.is_active, p.avatar, p.lng, p.lat, p.sexe, i.name
                FROM images as i
                RIGHT JOIN parsing_id as p
                 ON i.relation_id = p.id

               ";

        $users = $this->conn->prepare($sql);
        $users->execute();

        $arrayUsers = $users->fetchAll();
        return $arrayUsers;
   }

   public function getLocateIds()
   {
        $sql = "
                SELECT p.urlid, p.city, p.pseudo, p.age, p.is_active, p.avatar, p.lng, p.lat, p.sexe, i.name
                FROM images as i
                RIGHT JOIN parsing_id as p
                 ON i.relation_id = p.id
                 WHERE p.lng is not null
                 AND p.lat is not null
                 LIMIT 1000

               ";

        $users = $this->conn->prepare($sql);
        $users->execute();

        $arrayUsers = $users->fetchAll();
        return $arrayUsers;
   }


 
   public function getScreenLocateIds($startlat, $startlng)
   {
        $lat = floatval($startlat);
        $lng = floatval($startlng);

        if (!is_float($lat) || !is_float($lng) || $lat == 0 || $lng == 0) 
            return null;
        
        $sql = "
        SELECT p.urlid, p.city, p.pseudo, p.age, p.is_active, p.avatar, p.lng, p.lat, p.sexe, i.name, SQRT(
            POW(69.1 * (p.lat - :startlat), 2) +
            POW(69.1 * (:startlng - p.lng) * COS(p.lat / 57.3), 2)) AS distance
            FROM images as i
            RIGHT JOIN parsing_id as p
            ON i.relation_id = p.id
            WHERE i.name is not null
            HAVING distance < 8 ORDER BY distance
            LIMIT 0, 100
        ";

        $query = $this->conn->prepare($sql);
        $query->execute(array('startlat' => $startlat, 'startlng' => $startlng));

        return $query->fetchAll();
   }


    // /**
    //  * @return ParsingId[] Returns an array of ParsingId objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ParsingId
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
