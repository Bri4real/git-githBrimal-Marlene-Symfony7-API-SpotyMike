<?php

namespace ContainerCVuE3t8;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getSignUpControllerService extends App_KernelDevDebugContainer
{
    /**
     * Gets the public 'App\Controller\SignUpController' shared autowired service.
     *
     * @return \App\Controller\SignUpController
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/vendor/symfony/framework-bundle/Controller/AbstractController.php';
        include_once \dirname(__DIR__, 4).'/src/Controller/SignUpController.php';

        $container->services['App\\Controller\\SignUpController'] = $instance = new \App\Controller\SignUpController(($container->services['doctrine.orm.default_entity_manager'] ?? $container->load('getDoctrine_Orm_DefaultEntityManagerService')), ($container->privates['validator'] ?? $container->load('getValidatorService')));

        $instance->setContainer(($container->privates['.service_locator.jUv.zyj'] ?? $container->load('get_ServiceLocator_JUv_ZyjService'))->withContext('App\\Controller\\SignUpController', $container));

        return $instance;
    }
}
