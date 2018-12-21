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

namespace Fusio\Adapter\GraphQL\Connection;

use Fusio\Adapter\GraphQL\Client;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use GuzzleHttp;

/**
 * GraphQL
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class GraphQL implements ConnectionInterface
{
    public function getName()
    {
        return 'GraphQL';
    }

    /**
     * @param \Fusio\Engine\ParametersInterface $config
     * @return \Fusio\Adapter\GraphQL\ClientInterface
     */
    public function getConnection(ParametersInterface $config)
    {
        return new Client($this->newHttpClient(), $config->get('url'));
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newInput('url', 'Url', 'text', 'HTTP base url'));
    }

    protected function newHttpClient()
    {
        $options = [];
        $options['http_errors'] = false;

        return new GuzzleHttp\Client($options);
    }
}
