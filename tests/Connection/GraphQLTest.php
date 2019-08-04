<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2019 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Adapter\GraphQL\Error;
use Fusio\Adapter\GraphQL\ErrorCollection;
use Fusio\Adapter\GraphQL\ErrorException;
use Fusio\Engine\Form\Builder;
use Fusio\Engine\Form\Container;
use Fusio\Engine\Form\Element\Input;
use Fusio\Engine\Parameters;
use Fusio\Engine\Test\EngineTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * GraphQLTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class GraphQLTest extends TestCase
{
    use EngineTestCaseTrait;

    public function testGetConnection()
    {
        /** @var GraphQL $connection */
        $connection = $this->getConnectionFactory()->factory(GraphQL::class);

        $config = new Parameters([
            'url' => 'https://graphql-demo-v2.now.sh/',
        ]);

        $client = $connection->getConnection($config);

        $this->assertInstanceOf(ClientInterface::class, $client);

        // test send query against fake endpoint
        $query = <<<GRAPHQL
{
  allBooks {
    author,
    title
  }
}
GRAPHQL;

        $data = $client->request($query);

        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertTrue(isset($data->allBooks));
    }

    public function testGetConnectionError()
    {
        /** @var GraphQL $connection */
        $connection = $this->getConnectionFactory()->factory(GraphQL::class);

        $config = new Parameters([
            'url' => 'https://graphql-demo-v2.now.sh/',
        ]);

        $client = $connection->getConnection($config);

        $this->assertInstanceOf(ClientInterface::class, $client);

        // test send query against fake endpoint
        $query = <<<'GRAPHQL'
{
  allBooks(count: $foo) {
    author,
    title
  }
}
GRAPHQL;

        try {
            $client->request($query);

            $this->fail('Should throw an exception');
        } catch (ErrorException $e) {
            $collection = $e->getErrors();
            $this->assertEquals('Unknown argument "count" on field "allBooks" of type "Query".', $e->getMessage());
            $this->assertInstanceOf(ErrorCollection::class, $collection);
            $this->assertInstanceOf(Error::class, $collection[0]);
            $this->assertEquals('Unknown argument "count" on field "allBooks" of type "Query".', $collection[0]->getMessage());
        }
    }

    public function testConfigure()
    {
        $connection = $this->getConnectionFactory()->factory(GraphQL::class);
        $builder    = new Builder();
        $factory    = $this->getFormElementFactory();

        $connection->configure($builder, $factory);

        $this->assertInstanceOf(Container::class, $builder->getForm());

        $elements = $builder->getForm()->getProperty('element');
        $this->assertEquals(1, count($elements));
        $this->assertInstanceOf(Input::class, $elements[0]);
    }
}
