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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;


/**
 * Controller managing the resetting of the password
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class ResettingController extends Controller
{
    const SESSION_ADMIN_RESET = 'fos_user_send_resetting_email/admin_email';
    const RESET_EMAIL_ALREADY_SENT = 0;
    const RESET_EMAIL_OK = 1;


    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var int
     */
    private $retryTtl;

    /**
     * @param UserManagerInterface     $userManager
     * @param TokenGeneratorInterface  $tokenGenerator
     * @param MailerInterface          $mailer
     * @param int                      $retryTtl
     */
    public function __construct(UserManagerInterface $userManager, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer, $retryTtl)
    {
        $this->userManager = $userManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->retryTtl = $retryTtl;
        $this->mailer = $mailer;
    }

    /**
     * Request reset user password: submit form and send email
     * @param Request $request
     *
     * @return RedirectResponse or Response
     */
    public function sendEmailAction(Request $request)
    {
        $email = $request->get('email');

        /**
         * @var $user UserInterface
         */
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            return $this->get('templating')->renderResponse(
                'FOSUserBundle:Resetting:request.html.twig',
                array('invalid_email' => $email)
            );
        }

        $code = $this->resetEmail($user);
        switch ($code) {
            case self::RESET_EMAIL_ALREADY_SENT:
                return $this->get('templating')->renderResponse(
                    'FOSUserBundle:Resetting:passwordAlreadyRequested.html.twig'
                );
                break;
            case self::RESET_EMAIL_OK:
                return new RedirectResponse(
                    $this->generateUrl('fos_user_resetting_check_email')
                );
                break;
        }
    }

    /**
     * Request reset user password: submit form and send email
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function adminSendEmailAction(Request $request)
    {
        $email = $request->query->get('email');

        /**
         * @var $user UserInterface
         */
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            $this->addFlash(self::SESSION_ADMIN_RESET, 'ctp_user.user.actions.reset_password.not_exist');
        } else {
            $code = $this->resetEmail($user);

            switch ($code) {
                case self::RESET_EMAIL_ALREADY_SENT:
                    $this->addFlash(
                        self::SESSION_ADMIN_RESET,
                        'ctp_user.user.actions.reset_password.already_sent'
                    );
                    break;
                case self::RESET_EMAIL_OK:
                    $this->addFlash(
                        self::SESSION_ADMIN_RESET,
                        'ctp_user.user.actions.reset_password.sent'
                    );
                    break;
            }
        }

        return new RedirectResponse(
            $this->get('router')->generate('sam_user_list')
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
        if ($user->isPasswordRequestNonExpired($this->retryTtl)) {
            return self::RESET_EMAIL_ALREADY_SENT;
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->mailer->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->userManager->updateUser($user);

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
        return $this->get('router')->generate('root');
    }
}
