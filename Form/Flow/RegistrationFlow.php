<?php

namespace CanalTP\SamEcoreUserManagerBundle\Form\Flow;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use CanalTP\SamEcoreUserManagerBundle\Form\Type\RegistrationStepOneFormType;
use CanalTP\SamEcoreUserManagerBundle\Form\Type\UserType;
use CanalTP\SamEcoreUserManagerBundle\Form\Type\CustomerType;
use CanalTP\SamEcoreUserManagerBundle\Form\Type\RoleType;

class RegistrationFlow extends FormFlow
{
    private $userType;
    private $userAssignCustomerType;
    private $userCustomerAssignRoleType;

    /**
     * @inherit
     */
    /*public function __construct(
        UserType $userType,
        CustomerType $userAssignCustomerType,
        RoleType $userCustomerAssignRoleType
    )
    {
        $this->userType = $userType;
        $this->userAssignCustomerType = $userAssignCustomerType;
        $this->userCustomerAssignRoleType = $userCustomerAssignRoleType;
    }*/

    public function getName()
    {
        return 'registration';
    }

    protected function loadStepsConfig()
    {
        return array(
            array(
                'label' => 'form.user.step_1.title',
                'form_type' => 'CanalTP\SamEcoreUserManagerBundle\Form\Type\UserType'
            ),
            array(
                'label' => 'form.user.step_2.title',
                'form_type' => 'CanalTP\SamEcoreUserManagerBundle\Form\Type\CustomerType'
            ),
            array(
                'label' => 'form.user.step_3.title',
                'form_type' => 'CanalTP\SamEcoreUserManagerBundle\Form\Type\RoleType'
            )
        );
    }

}
