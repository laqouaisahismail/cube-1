<?php

namespace App\Repository;

use App\Entity\Ressource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ressource|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ressource|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ressource[]    findAll()
 * @method Ressource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RessourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ressource::class);
    }

    /**
     * @return Ressource[] Returns an array of Ressource objects
    */
    
    public function searchRessourceRep($criteria)
    {

        if ($criteria['categorie'] == null && isset($criteria['titre']) ){
            return $this->createQueryBuilder('r')
            ->andWhere('r.titre = :titre')
            ->setParameter('titre', $criteria['titre'])
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
        }elseif($criteria['titre'] == null && isset($criteria['categorie'])){
            return $this->createQueryBuilder('r')
            ->andWhere('r.categorie = :categorie')
            ->setParameter('categorie', $criteria['categorie'])
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        }else{
            return $this->createQueryBuilder('r')
            ->andWhere('r.titre = :titre')
            ->setParameter('titre', $criteria['titre'])
            ->andWhere('r.categorie = :categorie')
            ->setParameter('categorie', $criteria['categorie'])
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        }

        
    }
    /**
     * @return Ressource[] Returns an array of Ressource objects
    */
    
    public function navSearchRessourceRep($keyword)
    {
            //dd($keyword);
            return $this->createQueryBuilder('r')
            ->andWhere('r.titre = :titre')
            ->setParameter('titre', $keyword)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;

        

        
    }
    

    /*
    public function findOneBySomeField($value): ?Ressource
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
