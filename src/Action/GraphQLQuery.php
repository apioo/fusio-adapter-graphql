<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Adapter\GraphQL\Action;

use Fusio\Adapter\GraphQL\ClientInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Exception\ConfigurationException;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * GraphQLQuery
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class GraphQLQuery extends ActionAbstract
{
    public function getName(): string
    {
        return 'GraphQL-Query';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
    {
        $client = $this->getClient($configuration);

        $operationName = $configuration->get('operation_name');
        if (empty($operationName)) {
            $operationName = null;
        }

        $query = $configuration->get('query');
        if (empty($query)) {
            throw new ConfigurationException('No query configured');
        }

        $data = $client->request($query, $request->getArguments(), $operationName);

        return $this->response->build(
            200,
            [],
            $data
        );
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newConnection('connection', 'Connection', 'The GraphQL connection which should be used'));
        $builder->add($elementFactory->newInput('operation_name', 'Operation-Name', 'text', 'Optional an operation name'));
        $builder->add($elementFactory->newTextArea('query', 'Query', 'graphql', 'The GraphQL query which is send to the server'));
    }

    protected function getClient(ParametersInterface $configuration): ClientInterface
    {
        $connection = $this->connector->getConnection($configuration->get('connection'));
        if (!$connection instanceof ClientInterface) {
            throw new ConfigurationException('Given connection must be a GraphQL connection');
        }

        return $connection;
    }
}
