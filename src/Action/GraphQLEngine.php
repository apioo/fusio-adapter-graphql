<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use GuzzleHttp\Client;
use PSX\Http\Exception as StatusCode;

/**
 * GraphQLEngine
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class GraphQLEngine extends ActionAbstract
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    public function __construct($url = null, Client $client = null)
    {
        $this->url    = $url;
        $this->client = $client ?: new Client();
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        if ($request->getMethod() === 'GET') {
            $query = $request->getParameter('query');
            $operationName = null;
            $variables = null;
        } elseif ($request->getMethod() === 'POST') {
            $body = $request->getBody();

            $query = $body->getProperty('query');
            $operationName = $body->getProperty('operationName');
            $variables = $body->getProperty('variables');
        } else {
            throw new StatusCode\MethodNotAllowedException('Method not allowed', ['GET', 'POST']);
        }

        if (empty($query)) {
            throw new StatusCode\BadRequestException('No query provided');
        }

        $response = $this->client->post($this->url, [
            'json' => $this->getJson($query, $variables, $operationName)
        ]);

        $body = (string) $response->getBody();
        $data = \GuzzleHttp\json_decode($body);

        return $this->response->build(
            200,
            [],
            $data
        );
    }

    /**
     * @param string $query
     * @param array|null $variables
     * @param string|null $operationName
     * @return array
     */
    private function getJson(string $query, array $variables = null, string $operationName = null)
    {
        $json = ['query' => $query];

        if ($operationName !== null) {
            $json['operationName'] = $operationName;
        }

        if ($variables !== null) {
            $json['variables'] = $variables;
        }

        return $json;
    }
}
