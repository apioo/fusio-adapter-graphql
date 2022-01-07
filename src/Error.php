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
 * Error
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class Error
{
    protected string $message;
    protected ?array $locations = null;
    protected ?array $path = null;
    protected ?\stdClass $extensions = null;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLocations(): ?array
    {
        return $this->locations;
    }

    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }

    public function getPath(): ?array
    {
        return $this->path;
    }

    public function setPath(array $path)
    {
        $this->path = $path;
    }

    public function getExtensions(): ?\stdClass
    {
        return $this->extensions;
    }

    public function setExtensions(\stdClass $extensions)
    {
        $this->extensions = $extensions;
    }

    public static function fromObject(\stdClass $error): self
    {
        $error = new self($error->message ?? 'Unknown error');

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
