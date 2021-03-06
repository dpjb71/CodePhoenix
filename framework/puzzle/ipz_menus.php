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

use Phink\Data\Client\PDO\TPdoConnection;

define("BUTTON_OUT", "out");
define("BUTTON_OVER", "over");
define("BUTTON_DOWN", "down");
define("BUTTON_UP", "up");
define("BUTTON_IMAGE", "img");
define("BUTTON_INPUT", "input");
define("BUTTON_IMAGE_RESET", "img_reset");
define("BUTTON_INPUT_RESET", "input_reset");

class Menus extends Base
{
    public const SUB_MENU_HORIZONTAL = 0;
    public const SUB_MENU_VERTICAL = 1;

    public function getAdminUrl($userdb)
    {
        $adm_url = "";
        $cs = TPdoConnection::opener($userdb);
        $sql = "select app_link from applications where di_name='modadmin'";
        $stmt = $cs->query($sql);
        if ($rows = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $adm_url = $rows["app_link"];
        }

        return $adm_url;
    }

    public function showMenu($userdb)
    {
        $cs = TPdoConnection::opener($userdb);

        $sql = 'delete from v_menus;';
        $cs->query($sql);

        $sql = <<<SQL
        insert into v_menus (me_id, pa_id, me_level, di_name, me_target, pa_filename, di_fr_short, di_fr_long, di_en_short, di_en_long)
            select m.me_id, m.pa_id, m.me_level, m.di_name, m.me_target, p.pa_filename, d.di_fr_short, d.di_fr_long, d.di_en_short, d.di_en_long 
            from menus m, pages p, dictionary d 
            where m.di_name = d.di_name 
            and p.di_name = d.di_name 
            order by m.me_id
SQL;
        //echo $sql;
        $cs->query($sql);

        $sql = "select me_id as Menu, pa_id as Page, me_level as Niveau, di_name as Dictionnaire, me_target as Cible, pa_filename as Fichier, di_fr_short as 'Francais court', di_fr_long as 'Francais long', di_en_short as 'Anglais court', di_en_long as 'Anglais long' from v_menus";

        //tableau_sql("menu", $sql, 0, "edit.php", "", "&database=$database", "", "", "", $cs);
        //container("menu", 50, 250, 200, 355, 16);
        $dbgrid = createDbGrid("menu", $sql, "editor", "", "&me_id=#Menu&userdb={$this->database}", false, $dialog, array(), $grid_colors, $cs);
        echo $dbgrid;
    }

    /*** OBSOLETE ***/
    /*
    function menu_exists($database, $pa_filename="")
    {
    $cs=connection(CONNECT, $database);

    $sql=    "select m.me_id, m.pa_id, m.me_level, m.di_name, m.me_target, p.pa_filename, d.di_fr_short, d.di_fr_long, d.di_en_short, d.di_en_long " .
    "from menus m, pages p, dictionary d " .
    "where m.di_name = d.di_name " .
    "and p.di_name = d.di_name " .
    "and p.pa_filename = '$pa_filename' " .
    "order by m.me_id";

    $stmt = $cs->query($sql);
    $exists=$result->num_rows>0;

    return $exists;
    }
     */

    public function getPageId($userdb, $pa_filename)
    {
        $cs = TPdoConnection::opener($userdb);
        $sql = <<<SQL
        SELECT 
            pa_id
        FROM
            pages
        WHERE
            pa_filename = '$pa_filename'
SQL;
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        self::getLogger()->dump('LG', $this->lg);

        $stmt = $cs->query($sql);
        $rows = $stmt->fetch();
        $pa_id = isset($rows[0]) ? (int) $rows[0] : 0;

        return $pa_id;
    }

