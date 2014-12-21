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

    const RESPIZR_VI_QUALITY            = 'respizr/varien_image/quality';
    const RESPIZR_VI_KEEP_ASPECT_RATIO  = 'respizr/varien_image/keep_aspect_ratio';
    const RESPIZR_VI_KEEP_FRAME         = 'respizr/varien_image/keep_frame';
    const RESPIZR_VI_KEEP_TRANSPARENCY  = 'respizr/varien_image/keep_transparency';
    const RESPIZR_VI_CONSTRAIN_ONLY     = 'respizr/varien_image/constrain_only';
    const RESPIZR_VI_BACKGROUND_COLOR   = 'respizr/varien_image/background_color';

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
        $breakpoints =
            explode(',', Mage::getStoreConfig(self::RESPIZR_BREAKPOINTS));
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

    /**
     * Get Respizr settings for Varien_Image
     *
     * @return array
     */
    public function getRespizrVarienImageSettings()
    {
        $varienImageSettings = array();

        $varienImageSettings['quality'] =
            Mage::getStoreConfig(self::RESPIZR_VI_QUALITY);
        $varienImageSettings['keep_aspect_ratio'] =
            Mage::getStoreConfig(self::RESPIZR_VI_KEEP_ASPECT_RATIO);
        $varienImageSettings['keep_frame'] =
            Mage::getStoreConfig(self::RESPIZR_VI_KEEP_FRAME);
        $varienImageSettings['keep_transparency'] =
            Mage::getStoreConfig(self::RESPIZR_VI_KEEP_TRANSPARENCY);
        $varienImageSettings['constrain_only'] =
            Mage::getStoreConfig(self::RESPIZR_VI_CONSTRAIN_ONLY);
        $varienImageSettings['background_color'] =
            $this->getBackgroundColor(false);

        return $varienImageSettings;
    }

    /**
     * Get image background RGB color as an array or hex string.
     *
     * @param boolean $asHex
     * @param Mage_Core_Model_Store|string|int|null $store
     * @return array|string
     */
    public function getBackgroundColor($asHex = false, $store = null)
    {
        $string = Mage::getStoreConfig(
            self::RESPIZR_VI_BACKGROUND_COLOR,
            $store
        );
        $array = preg_split('/\s*,\s*/', $string, -1, PREG_SPLIT_NO_EMPTY);
        $color = array();
        for ($i = 0; $i < 3; ++$i) {
            if (isset($array[$i])) {
                $color[] = min(255, max(0, (int) $array[$i]));
            } else {
                $color[] = 255;
            }
        }
        if ($asHex) {
            $hexString = '';
            foreach ($color as $c) {
                $hexString .= sprintf('%02s', dechex($c));
            }
            return $hexString;
        }
        return $color;
    }

}
