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

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use GuzzleHttp\Client;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Http\Exception as StatusCode;

/**
 * GraphQLEngine
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class GraphQLEngine extends ActionAbstract
{
    protected ?string $url;
    protected ?\GuzzleHttp\Client $client;

    public function __construct(?string $url = null, ?Client $client = null)
    {
        $this->url    = $url;
        $this->client = $client ?: new Client();
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function setClient(?Client $client): void
    {
        $this->client = $client;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
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

    private function getJson(string $query, ?array $variables = null, ?string $operationName = null): array
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