    public function getMenuId($conf, $pa_filename)
    {
        $cs = TPdoConnection::opener($conf);
        $sql = <<<SQL
        SELECT 
            m.me_id, p.pa_id
        FROM
            menus m
                LEFT OUTER JOIN
            pages p ON m.pa_id = p.pa_id
        WHERE
            p.pa_filename = '$pa_filename'
SQL;
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        $stmt = $cs->query($sql);
        $rows = $stmt->fetch();
        $me_id = isset($rows[0]) ? (int) $rows[0] : 0;

        return $me_id;
    }

    public function getMenuAndPage($userdb, $pa_filename)
    {
        $cs = TPdoConnection::opener($userdb);
        $sql = <<<SQL
        SELECT 
            m.me_id, p.pa_id
        FROM
            menus m
                LEFT OUTER JOIN
            pages p ON m.pa_id = p.pa_id
        WHERE
            p.pa_filename = '$pa_filename'
SQL;
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        $stmt = $cs->query($sql);
        $rows = $stmt->fetch();
        $me_id = isset($rows[0]) ? (int) $rows[0] : 0;
        $pa_id = isset($rows[1]) ? (int) $rows[1] : 0;

        return [$me_id, $pa_id];
    }

    public function getPageFilename($conf, $id = 0)
    {
        $sql = <<<SQL
        select p.pa_filename
            from pages p, menus m, dictionary d
            where m.di_id=d.di_id
            and p.pa_id=m.pa_id
            and m.me_id=$id;
SQL;
        $cs = TPdoConnection::opener($conf);
        $stmt = $cs->query($sql);
        $rows = $stmt->fetch();
        $page = $rows[0];

        return $page;
    }

    public function addMenuAndPage(
        $userdb,
        $di_name,
        $me_level,
        $me_target,
        $pa_filename,
        $di_fr_short,
        $di_fr_long,
        $di_en_short = "",
        $di_en_long = ""
    ) {
        list($me_id, $pa_id) = $this->getMenuAndPage($userdb, $pa_filename);
        if (!($me_id && $pa_id)) {
            $cs = TPdoConnection::opener($userdb);
            $wwwroot = DOCUMENT_ROOT;

            if (empty($me_target)) {
                $me_target = "page";
            }
            $affected_rows = 0;
            $sql = <<<SQL
            INSERT INTO
                dictionary (di_name, di_fr_short, di_fr_long, di_en_short, di_en_long)
                VALUES 
                    ('$di_name', '$di_fr_short', '$di_fr_long', '$di_en_short', '$di_en_long')
SQL;
            $cs->beginTransaction();
            $affected_rows += (int) $cs->exec($sql);
            $di_id = $cs->lastInsertId();

            self::getLogger()->debug($sql, __FILE__, __LINE__);

            $sql = <<<SQL
            INSERT INTO
                pages (di_id, pa_filename)
                VALUES
                    ('$di_id', '$pa_filename')
SQL;
            $affected_rows += (int) $cs->exec($sql);
            $pa_id = $cs->lastInsertId();

            self::getLogger()->debug($sql, __FILE__, __LINE__);

            $sql = <<<SQL
            INSERT INTO
                menus (me_level, me_target, pa_id)
                VALUES 
                    ('$me_level', '$me_target', $pa_id)
SQL;
            $affected_rows += (int) $cs->exec($sql);
            $me_id = $cs->lastInsertId();

            self::getLogger()->debug($sql, __FILE__, __LINE__);

            $cs->commit();
        }
        return [$me_id, $pa_id, $affected_rows];
    }

