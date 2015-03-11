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
     * @param int|null $height
     * @return string|false
     */
    public function resizeImg($originalUrl, $width, $height = null)
    {
        if (empty($originalUrl) || empty($width)) {
            return false;
        }
        $paths = $this->getImgPathsFromUrl($originalUrl);
        if (!$paths) {
            return false;
        }
        $config = Mage::getSingleton('respizr/config');
        /* @var $config Danjulf_Respizr_Model_Config */
        $mediaPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $resizedRelativePath = $this->getResizedImgRelativePath(
            $paths['relative'], $width, $height
        );

        $resizedDirPath = $mediaPath . DS . $config->getRespizrDirName();
        if (!is_dir($resizedDirPath)) {
            mkdir($resizedDirPath, 0777, true);
        }

        $resizedImagePath = $resizedDirPath . DS . $resizedRelativePath;
        if (file_exists($paths['full']) && is_file($paths['full'])) {
            if (!file_exists($resizedImagePath)) {
                $image = new Varien_Image($paths['full']);
                $image = $this->addVarienImageOptions($image);
                $image->resize($width, $height);
                $image->save($resizedImagePath);
            }
            if (file_exists($resizedImagePath)) {
                $mediaUrl =
                    Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
                $resizedUrl = $mediaUrl . $config->getRespizrDirName() . DS
                    . $resizedRelativePath;
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
     * @return array|false
     */
    public function resizeImages($rConfig, $imageUrl)
    {
        if (empty($rConfig) || empty($imageUrl)) {
            return false;
        }
        $images = array();
        foreach ($rConfig['breakpoints'] as $breakpoint) {
            $_size = $this->calculateSize($breakpoint, $rConfig);
            $_resizedImg = $this->resizeImg(
                $imageUrl, $_size['width'], $_size['height']
            );
            if ($rConfig['retina']) {
                $_resizedImg2x = $this->resizeImg(
                    $imageUrl,
                    $_size['width'] * 2,
                    ($_size['height'] ? $_size['height'] * 2 : null)
                );
            }
            if ($_resizedImg) {
                $images[$breakpoint] = array('image_url' => $_resizedImg);
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
        $fileName =  $pathInfo['filename'] . '_' . $width;
        if (!empty($height)) {
            $fileName .= 'x' . $height;
        }
        $fileName .= '.' . $pathInfo['extension'];

        return $pathInfo['dirname'] . DS . $fileName;
    }

    /**
     * Gets image paths from Url
     *
     * @param string $imgUrl
     * @return string|false
     */
    public function getImgPathsFromUrl($imgUrl)
    {
        $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $relativeMediaUrl = '/' . Mage_Core_Model_Store::URL_TYPE_MEDIA . '/';
        $skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        $relativeSkinUrl = '/' . Mage_Core_Model_Store::URL_TYPE_SKIN . '/';
        $mediaPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $skinPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_SKIN);

        $possibleUrls = array(
            $mediaUrl           => $mediaPath,
            $relativeMediaUrl   => $mediaPath,
            $skinUrl            => $skinPath,
            $relativeSkinUrl    => $skinPath,
        );

        foreach ($possibleUrls as $url => $path) {
            if (strpos($imgUrl, $url) !== false) {
                $imgPath['full'] = str_replace($url, $path . DS, $imgUrl);
                $imgPath['relative'] = str_replace($url, '', $imgUrl);
                return $imgPath;
            }
        }
        return false;
    }

    /**
     * Returns picture html with one source per specified breakpoint
     *
     * @param string $imageUrl
     * @param string $alt
     * @param int $maxWidth
     * @param int|null $maxHeight
     * @param array $options
     * @return string|false
     */
    public function getPictureHtml($imageUrl, $alt, $maxWidth,
        $maxHeight = null, $options = null
    ) {
        if (!$imageUrl || !$maxWidth) {
            return false;
        }
        $rConfig =
            $this->getResponsiveConfig($maxWidth, $maxHeight, $options);
        $images = $this->resizeImages($rConfig, $imageUrl);

        return $this->preparePictureHtml($rConfig, $images, $alt);
    }

    /**
     * Returns picture html for a product image with one source per specified
     * breakpoint
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeName
     * @param int $maxWidth
     * @param int|null $maxHeight
     * @param array $options
     * @return string|false
     */
    public function getProductPictureHtml(Mage_Catalog_Model_Product $product,
        $attributeName, $maxWidth, $maxHeight = null, $options = null
    ) {
        if (!$product || !$attributeName || !$maxWidth) {
            return false;
        }
        $rConfig =
            $this->getResponsiveConfig($maxWidth, $maxHeight, $options);
        $images =
            $this->resizeProductImages($rConfig, $product, $attributeName);

        return $this->preparePictureHtml($rConfig, $images, '');
    }

    /**
     * Prepares picture data for output in template
     *
     * @param array $rConfig
     * @param array $images
     * @param string $alt
     * @return string|false
     */
    public function preparePictureHtml($rConfig, $images, $alt)
    {
        if (!$rConfig || !count($images) > 0) {
            return false;
        }
        $smallestImg = $images[
            $rConfig['breakpoints'][count($rConfig['breakpoints']) - 1]
        ];
        $largestImg = $images[$rConfig['breakpoints'][0]];
        $multiplier = $rConfig['multiplier'];

        $pictureHtml =
            Mage::app()->getLayout()->createBlock('core/template', '', array(
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
    public function resizeProductImages($rConfig,
        Mage_Catalog_Model_Product $product, $attributeName
    ) {
        $images = array();

        foreach ($rConfig['breakpoints'] as $breakpoint) {
            $_size = $this->calculateSize($breakpoint, $rConfig);
            $_resizedImg = $this->resizeProductImage(
                $product, $attributeName, $_size['width'], $_size['height']
            );
            if ($rConfig['retina']) {
                $_resizedImg2x = $this->resizeProductImage(
                    $product,
                    $attributeName,
                    $_size['width'] * 2,
                    ($_size['height'] ? $_size['height'] * 2 : null)
                );
            }
            if ($_resizedImg) {
                $images[$breakpoint] = array('image_url' => $_resizedImg);
            }
            if (isset($_resizedImg2x)) {
                $images[$breakpoint]['image_url_2x'] = $_resizedImg2x;
            }
        }
        return $images;
    }

    /**
     * Resize a product image using Mage_Catalog_Helper_Image
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeName
     * @param int $width
     * @param int|null $height
     * @param array|null $varienImageOverrides
     * @return string
     */
    public function resizeProductImage(Mage_Catalog_Model_Product $product,
        $attributeName, $width, $height = null, $varienImageOverrides = null
    ) {
        $helper = Mage::helper('catalog/image');
        /* @var $helper Mage_Catalog_Helper_Image */
        $resizedImage = $helper->init($product, $attributeName);
        $resizedImage = $this->addVarienImageOptions(
            $resizedImage, $varienImageOverrides);
        $resizedImage->resize($width, $height);

        return (string)$resizedImage;
    }

    /**
     * Add Varien Image options set in Respizr Config
     *
     * @param Varien_Image|Mage_Catalog_Helper_Image $image
     * @param array $overrides
     * @return Varien_Image|Mage_Catalog_Helper_Image $image
     */
    public function addVarienImageOptions($image, $overrides = null)
    {
        $config = Mage::getSingleton('respizr/config');
        /* @var $config Danjulf_Respizr_Model_Config */
        $viSettings = $config->getRespizrVarienImageSettings();

        if ($overrides && isset($overrides['quality'])) {
            if (Get_class($image) === 'Mage_Catalog_Helper_Image') {
                $image->setQuality(max(10, (int) $overrides['quality']));
            } else {
                $image->quality(max(10, (int) $overrides['quality']));
            }
        } else if (isset($viSettings['quality'])) {
            if (Get_class($image) === 'Mage_Catalog_Helper_Image') {
                $image->setQuality(max(10, (int) $viSettings['quality']));
            } else {
                $image->quality(max(10, (int) $viSettings['quality']));
            }
        }

        if ($overrides && $overrides['keep_transparency']) {
            $image->keepTransparency(!!$overrides['keep_transparency']);
        } else if (isset($viSettings['keep_transparency'])) {
            $image->keepTransparency(!!$viSettings['keep_transparency']);
        }

        if ($overrides && $overrides['keep_aspect_ratio']) {
            $image->keepTransparency(!!$overrides['keep_aspect_ratio']);
        } else if (isset($viSettings['keep_aspect_ratio'])) {
            $image->keepAspectRatio(!!$viSettings['keep_aspect_ratio']);
        }

        if ($overrides && $overrides['keep_frame']) {
            $image->keepTransparency(!!$overrides['keep_frame']);
        } else if (isset($viSettings['keep_frame'])) {
            $image->keepFrame(!!$viSettings['keep_frame']);
        }
        if ($overrides && $overrides['constrain_only']) {
            $image->keepTransparency(!!$overrides['constrain_only']);
        } else if (isset($viSettings['constrain_only'])) {
            $image->constrainOnly(!!$viSettings['constrain_only']);
        }

        if ($overrides && $overrides['background_color']) {
            $image->keepTransparency(!!$overrides['background_color']);
        } else if (isset($viSettings['background_color'])) {
            $image->backgroundColor($viSettings['background_color']);
        }

        return $image;
    }

    /**
     * Retrieves responsive configuration for a specified width
     *
     * @param int $width
     * @param int|null $height
     * @param array|null $options
     * @return array
     */
    public function getResponsiveConfig($width, $height = null,
        $options = null
    ) {
        $rConfig = array();
        $config = Mage::getSingleton('respizr/config');
        /* @var $config Danjulf_Respizr_Model_Config */
        $layoutCode = $this->getLayoutCode();

        $rConfig['breakpoints'] = $config->getRespizrBreakpoints();
        $rConfig['offsets']     = $config->getRespizrOffsets($layoutCode);
        $rConfig['multiplier']  = $width / max($rConfig['breakpoints']);
        $rConfig['retina']      = $config->isRespizrRetina();
        if ($height) {
            $rConfig['height_multiplier'] = $height / $width;
        }

        // Skip predifined breakpoints:
        if ($options && isset($options['skip'])) {
            foreach ($options['skip'] as $breakpoint) {
                $index = array_search($breakpoint, $rConfig['breakpoints']);
                if ($index !== false) {
                    array_splice($rConfig['breakpoints'], $index, 1);
                }
            }
        }

        // Override predifined breakpoints with inline options:
        if ($options && isset($options['overrides'])) {
            foreach ($options['overrides'] as $breakpoint => $offset) {
                if (!in_array($breakpoint, $rConfig['breakpoints'])) {
                    array_push($rConfig['breakpoints'], $breakpoint);
                    arsort($rConfig['breakpoints']);
                }
                $rConfig['offsets'][$breakpoint] = intval($offset);
            }
        }

        // Add absolute overrides
        if ($options && isset($options['absolute'])) {
            foreach ($options['absolute'] as $breakpoint => $offset) {
                if (!in_array($breakpoint, $rConfig['breakpoints'])) {
                    array_push($rConfig['breakpoints'], $breakpoint);
                    arsort($rConfig['breakpoints']);
                }
            }
            $rConfig['absolute'] = $options['absolute'];
        }

        return $rConfig;
    }

    /**
     * Calculate Image Size from breakpoint and responsive config
     *
     * @param int $breakpoint
     * @param array $rConfig
     * @return array $size
     */
    public function calculateSize($breakpoint, $rConfig)
    {
        $size = array();
        // Calculate height and width from responsive config
        $width = intval($breakpoint * $rConfig['multiplier']);
        $height = null;
        if (array_key_exists($breakpoint, $rConfig['offsets'])) {
            $width += intval($rConfig['offsets'][$breakpoint]);
        }
        if (isset($rConfig['height_multiplier'])) {
            $height = intval($_width * $rConfig['height_multiplier']);
        }
        // Override with absolute breakpoint
        if (isset($rConfig['absolute']) &&
                isset($rConfig['absolute'][$breakpoint])) {
            $width = $rConfig['absolute'][$breakpoint];
            $height = null;
        }
        $size['height'] = $height;
        $size['width'] = $width;
        return $size;
    }

    /**
     * Returns image html
     *
     * @param string $imageUrl
     * @param string $alt
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getImgHtml($imageUrl, $alt, $width, $height = null)
    {
        $image = $this->resizeImg($imageUrl, $width, $height);

        $imgBlock =
            Mage::app()->getLayout()->createBlock('core/template', '', array(
                'template'  => 'respizr/image.phtml',
                'alt'       => $alt,
                'image'     => $image,
            ));

        return $imgBlock->toHtml();
    }

    /**
     * Retrieve Pages Layout Code
     *
     * @return string|false
     */
    public function getLayoutCode()
    {
        $rootBlock = $this->getLayout()->getBlock('root');
        if (!$rootBlock) {
            return false;
        }
        $layoutTemplate = $rootBlock->getTemplate();
        $pageLayouts    = Mage::getSingleton('page/config')->getPageLayouts();

        foreach ($pageLayouts as $layout) {
            if ($layout->getTemplate() === $layoutTemplate) {
                return $layout->getCode();
                break;
            }
        }
        return false;
    }

}
