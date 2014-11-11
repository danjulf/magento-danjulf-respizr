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
 * @author    Henrik Hedelund <henke.hedelund@gmail.com>
 * @copyright 2014 Daniel Bergstrom (danjulf@gmail.com)
 * @license   http://www.gnu.org/licenses/lgpl.html GNU LGPL
 */

class Danjulf_Respizr_Model_Adminhtml_System_Config_Backend_Offsets
    extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
{

    /**
     * Cast offsets to int before saving
     *
     * @return void
     */
    protected function _beforeSave()
    {
        if (is_array($value = $this->getValue())) {
            foreach ($value as $layoutCode => $offsets) {
                $value[$layoutCode] = array_map('intval', $offsets);
            }
            $this->setValue($value);
        }
        parent::_beforeSave();
    }
}
