<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CanalTP\SamEcoreUserManagerBundle\Controller;

use FOS\UserBundle\Controller\ResettingController as BaseResettingController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller managing the resetting of the password
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class ResettingController extends BaseResettingController
{
    const SESSION_ADMIN_RESET = 'fos_user_send_resetting_email/admin_email';
    const RESET_EMAIL_ALREADY_SENT = 0;
    const RESET_EMAIL_OK = 1;

    /**
     * Request reset user password: submit form and send email
     */
    public function sendEmailAction(Request $request)
    {
        $email = $this->container->get('request')->request->get('email');

        /**
         * @var $user UserInterface
         */
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            return $this->container->get('templating')->renderResponse(
                'FOSUserBundle:Resetting:request.html.'.$this->getEngine(),
                array('invalid_email' => $email)
            );
        }

        $code = $this->resetEmail($user);
        switch ($code) {
            case self::RESET_EMAIL_ALREADY_SENT:
                return $this->container->get('templating')->renderResponse(
                    'FOSUserBundle:Resetting:passwordAlreadyRequested.html.'.$this->getEngine()
                );
                break;
            case self::RESET_EMAIL_OK:
                return new RedirectResponse(
                    $this->container->get('router')->generate('fos_user_resetting_check_email')
                );
                break;
        }
    }

    /**
     * Request reset user password: submit form and send email
     */
    public function adminSendEmailAction()
    {
        $email = $this->container->get('request')->query->get('email');

        /**
         * @var $user UserInterface
         */
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            $this->setFlash(self::SESSION_ADMIN_RESET, 'ctp_user.user.actions.reset_password.not_exist');
        } else {
            $code = $this->resetEmail($user);

            switch ($code) {
                case self::RESET_EMAIL_ALREADY_SENT:
                    $this->setFlash(
                        self::SESSION_ADMIN_RESET,
                        'ctp_user.user.actions.reset_password.already_sent'
                    );
                    break;
                case self::RESET_EMAIL_OK:
                    $this->setFlash(
                        self::SESSION_ADMIN_RESET,
                        'ctp_user.user.actions.reset_password.sent'
                    );
                    break;
            }
        }

        return new RedirectResponse(
            $this->container->get('router')->generate('sam_user_list')
        );
    }

    /**
     * resetEmail
     *
     * reset le password et envoie un mail pour le redefinir
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     * @return Integer Code de retour
     */
    private function resetEmail(UserInterface $user)
    {
        $ttl = $this->container->getParameter('fos_user.resetting.token_ttl');
        if ($user->isPasswordRequestNonExpired($ttl)) {
            return self::RESET_EMAIL_ALREADY_SENT;
        }

        if (null === $user->getConfirmationToken()) {
            /**
             * @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface
             */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('session')->set(
            static::SESSION_EMAIL,
            $this->getObfuscatedEmail($user)
        );
        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return self::RESET_EMAIL_OK;
    }

    /**
     * Generate the redirection url when the resetting is completed.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('root');
    }
}
