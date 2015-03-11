<?php
/**
 * Danjulf_Respizr (Responsive Image Resizer)
 *
 * Copyright (C) 2014 Daniel Bergstrom (danjulf@gmail.com)
 *
 * This file is part of Danjulf_Respizr.
 *
 * Danjulf_Respizr is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Danjulf_Respizr is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Danjulf_Respizr. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Danjulf
 * @package   Danjulf_Respizr
 * @author    Daniel Bergstrom <danjulf@gmail.com>
 * @copyright 2014 Daniel Bergstrom (danjulf@gmail.com)
 * @license   http://www.gnu.org/licenses/lgpl.html GNU LGPL
 */

class Danjulf_Respizr_Model_Widget extends Mage_Widget_Model_Widget
{
    /**
     * Return widget presentation code in WYSIWYG editor
     *
     * @param string $type Widget Type
     * @param array $params Pre-configured Widget Params
     * @param bool $asIs Return result as widget directive(true) or as placeholder image(false)
     * @return string Widget directive ready to parse
     */
    public function getWidgetDeclaration($type, $params = array(), $asIs = true)
    {
        if ($type == 'respizr/widget_picture') {
            // Replace image_urls with decoded relative url
            foreach ($params as $k => $v) {
                if (strpos($v, '/admin/cms_wysiwyg/directive/___directive/') !== false){
                    $parts = explode('/', parse_url($v, PHP_URL_PATH));
                    $key = array_search('___directive', $parts);
                    if ($key !== false) {
                        $directive = $parts[$key + 1];
                        $src = Mage::getModel('core/email_template_filter')
                            ->filter(Mage::helper('core')->urlDecode(
                                    $directive));
                        if (!empty($src)) {
                            $params[$k] = parse_url($src, PHP_URL_PATH);
                        }
                    }
                }
            }
        }
        return parent::getWidgetDeclaration($type, $params, $asIs);
    }
}