<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Handler;

use FOS\UserBundle\Mailer\Mailer;
use FOS\UserBundle\Util\TokenGenerator;
use CanalTP\SamEcoreUserManagerBundle\Service\UserManager;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

/*use Symfony\Component\Form\FormError;
use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseRegistrationFormHandler;
use CanalTP\SamEcoreApplicationManagerBundle\Exception\OutOfBoundsException;
use CanalTP\SamEcoreApplicationManagerBundle\Component\BusinessComponentRegistry;
use CanalTP\SamEcoreUserManagerBundle\Form\Model\UserRegistration;*/

class RegistrationFormHandler /*extends BaseRegistrationFormHandler*/
{
    /*private $objectManager;

    public function setObjectManager($om)
    {
        $this->objectManager = $om;
    }*/

    /**
     * @var UserManager $userManager
     */
    private $userManager;

    /**
     * @var Mailer $mailer
     */
    private $mailer;

    /**
     * @var TokenGenerator $tokenGenerator
     */
    private $tokenGenerator;

    public function __construct(UserManager $userManager, Mailer $mailer, TokenGenerator $tokenGenerator) {
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->userManager = $userManager;
    }

    /**
     * @param boolean $confirmation
     * @see about setPlainPassword -> https://github.com/FriendsOfSymfony/FOSUserBundle/issues/898
     */
    public function save(User $user, $confirmation = false)
    {
        if (!$user->isEnabled() && $user->getStatus() == User::STATUS_STEP_1) {
            $user->setPlainPassword(md5(time()));
        }
        if (!$user->isEnabled() && $user->getStatus() == User::STATUS_STEP_3) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }
            $this->userManager->updateUser($user);
            $this->mailer->sendConfirmationEmailMessage($user);
            $user->setStatus(User::MAIL_SENDED);
        } else if ($confirmation && $user->getStatus() == User::MAIL_SENDED) {
            $user->setEnabled(true);
        }

        $this->userManager->updateUser($user);
    }
}
