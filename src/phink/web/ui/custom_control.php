<?php
/*
 * Copyright (C) 2016 David Blanchard
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

namespace Phink\Web\UI;

/**
 * Description of custom_control
 *
 * @author David
 */
abstract class TCustomControl extends \Phink\Core\TObject implements \Phink\Web\IHttpTransport, \Phink\Web\IWebObject
{
    use \Phink\Web\TWebObject;

    public function __construct($parent)
    {
        parent::__construct($parent);
    }

    protected $isRendered = false;

    public function init()
    {
    }
   
    public function load()
    {
    }
    
    public function view($html)
    {
    }
    
    public function partialLoad()
    {
    }
    
    public function beforeBinding()
    {
    }

    public function afterBinding()
    {
    }

    public function parse()
    {
    }

    public function renderHtml()
    {
    }

    public function render()
    {
    }

    public function unload()
    {
    }
}