    public function updateMenu(
        $userdb,
        $di_name,
        $me_level,
        $me_target,
        $pa_filename,
        $di_fr_short,
        $di_fr_long,
        $di_en_short,
        $di_en_long
    ) {
        list($me_id, $pa_id) = $this->getMenuAndPage($userdb, $pa_filename);

        $cs = TPdoConnection::opener($userdb);

        $cs->beginTransaction();

        $sql = <<<SQL
        UPDATE menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id 
        SET 
            m.di_id = d.di_id,
            me_level = '$me_level',
            me_target = '$me_target',
            m.pa_id = $pa_id
        WHERE
            m.me_id = $me_id
                AND d.di_id = '$di_name';
SQL;
        $affected_rows = $cs->exec($sql);

        $sql = <<<SQL
        update pages set di_name='$di_name', pa_filename='$pa_filename'
            where pa_id=$pa_id
SQL;
        $affected_rows += $cs->exec($sql);

        $sql = <<<SQL
        UPDATE dictionary 
        SET 
            di_fr_short = '$di_fr_short',
            di_fr_long = '$di_fr_long',
            di_en_short = '$di_en_short',
            di_en_long = '$di_en_long'
        WHERE
            di_name = '$di_name'
SQL;
        $affected_rows += $cs->exec($sql);

        $cs->commit();

        return $affected_rows;
    }

    public function deleteMenu($userdb, $di_name)
    {
        $cs = TPdoConnection::opener($userdb);

        $cs->beginTransaction();

        $sql = <<<SQL
        DELETE 
        FROM
            menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id
        WHERE
            d.di_name='$di_name'
SQL;
        $affected_rows = $cs->exec($sql);

        $sql = <<<SQL
        DELETE 
        FROM
            pages p 
                INNER JOIN
            dictionary d ON d.di_id = p.di_id
        WHERE
            d.di_name='$di_name'
SQL;
        $affected_rows += $cs->exec($sql);

        $sql = <<<SQL
        DELETE 
        FROM
            dictionary 
        WHERE
            di_name='$di_name'
SQL;
        $affected_rows += $cs->exec($sql);

        $cs->commit();

        return $affected_rows;
    }

    public function makeButtonImage($text = "", $style = "", $hl_color = "")
    {
        $images_dir = getLocalImagesDir();
        $filename = $images_dir . $text . "_" . $style . ".png";

        if (!file_exists($filename)) {
            $size = 10;
            $offset = -16;
            $fonts_dir = getLocalFontsDir();
            $font = $fonts_dir . "tahoma.ttf";

            if (!empty($hl_color)) {
                $red = hexdec(substr($hl_color, 0, 2));
                $green = hexdec(substr($hl_color, 2, 2));
                $blue = hexdec(substr($hl_color, 4, 2));
            } else {
                $red = 255;
                $green = 255;
                $blue = 255;
            }

            if (
                $style == BUTTON_UP
                || $style == BUTTON_OUT
                || $style == BUTTON_OVER
            ) {
                $offsetX = -2;
                $offsetY = -2;
                $position = BUTTON_UP;
            } elseif ($style == BUTTON_DOWN) {
                $offsetX = 0;
                $offsetY = 0;
                $position = BUTTON_DOWN;
            }

            list($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly) = imageTTFbbox($size, 0, $font, $text);

            $fwidth = abs($llx) + $lrx;
            $fheight = abs($uly - $lly);

            $im = imagecreate($fwidth + $offset + 24, 24);
            $blue_bg = ImageColorAllocate($im, 0, 0, 255);
            imagecolortransparent($im, $blue_bg);

            $src_im = imagecreatefrompng($images_dir . "builds/button_" . $position . "_left.png");
            imagecopy($im, $src_im, 0, 0, 0, 0, 12, 24);
            imagedestroy($src_im);

            $src_im = imagecreatefrompng($images_dir . "builds/button_" . $position . "_middle.png");
            imagecopy($im, $src_im, 12, 0, 0, 0, $fwidth + $offset, 24);
            imagedestroy($src_im);

            $src_im = imagecreatefrompng($images_dir . "builds/button_" . $position . "_right.png");
            imagecopy($im, $src_im, $fwidth + $offset + 12, 0, 0, 0, 12, 24);
            imagedestroy($src_im);

            $width = imagesx($im);
            $height = imagesy($im);
            $shadow_color = ImageColorAllocate($im, 0, 0, 0);
            $fore_color = ImageColorAllocate($im, $red, $green, $blue);
            $values = "($red, $green, $blue)";

            $left = abs(($width - $fwidth) / 2) + abs($llx) + $offsetX;
            $top = abs(($height - $fheight) / 2) + $fheight - $lly + $offsetY;
            //$top=abs(($height-$fheight)/2)+abs($uly);

            imagettftext($im, $size, 0, $left, $top, $shadow_color, $font, $text);
            imagettftext($im, $size, 0, $left + 1, $top + 1, $fore_color, $font, $text);
            imagepng($im, $filename, 255);
            //passthru("convert $filename.png $filename.gif");
            imagedestroy($im);
        }

        return $values;
    }

