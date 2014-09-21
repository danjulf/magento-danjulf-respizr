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

class Danjulf_Respizr_Model_Config
{
    const RESPIZR_DIR_NAME      = 'resized';
    const RESPIZR_BREAKPOINTS   = 'respizr/general/breakpoints';
    const RESPIZR_RETINA        = 'respizr/general/retina';
    const RESPIZR_OFFSETS       = 'respizr/general/layout_offsets';

    /**
     * Retrieve directory name for resized images
     *
     * @return string
     */
    public function getRespizrDirName()
    {
        return self::RESPIZR_DIR_NAME;
    }

    /**
     * Retrieve Admin setting for Retina images
     *
     * @return bool
     */
    public function isRespizrRetina()
    {
        return Mage::getStoreConfig(self::RESPIZR_RETINA);
    }

    /**
     * Retrieve Breakpoints for current theme from Admin
     *
     * @return array
     */
    public function getRespizrBreakpoints()
    {
        $breakpoints = explode(',', Mage::getStoreConfig(self::RESPIZR_BREAKPOINTS));
        $breakpoints = array_map('trim', $breakpoints);
        arsort($breakpoints);
        return $breakpoints;
    }

    /**
     * Get breakpoint offsets by layout code
     *
     * @param string $layoutCode
     * @param Mage_Core_Model_Store $store
     * @return array
     */
    public function getRespizrOffsets($layoutCode, $store = null)
    {
        $value = Mage::getStoreConfig(self::RESPIZR_OFFSETS, $store);
        $offsets = @unserialize($value);
        return is_array($offsets) && isset ($offsets[$layoutCode])
            ? $offsets[$layoutCode]
            : array();
    }
}
