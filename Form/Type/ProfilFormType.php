<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilFormType extends BaseRegistrationFormType
{
    public function __construct()
    {
        parent::__construct('CanalTP\SamEcoreUserManagerBundle\Entity\User');
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add(
            'lastname',
            TextType::class,
            array(
                'label' => 'form.lastname',
                'translation_domain' => 'FOSUserBundle',
                'constraints' => array(
                    new NotBlank()
                )
            )
        );
        $builder->add(
            'firstname',
            TextType::class,
            array(
                'label' => 'form.firstname',
                'translation_domain' => 'FOSUserBundle',
                'constraints' => array(
                    new NotBlank()
                )
            )
        );
        $builder->add('email', EmailType::class, array('disabled' => true));

        $builder->add(
            'timezone',
            TimezoneType::class,
            [
              'label' => 'form.timezone',
              'preferred_choices' => array('Europe/Paris'),
              'translation_domain' => 'FOSUserBundle'
            ]
        );
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'edit_user_profil';
    }



}
