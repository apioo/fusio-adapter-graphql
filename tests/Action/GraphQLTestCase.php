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

namespace Fusio\Adapter\GraphQL\Tests\Action;

use Fusio\Adapter\GraphQL\Action\GraphQLEngine;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Test\EngineTestCaseTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Record\Record;

/**
 * GraphQLTestCase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class GraphQLTestCase extends TestCase
{
    use EngineTestCaseTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testHandle()
    {
        $transactions = [];
        $history = Middleware::history($transactions);

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Foo', 'Content-Type' => 'application/json'], json_encode(['data' => ['foo' => 'bar']])),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push($history);
        $client = new Client(['handler' => $handler]);

        /** @var GraphQLEngine $action */
        $action = $this->getActionFactory()->factory($this->getActionClass());
        $action->setClient($client);

        // handle request
        $response = $this->handle(
            $action,
            $this->getRequest(
                'POST',
                ['foo' => 'bar'],
                ['foo' => 'bar'],
                ['Content-Type' => 'application/json'],
                Record::fromArray(['query' => '{me{name}}'])
            ),
            $this->getParameters([
                'url' => 'http://127.0.0.1',
            ]),
            $this->getContext()
        );

        $actual = json_encode($response->getBody(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "data": {
        "foo": "bar"
    }
}
JSON;

        $this->assertInstanceOf(HttpResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);

        $this->assertEquals(1, count($transactions));
        $transaction = reset($transactions);

        $this->assertEquals('POST', $transaction['request']->getMethod());
        $this->assertEquals('http://127.0.0.1', $transaction['request']->getUri()->__toString());
        $this->assertJsonStringEqualsJsonString('{"query":"{me{name}}"}', $transaction['request']->getBody()->__toString());
    }

    public function testHandleError()
    {
        $transactions = [];
        $history = Middleware::history($transactions);

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Foo', 'Content-Type' => 'application/json'], json_encode(['errors' => [['message' => 'Server error']]])),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push($history);
        $client = new Client(['handler' => $handler]);

        /** @var GraphQLEngine $action */
        $action = $this->getActionFactory()->factory($this->getActionClass());
        $action->setClient($client);

        // handle request
        $response = $this->handle(
            $action,
            $this->getRequest(
                'POST',
                ['foo' => 'bar'],
                ['foo' => 'bar'],
                ['Content-Type' => 'application/json'],
                Record::fromArray(['query' => '{me{name}}'])
            ),
            $this->getParameters([
                'url' => 'http://127.0.0.1'
            ]),
            $this->getContext()
        );

        $actual = json_encode($response->getBody(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "errors": [
        {
            "message": "Server error"
        }
    ]
}
JSON;

        $this->assertInstanceOf(HttpResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);

        $this->assertEquals(1, count($transactions));
        $transaction = reset($transactions);

        $this->assertEquals('POST', $transaction['request']->getMethod());
        $this->assertEquals('http://127.0.0.1', $transaction['request']->getUri()->__toString());
        $this->assertJsonStringEqualsJsonString('{"query":"{me{name}}"}', $transaction['request']->getBody()->__toString());
    }

    abstract protected function getActionClass();

    abstract protected function handle(GraphQLEngine $action, RequestInterface $request, ParametersInterface $configuration, ContextInterface $context);
}