    public function makeButtonCode($text = "", $type = "", $out_color = "", $over_color = "", $down_color = "")
    {
        $values = makeButtonImage($text, BUTTON_OUT, $out_color);
        $values = makeButtonImage($text, BUTTON_OVER, $over_color);
        $values = makeButtonImage($text, BUTTON_DOWN, $down_color);
        $images_dir = getHttpImagesDir();

        if ($type == BUTTON_IMAGE || $type == BUTTON_IMAGE_RESET) {
            $button = "<img\n";
        } elseif ($type == BUTTON_INPUT || $type == BUTTON_INPUT_RESET) {
            $button = "<input type=\"image\" name=\"$text\" value=\"$text\"\n";
        }

        $button .= "\tid=\"$text\"\n";
        $button .= "\tsrc=\"" . $images_dir . $text . "_out.png\"\n";
        $button .= "\tonMouseOut=\"PZ_IMG.src='" . $images_dir . $text . "_out.png';\"\n";
        $button .= "\tonMouseOver=\"PZ_IMG=document.getElementById('$text'); PZ_IMG.src='" . $images_dir . $text . "_over.png';\"\n";
        $button .= "\tonMouseDown=\"PZ_IMG.src='" . $images_dir . $text . "_down.png';\"\n";
        if ($type == BUTTON_IMAGE || $type == BUTTON_INPUT) {
            $button .= "\tonMouseUp=\"PZ_IMG.src='" . $images_dir . $text . "_over.png';\"\n";
        } elseif ($type == BUTTON_IMAGE_RESET || $type == BUTTON_INPUT_RESET) {
            $button .= "\tonMouseUp=\"PZ_IMG.src='" . $images_dir . $text . "_over.png'; document.myForm.reset();\"\n";
        }
        $button .= ">\n";

        return $button;
    }

    public function createMainMenu($conf, $level = 0)
    {
        //${this->lg}=getArgument("lg");

        $main_menu = '';
        $sql = "";
        $sql = <<<SQL
        SELECT 
            m.pa_id, m.me_level, d.di_{$this->lg}_short, d.di_name
        FROM
            menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id
        WHERE
            m.me_level='$level'
        ORDER BY m.me_id        
SQL;
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        self::getLogger()->dump('LG', $this->lg);

        $cs = TPdoConnection::opener($conf);
        $stmt = $cs->query($sql);
        $count = 0;
        $menu_items = [];
        while ($rows = $stmt->fetch()) {
            $id = $rows[0];
            $level = $rows[1];
            $caption = $rows[2];
            $name = $rows[3];
            //$target=$rows[3];
            //$link=$rows[4];

            #$main_menu=$main_menu . "<td bgcolor='black'><a href='?id=$id&lg=" . ${this->lg} . "'><span style='color:#ffffff'><b>$caption</b></span></a><span style='color:#ffffff'><b>&nbsp;|&nbsp;</b></span></td>";
            //$menu_items[] =  "<a href='?id=$id&lg={$this->lg}'><span>$caption</span></a>";
            $menu_items[] = "<a href='?id=$id&di=$name&lg={$this->lg}'><span>$caption</span></a>";

            if ($count == 0) {
                $default_id = $id;
            }
            $count++;
        }

        $main_menu = implode("<span>&nbsp;|&nbsp;</span>", $menu_items);

        //$stmt->free();

        return array("id" => $default_id, "menu" => $main_menu);
    }

