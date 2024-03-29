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

namespace Fusio\Adapter\GraphQL;

use GuzzleHttp;

/**
 * Client
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Client implements ClientInterface
{
    private string $baseUrl;
    private \GuzzleHttp\Client $httpClient;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl    = $baseUrl;
        $this->httpClient = $this->newHttpClient();
    }

    public function request(string $query, ?array $variables = null, ?string $operationName = null): mixed
    {
        $response = $this->httpClient->post($this->baseUrl, [
            'json' => $this->getJson($query, $variables, $operationName)
        ]);

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        if (!$data instanceof \stdClass) {
            return null;
        }

        if (isset($data->errors) && is_array($data->errors)) {
            throw new ErrorException(new ErrorCollection($data->errors));
        } elseif (isset($data->data)) {
            return $data->data;
        } else {
            return null;
        }
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

    private function newHttpClient(): GuzzleHttp\Client
    {
        $options = [];
        $options['http_errors'] = false;

        return new GuzzleHttp\Client($options);
    }
}
