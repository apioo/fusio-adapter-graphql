<?php

use Fusio\Adapter\GraphQL\Action\GraphQLProcessor;
use Fusio\Adapter\GraphQL\Action\GraphQLQuery;
use Fusio\Adapter\GraphQL\Connection\GraphQL;
use Fusio\Engine\Adapter\ServiceBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = ServiceBuilder::build($container);
    $services->set(GraphQL::class);
    $services->set(GraphQLProcessor::class);
    $services->set(GraphQLQuery::class);
};
