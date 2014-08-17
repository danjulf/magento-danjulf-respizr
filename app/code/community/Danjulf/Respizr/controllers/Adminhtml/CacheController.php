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

require_once implode(DS, array(
    'Mage', 'Adminhtml', 'controllers', 'CacheController.php'
));

class Danjulf_Respizr_Adminhtml_CacheController extends Mage_Adminhtml_CacheController
{

    /**
     * Clean image files cache
     */
    public function cleanImagesAction()
    {
        parent::cleanImagesAction();
        $this->clearRespizrImageCache();
    }

    /**
     * Clears all images in "resized" folder
     */
    public function clearRespizrImageCache()
    {
        $mediaPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $resizedDirPath = $mediaPath . DS . Mage::getSingleton('respizr/config')->getRespizrDirName();
        if (is_dir($resizedDirPath)) {
            $io = new Varien_Io_File();
            $io->rmdir($resizedDirPath, true);
        }
    }
}
