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

namespace Fusio\Adapter\GraphQL;

use GuzzleHttp;

/**
 * Client
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Client implements ClientInterface
{
    /**
     * @var \GuzzleHttp\Client 
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param \GuzzleHttp\Client $httpClient
     * @param string $baseUrl
     */
    public function __construct(GuzzleHttp\Client $httpClient, string $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl    = $baseUrl;
    }

    /**
     * @inheritdoc
     */
    public function request(string $query, array $variables = null, string $operationName = null)
    {
        $response = $this->httpClient->post($this->baseUrl, [
            'json' => $this->getJson($query, $variables, $operationName)
        ]);

        $body = (string) $response->getBody();
        $data = GuzzleHttp\json_decode($body);

        if (isset($data->errors) && is_array($data->errors)) {
            throw new ErrorException(new ErrorCollection($data->errors));
        } elseif (isset($data->data)) {
            return $data->data;
        } else {
            return null;
        }
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
