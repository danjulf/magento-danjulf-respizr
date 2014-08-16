Danjulf_Respizr
===============
Responsive image resizer, a magento module to help develop responsive Magento themes. It allows resizing of any uploaded image by simply providing the url. It uses Andrea Verlicchis awesome [picturePolyfill](https://github.com/verlok/picturePolyfill) to provide responsive images.

Usage
-----
In your .phtml-files simply call the helper and provide an image url, description and size:
```
<?php echo $this->helper('respizr')->getPictureHtml('http://www.magentosite.com/media/sample.png', 'description', 200); ?>
```

This will return a picture element with one resized image and a retina-image per breakpoint (specified in System -> Configuration -> Respizr)

How about product images? Provide the product, attribute name and size:
```
<?php echo $this->helper('respizr')->getProductPictureHtml($_product, 'small_image', 200); ?>
```

Enjoy!