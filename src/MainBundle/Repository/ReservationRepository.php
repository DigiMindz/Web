<?php

namespace MainBundle\Repository;

/**
 * ReservationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReservationRepository extends \Doctrine\ORM\EntityRepository
{

    public function Affichage($id1){
        $SQB1 = $this->getEntityManager()->createQueryBuilder()->select('r')->from("MainBundle:Reservation",'r')
            ->join('r.etablissement','r_etab')->where('r_etab.id=:i')->setParameter('i',$id1);
        return $SQB1->getQuery()->getResult();
    }
    public function Affich1($id1){
        $SQB1 = $this->getEntityManager()->createQueryBuilder()->select('r')->from("MainBundle:Reservation",'r')
            ->join('r.user','r_user')->where('r_user.id=:i')->setParameter('i',$id1);
        return $SQB1->getQuery()->getResult();
    }
    public function Affich($id1){
         $query=$this->getEntityManager()->createQuery("SELECT r FROM MainBundle:Reservation r WHERE r.id like :i 
            order by r.id ASC")
            ->setParameter('i', '%'.$id1.'%');
        return $query->getResult();
    }
    public function FiltreEtab($id_etab){
        $query=$this->getEntityManager()->createQuery("SELECT r FROM MainBundle:Reservation r WHERE r.etablissement=:i  
            order by r.id ASC")
            ->setParameter('i', $id_etab);
        return $query->getResult();
    }

    public function FiltreUser($id_user){
        $query=$this->getEntityManager()->createQuery("SELECT r FROM MainBundle:Reservation r WHERE r.user=:i
            order by r.id ASC")
            ->setParameter('i', $id_user);
        return $query->getResult();
    }
    public function FiltreNom($nom){
        $query=$this->getEntityManager()->createQuery("SELECT r FROM MainBundle:Reservation r WHERE r.nom  like :i
            order by r.id ASC")
            ->setParameter('i', '%'.$nom.'%');
        return $query->getResult();
    }
    public function chercher($mot,$id_etab){

        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'select e FROM MainBundle:Reservation e where e.nom LIKE :mot OR e.prenom LIKE :mot and e.etablissement=:i '
        )->setParameter('mot', '%'.$mot.'%')->setParameter('i', $id_etab);

        return $query->getResult();
    }
}