<?php

/*
 * Textpattern Content Management System
 * https://textpattern.io/
 *
 * Copyright (C) 2017 The Textpattern Development Team
 *
 * This file is part of Textpattern.
 *
 * Textpattern is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * Textpattern is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Textpattern. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Link tags.
 *
 * @since  4.7.0
 */

namespace Textpattern\Module\Link;

class Link
{
    /**
     * Checks if the link is the first in the list.
     *
     * @param  array  $atts
     * @param  string $thing
     * @return string
     */

    public static function if_first_link($atts, $thing)
    {
        global $thislink;

        assert_link();

        return parse($thing, !empty($thislink['is_first']));
    }

    /**
     * Checks if the link is the last in the list.
     *
     * @param  array  $atts
     * @param  string $thing
     * @return string
     */

    public static function if_last_link($atts, $thing)
    {
        global $thislink;

        assert_link();

        return parse($thing, !empty($thislink['is_last']));
    }


    /**
     * Return a link and its `Title` field contents
     *
     * @param  array  $atts
     * @return string
     */

    public static function linkdesctitle($atts)
    {
        global $thislink;

        assert_link();

        extract(lAtts(array(
            'rel' => '',
        ), $atts));

        $description = ($thislink['description'])
            ? ' title="'.txpspecialchars($thislink['description']).'"'
            : '';

        return tag(
            txpspecialchars($thislink['linkname']), 'a',
            ($rel ? ' rel="'.txpspecialchars($rel).'"' : '').
            ' href="'.doSpecial($thislink['url']).'"'.$description
        );
    }

    // -------------------------------------------------------------

    public static function link_name($atts)
    {
        global $thislink;

        assert_link();

        extract(lAtts(array(
            'escape' => 'html',
        ), $atts));

        return ($escape == 'html')
            ? txpspecialchars($thislink['linkname'])
            : $thislink['linkname'];
    }

    // -------------------------------------------------------------

    public static function link_url()
    {
        global $thislink;

        assert_link();

        return doSpecial($thislink['url']);
    }

    // -------------------------------------------------------------

    public static function link_author($atts)
    {
        global $thislink, $s;

        assert_link();

        extract(lAtts(array(
            'class'        => '',
            'link'         => 0,
            'title'        => 1,
            'section'      => '',
            'this_section' => '',
            'wraptag'      => '',
        ), $atts));

        if ($thislink['author']) {
            $author_name = get_author_name($thislink['author']);
            $display_name = txpspecialchars(($title) ? $author_name : $thislink['author']);

            $section = ($this_section) ? ($s == 'default' ? '' : $s) : $section;

            $author = ($link)
                ? href($display_name, pagelinkurl(array('s' => $section, 'author' => $author_name, 'context' => 'link')))
                : $display_name;

            return ($wraptag) ? doTag($author, $wraptag, $class) : $author;
        }
    }

    // -------------------------------------------------------------

    public static function link_description($atts)
    {
        global $thislink;

        assert_link();

        extract(lAtts(array(
            'class'    => '',
            'escape'   => 'html',
            'label'    => '',
            'labeltag' => '',
            'wraptag'  => '',
        ), $atts));

        if ($thislink['description']) {
            $description = ($escape == 'html') ?
                txpspecialchars($thislink['description']) :
                $thislink['description'];

            return doLabel($label, $labeltag).doTag($description, $wraptag, $class);
        }
    }

    // -------------------------------------------------------------

    public static function link_date($atts)
    {
        global $thislink, $dateformat;

        assert_link();

        extract(lAtts(array(
            'format' => $dateformat,
            'gmt'    => '',
            'lang'   => '',
        ), $atts));

        return safe_strftime($format, $thislink['date'], $gmt, $lang);
    }

    // -------------------------------------------------------------

    public static function link_category($atts)
    {
        global $thislink;

        assert_link();

        extract(lAtts(array(
            'class'    => '',
            'label'    => '',
            'labeltag' => '',
            'title'    => 0,
            'wraptag'  => '',
        ), $atts));

        if ($thislink['category']) {
            $category = ($title)
                ? fetch_category_title($thislink['category'], 'link')
                : $thislink['category'];

            return doLabel($label, $labeltag).doTag($category, $wraptag, $class);
        }
    }

    // -------------------------------------------------------------

    public static function link_id()
    {
        global $thislink;

        assert_link();

        return $thislink['id'];
    }

    // -------------------------------------------------------------

    /**
     * Return linklist
     *
     * @param  array  $atts
     * @param  string $thing
     * @return string
     */


