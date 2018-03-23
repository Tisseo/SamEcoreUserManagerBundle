<?php

namespace CanalTP\SamEcoreUserManagerBundle\Processor;

use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;


/**
 * Cette classe a pour but de gerer les differentes données
 * accessibles en fonction du role d'un utilisateur
 *
 * @author JRE <johan.rouve@canaltp.fr>
 * @copyright Canal TP (c) 2013
 * */
class RoleProcessor
{
    private $authorizationChecker;
    private $userManager;

    /**
     * __construct
     *
     * @param SecurityContext      $securityContext
     * @param UserManagerInterface $userManager
     */
    public function __construct(AuthorizationChecker $authorizationChecker, UserManagerInterface $userManager)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->userManager = $userManager;
    }

    /**
     * Recupere la liste d'utilisateurs visible pour l'utilisateur courant.
     * Le SUPER_ADMIN peut voir tout le monde
     * L'ADMIN peut voir ADMIN et USER
     *
     * @return Array
     */
    public function getVisibleUsers($page)
    {
        $roleSuperAdmin = 'ROLE_SUPER_ADMIN';

        if ($this->authorizationChecker->isGranted($roleSuperAdmin)) {
            $entities = $this->userManager->findPaginateUsers($page);
        } else {
            $entities = $this->userManager->findPaginateUsersExcludingRole($roleSuperAdmin, $page);
        }

        return $entities;
    }

    /**
     * Retourne le nombre d'utilisateurs visible pour l'utilisateur courant.
     * Le SUPER_ADMIN peut voir tout le monde
     * L'ADMIN peut voir ADMIN et USER
     *
     * @return Integer
     */
    public function countVisibleUsers()
    {
        $roleSuperAdmin = 'ROLE_SUPER_ADMIN';

        if ($this->authorizationChecker->isGranted($roleSuperAdmin)) {
            $count = $this->userManager->countUsers();
        } else {
            $count = $this->userManager->countUsersExcludingRole($roleSuperAdmin);
        }

        return $count;
    }

    /**
     * Retourne le nombre de pages presente dans la liste
     *
     * @return Integer
     */
    public function getPagination()
    {
        $limit = $this->userManager->getUsersLimit();
        $count = $this->countVisibleUsers();
        $pages = $count / $limit;
        $remainder = $count % $limit;
        if ($remainder === 0) {
            return $pages;
        } else {
            return floor($pages) + 1;
        }
    }
}
