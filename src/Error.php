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

/**
 * Error
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Error
{
    /**
     * @var string 
     */
    protected $message;

    /**
     * @var array
     */
    protected $locations;

    /**
     * @var array
     */
    protected $path;

    /**
     * @var \stdClass
     */
    protected $extensions;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param array $locations
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }

    /**
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @param array $path
     */
    public function setPath(array $path)
    {
        $this->path = $path;
    }

    /**
     * @return \stdClass
     */
    public function getExtensions(): \stdClass
    {
        return $this->extensions;
    }

    /**
     * @param \stdClass $extensions
     */
    public function setExtensions(\stdClass $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @param \stdClass $error
     * @return Error
     */
    public static function fromObject(\stdClass $error)
    {
        $error = new static($error->message ?? 'Unknown error');

        if (isset($error->locations) && is_array($error->locations)) {
            $error->setLocations($error->locations);
        }

        if (isset($error->path) && is_array($error->path)) {
            $error->setPath($error->path);
        }

        if (isset($error->extensions) && $error->extensions instanceof \stdClass) {
            $error->setExtensions($error->extensions);
        }

        return $error;
    }
}
