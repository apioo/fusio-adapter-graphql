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

/**
 * ErrorCollection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 *
 * @template-extends \ArrayObject<int, Error>
 */
class ErrorCollection extends \ArrayObject
{
    public function __construct(array $errors)
    {
        parent::__construct($this->build($errors));
    }

    public function getFirstMessage(): string
    {
        if ($this->offsetExists(0)) {
            return $this->offsetGet(0)->getMessage(); 
        } else {
            return 'An unknown error occurred';
        }
    }

    private function build(array $errors): array
    {
        $result = [];
        foreach ($errors as $error) {
            if ($error instanceof \stdClass) {
                $result[] = Error::fromObject($error);
            }
        }

        return $result;
    }
}
