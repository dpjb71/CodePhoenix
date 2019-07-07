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
 
 
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Phink\Web;

use Phink\Core\IObject;

 /**
 * Description of TObject
 *
 * @author david
 */
 

 interface IWebObject extends IHttpTransport, IObject {
 
    public function getApplication();
    public function getCacheFileName();
    public function getClassName();
    public function getActionName();
    public function getModelFileName();
    public function getViewFileName();
    public function getControllerFileName();
    public function getJsControllerFileName();
    public function getCssFileName();
    public function getViewName();
    public function getParameters();
    
    
}