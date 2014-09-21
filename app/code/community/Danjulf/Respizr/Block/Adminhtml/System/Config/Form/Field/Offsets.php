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

class Danjulf_Respizr_Block_Adminhtml_System_Config_Form_Field_Offsets
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * True if the related breakpoints field inherits from parent scope
     *
     * @var boolean $_breakpointsInherit
     */
    protected $_breakpointsInherit = null;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate(
                'respizr/system/config/form/field/offsets.phtml'
            );
        }
    }

    /**
     * Get the grid and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
        return $html;
    }

    /**
     * Render form element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        if ($this->_getBreakpointsInherit() === false) {
            // If the breakpoints are changed in this scope,
            // the offsets should be changed as well
            $element->setInherit(false);
        }
        return parent::render($element);
    }

    /**
     * Get field holding breakpoints
     *
     * @return string|false
     */
    public function getBreakpointsField()
    {
        $field = false;
        if ($fieldConfig = $this->getElement()->getFieldConfig()) {
            /* @var $fieldConfig Mage_Core_Model_Config_Element */
            $field = (string) $fieldConfig->breakpoints_field;
        }
        return empty($field) ? false : $field;
    }

    /**
     * Get breakpoints
     *
     * @return array
     */
    public function getBreakpoints()
    {
        $path = $this->getBreakpointsField();
        if (!$path) {
            return array();
        }
        $configDataObject = Mage::getSingleton('adminhtml/config_data');
        /* @var $configDataObject Mage_Adminhtml_Model_Config_Data */
        $value = (string) $configDataObject->getConfigDataValue(
            $path,
            $this->_breakpointsInherit,
            $this->getConfigData()
        );
        return array_map(
            'intval',
            preg_split(
                '/\s*,\s*/',
                $value,
                -1,
                PREG_SPLIT_NO_EMPTY
            )
        );
    }

    /**
     *
     * @return boolean
     */
    protected function _getBreakpointsInherit()
    {
        if (is_null($this->_breakpointsInherit)) {
            $this->getBreakpoints();
        }
        return $this->_breakpointsInherit;
    }

    /**
     * Get layout-breakpoint-offsets map
     *
     * @return array
     */
    public function getOffsets()
    {
        $result = array();

        // Combine layouts and breakpoints
        $layouts = Mage::getSingleton('page/config')->getPageLayouts();
        $breakpoints = $this->getBreakpoints();
        $breakpointsCount = count($breakpoints);
        foreach ($layouts as $layout) {
            $result[$layout->getCode()] = array_combine(
                $breakpoints,
                $breakpointsCount > 0
                    ? array_fill(0, $breakpointsCount, 0)
                    : array()
            );
        }

        // Populate result with stored values
        $element = $this->getElement();
        if ($element->getValue() && is_array($element->getValue())) {
            foreach ($element->getValue() as $layoutCode => $offsets) {
                if (isset ($result[$layoutCode])) {
                    foreach ($offsets as $breakpoint => $offset) {
                        if (isset ($result[$layoutCode][$breakpoint])) {
                            $result[$layoutCode][$breakpoint] = $offset;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get page layout label by code
     *
     * @param string $layoutCode
     * @return string
     */
    public function getLayoutLabel($layoutCode)
    {
        $config = Mage::getSingleton('page/config');
        /* @var $config Mage_Page_Model_Config */
        if ($layout = $config->getPageLayout($layoutCode)) {
            return $layout->getLabel();
        }
        return $layoutCode;
    }

    /**
     *
     * @param string $layoutCode
     * @param int $breakpoint
     * @return string
     */
    public function getInputName($layoutCode, $breakpoint)
    {
        return sprintf(
            '%s[%s][%d]',
            $this->getElement()->getName(),
            $layoutCode,
            $breakpoint
        );
    }
}
