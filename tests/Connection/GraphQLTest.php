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

namespace Fusio\Adapter\GraphQL\Tests\Connection;

use Fusio\Adapter\GraphQL\ClientInterface;
use Fusio\Adapter\GraphQL\Connection\GraphQL;
use Fusio\Adapter\GraphQL\Tests\GraphQLTestCase;
use Fusio\Engine\Form\Builder;
use Fusio\Engine\Form\Container;
use Fusio\Engine\Form\Element\Input;
use Fusio\Engine\Parameters;

/**
 * GraphQLTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class GraphQLTest extends GraphQLTestCase
{
    public function testGetConnection()
    {
        /** @var GraphQL $connection */
        $connection = $this->getConnectionFactory()->factory(GraphQL::class);

        $config = new Parameters([
            'url' => 'https://graphql-demo-v2.now.sh/',
        ]);

        $client = $connection->getConnection($config);

        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function testConfigure()
    {
        $connection = $this->getConnectionFactory()->factory(GraphQL::class);
        $builder    = new Builder();
        $factory    = $this->getFormElementFactory();

        $connection->configure($builder, $factory);

        $this->assertInstanceOf(Container::class, $builder->getForm());

        $elements = $builder->getForm()->getElements();
        $this->assertEquals(1, count($elements));
        $this->assertInstanceOf(Input::class, $elements[0]);
    }
}
