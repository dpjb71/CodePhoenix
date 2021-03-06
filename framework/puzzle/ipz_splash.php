<?php
/*
iPuzzle.WebPieces
Copyright (C) 2004 David Blanchard

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
namespace Puzzle;

class Splash extends Base
{
    public function display($message = "", $text_color = "", $back_color = "", $width = 0)
    {
        $result = "<div id='load_splash'>\n" .
            "<table bgcolor='$back_color' border='0' cellpadding='2' cellspacing='0' width='$width'>\n" .
            "\t<tr>\n" .
            "\t\t<td align='center' valgin='middle'>\n" .
            "\t\t\t<span face='helvetica' size='4'style='color:$text_color'>$message</span>\n" .
            "\t\t</td>\n" .
            "\t</tr>\n" .
            "</table>\n" .
            "</div>\n" .
            "<script language='JavaScript' src='/js/pz_splash.js'></script>\n" .
            "<SCRIPT language='JavaScript'>loadSplashOn(PZ_LOAD_SPLASH);</SCRIPT>";

        return $result;
    }
}