    public function createSubMenu($conf, $id = 0, $orientation)
    {
        if ($orientation == Menus::SUB_MENU_HORIZONTAL) {
            $sub_menu = "";
        } elseif ($orientation == Menus::SUB_MENU_VERTICAL) {
            $sub_menu = "<table width='100%'>";
        }

        $sql = <<<SQL
        SELECT 
            m.me_id,
            m.me_level,
            d.di_{$this->lg}_short,
            m.me_target,
            p.pa_filename,
            p.pa_id
        FROM
            menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id AND m.me_id <> m.pa_id
                AND m.me_level > 1
                AND m.pa_id = $id;
SQL;
        //and m.me_id<>m.pa_id

        $cs = TPdoConnection::opener($conf);
        $stmt = $cs->query($sql);
        while ($rows = $stmt->fetch()) {
            $id = $rows[0];
            $level = $rows[1];
            $caption = $rows[2];
            $target = $rows[3];
            $link = $rows[4];
            $page = $rows[5];
            if ($orientation == Menus::SUB_MENU_HORIZONTAL) {
                switch ($level) {
                    case "2":
                        $sub_menu .= "<a href='?id=$id&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                        break;
                    case "3":
                        $sub_menu .= "<a href='$target?id=$id&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                        break;
                    case "4":
                        $sub_menu .= "<a href='?id=$page&lg={$this->lg}#$target'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                        //$sub_menu.="<a href='$PHP_SELF#$target'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                        break;
                }
            } elseif ($orientation == Menus::SUB_MENU_VERTICAL) {
                switch ($level) {
                    case "2":
                        $sub_menu .= "<tr><td><a href='?id=$id&lg={$this->lg}'>$caption</a></td></tr>";
                        break;
                    case "3":
                        $sub_menu .= "<tr><td><a href='$target?id=$id&lg={$this->lg}'>$caption</a></td></tr>";
                        break;
                    case "4":
                        $sub_menu .= "<tr><td><a href='?id=$page&lg={$this->lg}#$target'>$caption</a></td></tr>";
                        break;
                    case "5":
                        $sub_menu .= "<tr><td>&nbsp;&nbsp;&nbsp;<a href='?id=$page&lg={$this->lg}#$target'>$caption</a></td></tr>";
                    // no break
                    case "6":
                        $sub_menu .= "<tr><td><a href='$link' target='_new'>$caption</a></td></tr>";
                        break;
                }
            }
        }
        if ($orientation == Menus::SUB_MENU_HORIZONTAL) {
            $sub_menu = substr($sub_menu, 0, strlen($sub_menu) - 14);
        } elseif ($orientation == Menus::SUB_MENU_VERTICAL) {
            $sub_menu .= "</table>";
        }
        //$stmt->free();
        return $sub_menu;
    }

