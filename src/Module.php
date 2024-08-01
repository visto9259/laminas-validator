<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\ModuleManager\ModuleManager;
use Laminas\ServiceManager\ServiceManager;

/** @psalm-import-type ServiceManagerConfiguration from ServiceManager */
class Module
{
    /**
     * Return default laminas-validator configuration for laminas-mvc applications.
     *
     * @return array[]
     * @psalm-return array{service_manager: ServiceManagerConfiguration}
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }

    /**
     * Register a specification for the ValidatorManager with the ServiceListener.
     *
     * @deprecated ModuleManager support will be removed in version 3.0 of this component
     *
     * @param ModuleManager $moduleManager
     */
    public function init($moduleManager): void
    {
        $event           = $moduleManager->getEvent();
        $container       = $event->getParam('ServiceManager');
        $serviceListener = $container->get('ServiceListener');

        $serviceListener->addServiceManager(
            'ValidatorManager',
            'validators',
            ValidatorProviderInterface::class,
            'getValidatorConfig'
        );
    }
}
