<?php

namespace App\Repository;

use App\Entity\Informations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Informations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Informations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Informations[]    findAll()
 * @method Informations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InformationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Informations::class);

        $this->em = $em;
        $this->conn = $this->em->getConnection();
    }

    public function getOneData($id)
    {

        $sql = "
                SELECT *
                FROM informations 
                WHERE id = :id
               ";

        $users =$this->conn->prepare($sql);
        $users->execute(array(":id" => $id));

        $arrayUser = $users->fetchAll();
        return $arrayUser;

    }

  
}