    public function createMenuTree($conf, $id = 0, $lg = "", $orientation)
    {
        if ($orientation == Menus::SUB_MENU_HORIZONTAL) {
            $sub_menu = "";
        } elseif ($orientation == Menus::SUB_MENU_VERTICAL) {
            $sub_menu = "<table width='100%'>";
        }

        $sql = <<<SQL
        SELECT 
            m.me_id,
            m.me_level,
            d.di_{$this->lg}_short,
            m.me_target,
            p.pa_filename,
            p.pa_id
        FROM
            menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id
        WHERE
            m.me_id=$id 
        ORDER BY p.pa_id , m.me_level
SQL;

        $cs = TPdoConnection::opener($conf);
        $stmt = $cs->query($sql);
        while ($rows = $stmt->fetch()) {
            $page = $rows[5];
        }
        if (!empty($page)) {
            if ($page != $id) {
                $id = $page;
            }
        }

        $sql = <<<SQL
        SELECT 
            m.me_id,
            m.me_level,
            d.di_{$this->lg}_short,
            m.me_target,
            p.pa_filename,
            p.pa_id
        FROM
            menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id 
        ORDER BY p.pa_id , m.me_level
SQL;

        //echo "$sql<br>";

        $stmt = $cs->query($sql);
        while ($rows = $stmt->fetch()) {
            $id = $rows[0];
            $level = $rows[1];
            $caption = $rows[2];
            $target = $rows[3];
            $link = $rows[4];
            $page = $rows[5];
            if ($orientation == Menus::SUB_MENU_HORIZONTAL) {
                switch ($level) {
                    case "1":
                        $sub_menu .= "<a href='?id=$id&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                        break;
                    case "2":
                        $sub_menu .= "<a href='?id=$id&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                        break;
                    case "3":
                        $sub_menu .= "<a href='$target?id=$id&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                        break;
                    case "4":
                        $sub_menu .= "<a href='?id=$page&lg={$this->lg}#$target'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                        //$sub_menu.="<a href='$PHP_SELF#$target'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                        break;
                }
            } elseif ($orientation == Menus::SUB_MENU_VERTICAL) {
                switch ($level) {
                    case "1":
                        $sub_menu .= "<tr><td><a href='?id=$id&lg={$this->lg}'>$caption</a></td></tr>";
                        break;
                    case "2":
                        $sub_menu .= "<tr><td>&nbsp;&nbsp;<a href='?id=$id&lg={$this->lg}'>$caption</a></td></tr>";
                        break;
                    case "3":
                        $sub_menu .= "<tr><td>&nbsp;&nbsp;<a href='$target?id=$id&lg={$this->lg}'>$caption</a></td></tr>";
                        break;
                    case "4":
                        $sub_menu .= "<tr><td>&nbsp;&nbsp;<a href='?id=$page&lg={$this->lg}#$target'>$caption</a></td></tr>";
                        break;
                    case "5":
                        $sub_menu .= "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<a href='?id=$page&lg={$this->lg}#$target'>$caption</a></td></tr>";
                    // no break
                    case "6":
                        $sub_menu .= "<tr><td>&nbsp;&nbsp;<a href='$link' target='_new'>$caption</a></td></tr>";
                        break;
                }
            }
        }
        if ($orientation == Menus::SUB_MENU_HORIZONTAL) {
            $sub_menu = substr($sub_menu, 0, strlen($sub_menu) - 14);
        } elseif ($orientation == Menus::SUB_MENU_VERTICAL) {
            $sub_menu .= "</table>";
        }
        //$stmt->free();
        return $sub_menu;
    }

    public function retrievePageById($conf, $id = 0, $lg = "")
    {
        $title = "";
        $page = "";
        $sql = "";
        $sql = <<<SQL
        SELECT 
            d.di_name,
            p.pa_filename, 
            d.di_{$this->lg}_short, 
            d.di_{$this->lg}_long
        FROM
            menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id
        WHERE
            p.pa_id = $id
SQL;
        self::getLogger()->debug($sql, __FILE__, __LINE__);

        //echo $sql . "<br>";
        //"and p.pa_id=m.me_id " .
        $cs = TPdoConnection::opener($conf);
        $stmt = $cs->query($sql);
        $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
        $di = $rows["di_name"];
        $page = $rows["pa_filename"];
        $title = $rows["di_" . $this->lg . "_long"];
        if ($title == "") {
            $title = $rows["di_" . $this->lg . "_short"];
        }
        self::getLogger()->debug($rows, __FILE__, __LINE__);

        $request = "";
        $p = strpos($page, "?", 0);
        if ($p > -1) {
            $request = "&" . substr($page, $p + 1, strlen($page) - $p);
            $page = substr($page, 0, $p);
        }

        $title_page = array("id" => $id, "di" => $di, "title" => $title, "page" => $page, "request" => $request, "charset" => 'utf-8', "lang" => $this->lg);

        /*
        $filename=${this->lg}."/".$page;

        if (!file_exists($filename)) {
        copy("includes/fichier_vide.php", $filename);
        }
         */
        return $title_page;
    }

