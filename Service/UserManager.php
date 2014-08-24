<?php

namespace CanalTP\SamEcoreUserManagerBundle\Service;

use CanalTP\SamEcoreApplicationManagerBundle\Component\BusinessComponentRegistry;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;

class UserManager extends BaseUserManager
{
    /**
     * Business Registry
     * @var BusinessComponentRegistry
     */
    private $businessRegistry;

    /**
     * Permet de récuperer tous les utilisateurs triés
     * par ordre de connexion antechronologique
     *
     * @return Array
     */
    public function findUsers()
    {
        $query = $this->repository->createQueryBuilder('u')
            ->orderBy('u.lastLogin', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    public function find($id)
    {
        $query = $this->repository->createQueryBuilder('u')
            ->addSelect('r')
            ->addSelect('a')
            ->leftJoin('u.userRoles', 'r')
            ->leftJoin('r.application', 'a')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function deleteUser(UserInterface $user)
    {
        //get all user's application
        $roles = $user->getUserRoles();
        $appNames = array();

        foreach ($roles as $role) {
            $appNames[$role->getApplication()->getId()] = $role->getApplication()->getCanonicalName();
        }

        //remove all links between user and perimeters in all apps
        foreach ($appNames as $appName) {
            try{
                $this->getBusinessRegistry()
                    ->getBusinessComponent($appName)
                    ->getPerimetersManager()
                    ->deleteUserPerimeters($user);
            } catch (\Exception $e) {
            }
        }

        //finnaly, delete user with the external userManager
        parent::deleteUser($user);
    }

    public function setBusinessRegistry(BusinessComponentRegistry $businessRegistry)
    {
        $this->businessRegistry = $businessRegistry;
    }

    public function getBusinessRegistry()
    {
        return $this->businessRegistry;
    }
}
