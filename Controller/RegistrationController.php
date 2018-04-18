<?php

namespace CanalTP\SamEcoreUserManagerBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use FOS\UserBundle\Model\UserInterface;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

/**
 * {@inheritdoc}
 */
class RegistrationController extends BaseRegistrationController
{
    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction(Request $request)
    {
        $email = $this->container->get('session')->get('fos_user_send_confirmation_email/email');
        $this->container->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('The user with email "%s" does not exist', $email)
            );
        }

        return $this->container->get('templating')->renderResponse(
            'FOSUserBundle:Registration:checkEmail.html.twig',
            array(
                'user' => $user,
            )
        );
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction(Request $request, $token)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('The user with confirmation token "%s" does not exist', $token)
            );
        }

        $form = $this->container->get('canaltp_user.confirmation.form');
        $formHandler = $this->container->get('canaltp_user.confirmation.form.handler.default');
        $process = $formHandler->process($user);

        if ($process) {
            $user->setEnabled(true);
            $user->setConfirmationToken(null);
            $user->setLastLogin(new \DateTime());

            $response = new RedirectResponse(
                $this->container->get('router')->generate('fos_user_registration_confirmed')
            );
            $this->container->get('fos_user.user_manager')->updateUser($user);
            //$this->authenticateUser($user, $response);


            return $response;
        }

        return $this->container->get('templating')->renderResponse(
            'CanalTPSamEcoreUserManagerBundle:Registration:confirm.html.twig',
            array(
                'token' => $token,
                'form' => $form->createView(),
            )
        );

    }

}
