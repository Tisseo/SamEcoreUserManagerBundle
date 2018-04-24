<?php

namespace CanalTP\SamEcoreUserManagerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use CanalTP\SamEcoreUserManagerBundle\DependencyInjection\CanalTPSamEcoreUserManagerExtension;
use CanalTP\SamEcoreUserManagerBundle\DependencyInjection\Compiler\FormHandlerPass;

class CanalTPSamEcoreUserManagerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new FormHandlerPass());
    }

    /*public function getParent()
    {
        return 'FOSUserBundle';
    }*/

    public function getContainerExtension()
    {
        return new CanalTPSamEcoreUserManagerExtension();
    }
}