    public static function linklist($atts, $thing = null)
    {
        global $s, $c, $context, $thislink, $thispage, $pretext;

        extract(lAtts(array(
            'break'       => '',
            'category'    => '',
            'author'      => '',
            'realname'    => '',
            'auto_detect' => 'category, author',
            'class'       => __FUNCTION__,
            'form'        => 'plainlinks',
            'id'          => '',
            'label'       => '',
            'labeltag'    => '',
            'pageby'      => '',
            'limit'       => 0,
            'offset'      => 0,
            'sort'        => 'linksort asc',
            'wraptag'     => '',
        ), $atts));

        $where = array();
        $filters = isset($atts['category']) || isset($atts['author']) || isset($atts['realname']);
        $context_list = (empty($auto_detect) || $filters) ? array() : do_list_unique($auto_detect);
        $pageby = ($pageby == 'limit') ? $limit : $pageby;

        if ($category) {
            $where[] = "category IN ('".join("','", doSlash(do_list_unique($category)))."')";
        }

        if ($id) {
            $where[] = "id IN ('".join("','", doSlash(do_list_unique($id)))."')";
        }

        if ($author) {
            $where[] = "author IN ('".join("','", doSlash(do_list_unique($author)))."')";
        }

        if ($realname) {
            $authorlist = safe_column("name", 'txp_users', "RealName IN ('".join("','", doArray(doSlash(do_list_unique($realname)), 'urldecode'))."')");
            if ($authorlist) {
                $where[] = "author IN ('".join("','", doSlash($authorlist))."')";
            }
        }

        // If no links are selected, try...
        if (!$where && !$filters) {
            foreach ($context_list as $ctxt) {
                switch ($ctxt) {
                    case 'category':
                        // ...the global category in the URL.
                        if ($context == 'link' && !empty($c)) {
                            $where[] = "category = '".doSlash($c)."'";
                        }
                        break;
                    case 'author':
                        // ...the global author in the URL.
                        if ($context == 'link' && !empty($pretext['author'])) {
                            $where[] = "author = '".doSlash($pretext['author'])."'";
                        }
                        break;
                }

                // Only one context can be processed.
                if ($where) {
                    break;
                }
            }
        }

        if (!$where && $filters) {
            // If nothing matches, output nothing.
            return '';
        }

        if (!$where) {
            // If nothing matches, start with all links.
            $where[] = "1 = 1";
        }

        $where = join(" AND ", $where);

        // Set up paging if required.
        if ($limit && $pageby) {
            $grand_total = safe_count('txp_link', $where);
            $total = $grand_total - $offset;
            $numPages = ($pageby > 0) ? ceil($total/$pageby) : 1;
            $pg = (!$pretext['pg']) ? 1 : $pretext['pg'];
            $pgoffset = $offset + (($pg - 1) * $pageby);

            // Send paging info to txp:newer and txp:older.
            $pageout['pg']          = $pg;
            $pageout['numPages']    = $numPages;
            $pageout['s']           = $s;
            $pageout['c']           = $c;
            $pageout['context']     = 'link';
            $pageout['grand_total'] = $grand_total;
            $pageout['total']       = $total;

            if (empty($thispage)) {
                $thispage = $pageout;
            }
        } else {
            $pgoffset = $offset;
        }

        $qparts = array(
            $where,
            'ORDER BY '.doSlash($sort),
            ($limit) ? 'LIMIT '.intval($pgoffset).', '.intval($limit) : '',
        );

        $rs = safe_rows_start("*, UNIX_TIMESTAMP(date) AS uDate", 'txp_link', join(' ', $qparts));

        if ($rs) {
            $count = 0;
            $last = numRows($rs);
            $out = array();

            while ($a = nextRow($rs)) {
                ++$count;
                $thislink = $a;
                $thislink['date'] = $thislink['uDate'];
                $thislink['is_first'] = ($count == 1);
                $thislink['is_last'] = ($count == $last);
                unset($thislink['uDate']);

                $out[] = ($thing) ? parse($thing) : parse_form($form);

                $thislink = '';
            }

            if ($out) {
                return doLabel($label, $labeltag).doWrap($out, $wraptag, $break, $class);
            }
        }

        return '';
    }

    /**
     * Return link
     *
     * @param  array  $atts
     * @return string
     */

    public static function link($atts)
    {
        global $thislink;

        extract(lAtts(array(
            'rel'  => '',
            'id'   => '',
            'name' => '',
        ), $atts));

        $rs = $thislink;
        $sql = array();

        if ($id) {
            $sql[] = "id = ".intval($id);
        } elseif ($name) {
            $sql[] = "linkname = '".doSlash($name)."'";
        }

        if ($sql) {
            $rs = safe_row("linkname, url", 'txp_link', implode(" AND ", $sql)." LIMIT 1");
        }

        if (!$rs) {
            trigger_error(gTxt('unknown_link'));

            return;
        }

        return tag(
            txpspecialchars($rs['linkname']), 'a',
            ($rel ? ' rel="'.txpspecialchars($rel).'"' : '').
            ' href="'.txpspecialchars($rs['url']).'"'
        );
    }


}
