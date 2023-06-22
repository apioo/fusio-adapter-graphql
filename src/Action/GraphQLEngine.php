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

use Fusio\Adapter\GraphQL\Client;
use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Response\FactoryInterface;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Record\RecordInterface;

/**
 * GraphQLEngine
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class GraphQLEngine implements ActionInterface
{
    protected ?string $url = null;

    private FactoryInterface $response;

    public function __construct(RuntimeInterface $runtime)
    {
        $this->response = $runtime->getResponse();
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
    {
        $operationName = null;
        $variables = null;

        $requestContext = $request->getContext();
        if ($requestContext instanceof HttpRequestContext && $requestContext->getRequest()->getMethod() === 'GET') {
            $query = $request->get('query');
        } else {
            $body = $request->getPayload();
            if ($body instanceof RecordInterface) {
                $query = $body->get('query');
                $operationName = $body->get('operationName');
                $variables = $body->get('variables');
            }
        }

        if (empty($query) || !is_string($query)) {
            throw new StatusCode\BadRequestException('No query provided');
        }

        if ($variables !== null && !is_array($variables)) {
            throw new StatusCode\BadRequestException('Variables must be an object');
        }

        $url = $this->url ?? throw new StatusCode\InternalServerErrorException('No url configured');

        $client = new Client($url);
        $data = $client->request($query, $variables, $operationName);

        return $this->response->build(
            200,
            [],
            $data
        );
    }
}
