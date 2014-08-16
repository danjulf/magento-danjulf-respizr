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

class Danjulf_Respizr_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Takes an existing image uploaded in media or skin folder and resizes it
     *
     * @param string $originalUrl
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function resizeImg($originalUrl, $width, $height = null)
    {
        if (empty($originalUrl) || empty($width)) { return false; }

        $originalPaths = $this->getImgPathsFromUrl($originalUrl);

        if (!$originalPaths) { return false; }

        $originalPath = $originalPaths['path'];
        $relativePath = $originalPaths['relativePath'];
        $resizedRelativePath = $this->getResizedImgRelativePath($relativePath, $width, $height);
        $mediaPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $resizedDirPath = $mediaPath . DS . Mage::getSingleton('respizr/config')->getRespizrDirName();
        if (!is_dir($resizedDirPath)) {
            mkdir($resizedDirPath, 0777, true);
        }

        $resizedImagePath = $resizedDirPath . DS . $resizedRelativePath;

        if (file_exists($originalPath) && is_file($originalPath)) {
            if (!file_exists($resizedImagePath)) {
                $imageObj = new Varien_Image($originalPath);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(FALSE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize($width, $height);
                $imageObj->save($resizedImagePath);
            }
            if (file_exists($resizedImagePath)) {
                $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
                $resizedUrl = $mediaUrl . Mage::getSingleton('respizr/config')->getRespizrDirName() . DS . $resizedRelativePath;
                return $resizedUrl;
            }
        }
        return false;
    }

    /**
     * Resizes an image for normal and retina-resolution for all
     * breakpoints specified in config
     *
     * @param array $rConfig
     * @param string $imageUrl
     * @return mixed
     */
    public function resizeImages($rConfig, $imageUrl) {

        if (empty($rConfig) || empty($imageUrl)) { return false; }

        $images = array();

        foreach ($rConfig['breakpoints'] as $breakpoint) {
            $_width = intval($breakpoint * $rConfig['multiplier']);
            $_resizedImg = $this->resizeImg($imageUrl, $_width);

            if($rConfig['retina']) {
                $_resizedImg2x = $this->resizeImg($imageUrl, $_width * 2);
            }

            if ($_resizedImg) {
                $images[$breakpoint] = array( 'image_url' => $_resizedImg );
            }

            if (isset($_resizedImg2x)) {
                $images[$breakpoint]['image_url_2x'] = $_resizedImg2x;
            }
        }
        return $images;
    }

    /**
     * Retrives a resized image RelativePath
     *
     * @param string $filePath
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getResizedImgRelativePath($filePath, $width, $height = null)
    {
        $pathInfo = pathinfo($filePath);
        $fileName =  $pathInfo['filename'].'_'.$width;
        if (!empty($height)) {
            $fileName .= 'x'.$height;
        }
        $fileName .= '.'.$pathInfo['extension'];

        return $pathInfo['dirname'] . DS . $fileName;
    }

    /**
     * Gets image paths from Url
     *
     * @param string $imgUrl
     * @return mixed
     */
    public function getImgPathsFromUrl($imgUrl)
    {
        $mediaUrl   = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $skinUrl    = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        $mediaPath  = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $skinPath   = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_SKIN);

        $possibleUrls = array(
            $mediaUrl   => $mediaPath,
            $skinUrl    => $skinPath
        );

        foreach ($possibleUrls as $url => $path) {
            if(strpos($imgUrl, $url) !== false) {
                $imgPath['path'] = str_replace($url, $path . DS, $imgUrl);
                $imgPath['relativePath'] = str_replace($url, '', $imgUrl);
                return $imgPath;
            }
        }
        return false;
    }

    /**
     * Returns picture html with one source per specified breakpoint
     *
     * @param string $imageUrl
     * @param int $alt
     * @param int $maxWidth
     * @return mixed
     */
    public function getPictureHtml($imageUrl, $alt, $maxWidth)
    {
        if (!$imageUrl || !$maxWidth) { return false; }

        $rConfig    = $this->getResponsiveConfig($maxWidth);
        $images     = $this->resizeImages($rConfig, $imageUrl);

        return $this->preparePictureHtml($rConfig, $images, $alt);
    }

    /**
     * Returns picture html for a product image with one source per specified
     * breakpoint
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeName
     * @param int $maxWidth
     * @return mixed
     */
    public function getProductPictureHtml(Mage_Catalog_Model_Product $product, $attributeName, $maxWidth)
    {
        if (!$product || !$attributeName || !$maxWidth) { return false; }

        $rConfig    = $this->getResponsiveConfig($maxWidth);
        $images     = $this->resizeProductImages($rConfig, $product, $attributeName);

        return $this->preparePictureHtml($rConfig, $images, '');
    }

    /**
     * Prepares picture data for output in template
     *
     * @param array $rConfig
     * @param array $images
     * @param string $alt
     * @return mixed
     */
    public function preparePictureHtml($rConfig, $images, $alt)
    {
        if (!$rConfig || !count($images) > 0) { return false; }

        $smallestImg    = $images[$rConfig['breakpoints'][count($rConfig['breakpoints']) - 1]];
        $largestImg     = $images[$rConfig['breakpoints'][0]];
        $multiplier     = $rConfig['multiplier'];

        $pictureHtml = Mage::app()->getLayout()->createBlock('core/template', '', array(
            'template'      => 'respizr/picture.phtml',
            'alt'           => $alt,
            'images'        => $images,
            'smallest_img'  => $smallestImg,
            'largest_img'   => $largestImg,
        ));

        return $pictureHtml->toHtml();
    }

    /**
     * Resizes a product image for normal and retina-resolution for all
     * breakpoints specified in config
     *
     * @param array $rConfig
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeName
     * @return array
     */
    public function resizeProductImages($rConfig, Mage_Catalog_Model_Product $product, $attributeName) {
        $images = array();
        $helper = Mage::helper('catalog/image');

        foreach ($rConfig['breakpoints'] as $breakpoint) {
            $_width = intval($breakpoint * $rConfig['multiplier']);
            $_resizedImg = (string)$helper->init($product, $attributeName)->keepFrame(true)->resize($_width);

            if($rConfig['retina']) {
                $_resizedImg2x = (string)$helper->init($product, $attributeName)->keepFrame(true)->resize($_width * 2);
            }

            if ($_resizedImg) {
                $images[$breakpoint] = array( 'image_url' => $_resizedImg );
            }

            if (isset($_resizedImg2x)) {
                $images[$breakpoint]['image_url_2x'] = $_resizedImg2x;
            }
        }
        return $images;
    }

    /**
     * Retrieves responsive configuration for a specified width
     *
     * @param int $width
     * @return array
     */
    public function getResponsiveConfig($width)
    {
        $rConfig = array();

        $rConfig['breakpoints'] = Mage::getSingleton('respizr/config')->getRespizrBreakpoints();
        $rConfig['multiplier']  = $width / max($rConfig['breakpoints']);
        $rConfig['retina']      = Mage::getSingleton('respizr/config')->isRespizrRetina();

        return $rConfig;
    }

    /**
     * Returns image html
     *
     * @param string $imageUrl
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getImgHtml($imageUrl, $alt, $width, $height = null)
    {
        $image = $this->resizeImg($imageUrl, $width, $height);

        $imgBlock = Mage::app()->getLayout()->createBlock('core/template', '', array(
            'template'  => 'respizr/image.phtml',
            'alt'       => $alt,
            'image'     => $image,
        ));

        return $imgBlock->toHtml();
    }

}