    public function retrievePageByMenuId($conf, $id = 0, $lg = "")
    {
        $title = "";
        $page = "";
        $sql = "";
        $sql = <<<SQL
        SELECT 
            d.di_name,
            p.pa_filename,
            d.di_{$this->lg}_short,
            d.di_{$this->lg}_long
        FROM
            menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id
        WHERE
            m.me_id=$id
SQL;
        // self::getLogger()->debug($sql, __FILE__, __LINE__);

        //        echo $sql . "<br>";
        //"and p.pa_id=m.me_id " .
        $cs = TPdoConnection::opener($conf);
        $stmt = $cs->query($sql);
        $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
        $id = $rows["di_name"];
        $page = $rows["pa_filename"];
        $title = $rows["di_" . $this->lg . "_long"];
        $charset = $rows["me_charset"];
        if ($title == "") {
            $title = $rows["di_" . $this->lg . "_short"];
        }

        self::getLogger()->debug($rows, __FILE__, __LINE__);

        $request = "";
        $p = strpos($page, "?", 0);
        if ($p > -1) {
            $request = "&" . substr($page, $p + 1, strlen($page) - $p);
            $page = substr($page, 0, $p);
        }

        $title_page = array("id" => $id, "title" => $title, "page" => $page, "request" => $request, "charset" => $charset);

        /*
        $filename=${this->lg}."/".$page;

        if (!file_exists($filename)) {
        copy("includes/fichier_vide.php", $filename);
        }
         */
        return $title_page;
    }

    public function retrievePageByDictionaryId($conf, $di = "", $lg = "")
    {
        $title = "";
        $page = "";
        $sql = "";
        $sql = <<<SQL
        SELECT 
            m.me_id, p.pa_filename, d.di_{$this->lg}_short, d.di_{$this->lg}_long
        FROM
            menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id
        WHERE
            d.di_name='$di'
SQL;

        self::getLogger()->debug($sql, __FILE__, __LINE__);

        $cs = TPdoConnection::opener($conf);
        $stmt = $cs->query($sql);
        $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
        $id = $rows["me_id"];
        $page = empty($rows["pa_filename"]) ? "#none" : $rows["pa_filename"];
        $title = $rows["di_" . $this->lg . "_long"];
        if ($title == "") {
            $title = $rows["di_" . $this->lg . "_short"];
        }

        self::getLogger()->debug($rows, __FILE__, __LINE__);

        $request = "";
        $p = strpos($page, "?", 0);
        if ($p > -1) {
            $request = "&" . substr($page, $p + 1, strlen($page) - $p);
            $page = substr($page, 0, $p);
        }

        $title_page = array("id" => $id, "di" => $di, "title" => $title, "page" => $page, "request" => $request, "charset" => 'utf-8', "lang" => $this->lg);

        return $title_page;
    }

    public function getTabIdes($conf)
    {
        self::getLogger()->dump(__FILE__ . ':' . __METHOD__ . ':' . __LINE__ . ':conf', $conf);

        $sql = <<<SQL
        SELECT 
            m.me_id, d.di_name
        FROM
            menus m
                INNER JOIN
            pages p ON m.pa_id = p.pa_id
                INNER JOIN
            dictionary d ON d.di_id = p.di_id
        WHERE
            d.di_name LIKE 'mk%'
        ORDER BY m.me_id
SQL;
        self::getLogger()->dump(__FILE__ . ':' . __METHOD__ . ':' . __LINE__ . ':SQL', $sql);

        $cs = TPdoConnection::opener($conf);

        $stmt = $cs->query($sql);
        $tab_ides = (array) null;
        $i = 0;
        while ($rows = $stmt->fetch()) {
            $tab_ides[$rows[0]] = $rows[1];
            $i++;
        }

        self::getLogger()->dump(__FILE__ . ':' . __METHOD__ . ':' . __LINE__ . ':RES', $tab_ides);

        return $tab_ides;
    }
}
