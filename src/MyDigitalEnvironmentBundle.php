<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle;

use Doctrine\ORM\EntityManagerInterface;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Controller\UserController;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\EventListener\LocaleListener;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\EventListener\VisitListener;
use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Service\MyDigitalEnvironmentParameters;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class MyDigitalEnvironmentBundle extends AbstractBundle
{
    // public const string TABLE_SCHEMA = 'my_digital_environment'; // Removing the typed class constant to support php8.2
    public const TABLE_SCHEMA = 'my_digital_environment';

    public function configure(DefinitionConfigurator $definition): void
    {
        // Really need to read more on how It's supposed to work
        // how would I implement a certain validation, where the key is a class of specific type
        // (inherit (correct term ?) an abstract class)
        // todo: add some validation to verify that the given parameters extends class / implement interface
        // read here: https://symfony.com/doc/current/components/config/definition.html
        $definition->rootNode()
            ->children()
                // todo: correct name ? maybe: registration_accessors ? Unsure.
                ->arrayNode('registration_modifiers')
                    ->integerPrototype()->end()
                ->end()
            ->end()
        ;
    }


    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // todo: transform to xml files or define in services.yaml ?
        $container->import('../config/services.yaml');

        $container->parameters()
            ->set('env(MY_DE_CAN_SEND_EMAIL)', false)
            ->set('env(MY_DE_EMAIL_ADDRESS_NAME)', 'My Digital Environment')
            ->set('my_digital_environment.can_send_email', '%env(bool:MY_DE_CAN_SEND_EMAIL)%')
            ->set('my_digital_environment.email_domain', '%env(default::MY_DE_EMAIL_DOMAIN)%')
            ->set('my_digital_environment.email_address_name', '%env(MY_DE_EMAIL_ADDRESS_NAME)%')
            ->set('my_digital_environment.registration_modifiers', $config['registration_modifiers'])
        ;

        $container->services()
            ->set(LocaleListener::class)
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'priority' => 512,
            ])
            ->tag('kernel.event_listener', [
                'event' => 'kernel.response',
                'priority' => 512,
            ])
        ;

        $container->services()
            ->set(VisitListener::class)
            ->arg('$entityManager', new Reference(EntityManagerInterface::class))
            ->arg('$security', new Reference(Security::class))
            ->tag('kernel.event_listener', [
                'event' => 'kernel.terminate',
                'priority' => 512,
            ])
        ;

        $container->services()
            ->get(MyDigitalEnvironmentParameters::class)
            ->arg('$canSendEmail', '%my_digital_environment.can_send_email%')
            ->arg('$emailDomain', '%my_digital_environment.email_domain%')
            ->arg('$emailAddressName', '%my_digital_environment.email_address_name%')
        ;

        $container->services()
            ->get(UserController::class)
            ->arg('$registrationModifiers', '%my_digital_environment.registration_modifiers%')
        ;
    }
}