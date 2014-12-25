Danjulf_Respizr
===============
Responsive image resizer, a magento module to help develop responsive Magento themes. It allows resizing of any uploaded image by simply providing the url. It uses Andrea Verlicchis awesome [picturePolyfill](https://github.com/verlok/picturePolyfill) to provide responsive images.

Thanks to Aijko for help with the [image-chooser](https://github.com/aijko/aijko-widgetimagechooser) for the widget and thanks to henkelund for helping out.

If you are missing features or if you find any bugs, please let me know.

Enjoy!

What's new?
-----------
- Now works with relative Url:s
- Heights and height-calculation fixed

Usage
-----
In your .phtml-files simply call the helper and provide an image url, description and size:
```
<?php echo $this->helper('respizr')->getPictureHtml('http://www.magentosite.com/media/sample.png', 'description', 200); ?>
```

You can also include an array with inline-offsets from the rules you set in config:
```
<?php echo $this->helper('respizr')->getPictureHtml('http://www.magentosite.com/media/sample.png', 'description', 200, array('1024' => 100)); ?>
```

This will return a picture element with one resized image and a retina-image per breakpoint (specified in System -> Configuration -> Respizr)

How about product images? Provide the product, attribute name and size:
```
<?php echo $this->helper('respizr')->getProductPictureHtml($_product, 'small_image', 200); ?>
```

There is a widget, to be able to add pictures to cms-content as well.

Settings
--------
In System -> Configuration -> Respizr you can set your themes breakpoints and include offsets per page layout. You can also adjust settings for Varien Image (Quality, Keep Aspect Ratio, Background Color, Keep Frame, Keep Transparency, Constrain Only)

Upcoming Features
-----------------
- Ability to change product image type ('small_image', 'thumbnail', etc) per breakpoint for product images.
