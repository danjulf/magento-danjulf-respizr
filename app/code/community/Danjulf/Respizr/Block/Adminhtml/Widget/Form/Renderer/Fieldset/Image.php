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

class Danjulf_Respizr_Block_Adminhtml_Widget_Form_Renderer_Fieldset_Image
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{

    /**
     * Render Image Element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $previewHtml = '';
        if ($element->getValue()) {

            // Add image preview.
            $url = Mage::getStoreConfig('web/unsecure/base_url') .
                $element->getValue();

            if (!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                $url = Mage::getBaseUrl('media') . $url;
            }

            $previewHtml = '<a href="' . $url . '"'
                . ' onclick="imagePreview(\'' . $element->getHtmlId()
                . '_image\'); return false;">' . '<img src="' . $url . '" id="'
                . $element->getHtmlId() . '_image" title="'
                . $element->getValue() . '"' . ' alt="' . $element->getValue()
                . '" height="40" class="small-image-preview v-middle"'
                . ' style="margin-top:7px; border:1px solid grey" /></a> ';
        }

        $prefix = $element->getForm()->getHtmlIdPrefix();
        $elementId = $prefix . $element->getId();
        $chooserUrl = $this->getUrl('*/cms_wysiwyg_images/index', array(
            'target_element_id' => $elementId
        ));

        $label = ($element->getValue()) ?
            $this->__('Change Image') : $this->__('Insert Image');

        $chooseButton =
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('btn-chooser')
                ->setLabel($label)
                ->setOnclick(
                    'MediabrowserUtility.openDialog(\'' . $chooserUrl . '\')'
                )
                ->setDisabled($element->getReadonly())
                ->setStyle('display:inline;margin-top:7px');

        // Add delete button.
        $removeButton =
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('delete')
                ->setLabel($this->__('Remove Image'))
                ->setOnclick(
                    'document.getElementById(\'' . $elementId
                    . '\').value=\'\';if(document.getElementById(\''. $elementId
                    . '_image\'))document.getElementById(\'' . $elementId
                    . '_image\').parentNode.remove()'
                )
                ->setDisabled($element->getReadonly())
                ->setStyle('margin-left:10px;margin-top:7px');

        $element->setData(
            'after_element_html', $previewHtml . $chooseButton->toHtml()
                . $removeButton->toHtml()
        );

        $this->_element = $element;
        return $this->toHtml();
    }

}
