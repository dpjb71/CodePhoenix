<?php
/*
 * Copyright (C) 2019 David Blanchard
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Phink\Web;

use Phink\Auth\TAuthentication;
use Phink\Web\TRequest;
use Phink\Web\TResponse;

/**
 * Description of httpTransport
 *
 * @author David
 */
trait THttpTransport
{
    //put your code here
    protected $request = null;
    protected $response = null;
    protected $authentication = null;

    public function getAuthentication(): TAuthentication
    {
        return $this->authentication;
    }

    public function getRequest(): TRequest
    {
        return $this->request;
    }

    public function getResponse(): TResponse
    {
        return $this->response;
    }
}
