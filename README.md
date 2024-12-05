# yellow-imageFilter
## ImageFilter

With »ImageFilter« it is possible to apply image filters to the images on the website. The original image will not be change. New images are created and stored in subfolders. A default filter can be specified in the »yellow-system.ini«

In addition, you can select in the »yellow-system.ini« whether the new images should be saved in the webp format. The quality of the webp images can also be set there. 

## Before using »ImageFilter«

»ImageFilter« must compress the new images, otherwise the image file will be too large. However, Datenstrom Yellow already compresses the images when they are uploaded to your web server. However, the value of the compression can be set in the »yellow-system.ini«. I recommend to set »ImageUploadJpegQuality« from »80« to »95« when using “ImageFilter”.

## How to use

* You select the filter as a »class« in your »img tag« with the identifier »imfi-«  
For example: [image your-image.jpg "alt text" "imfi-contrast and your style classes"]
* Use only one filter per »img tag«
* Make your settings in the »yellow-system.ini«
* »ImageFilter« only contains the filters »imfi-lowsharpen« , »imfi-sharpen« and »imfi-contrast«
* For more filters or your own filters take a look at »ImageFilterCollection«

### yellow-system.ini

* ImageFilterDevMode: 0 or 1 (0)
* ImageFilterUseTitleTag: 0 or 1 (0)
* ImageFilterUseWebp: 0 or 1 (1)
* ImageFilterImageWebpQuality: 0 - 100 (60)
* ImageFilterImageJpegQuality: 0 -100 (80)
* ImageFilterDefaultImfi: imfi-sharpen or imfi-yourFilter ... (imfi-original)

#### ImageFilterDevMode

The »ImageFilterDevMode« is helpful if you want to develop filters yourself. Without »ImageFilterDevMode«, »ImageFilter« checks whether an image already exists. When developing new filters, you must always see the effect directly. Therefore, the existing images are always overwritten in the »ImageFilterDevMode«.

#### ImageFilterUseTitleTag

Datenstrom Yellow uses the »title« for the »image tag«. Yellow uses the »alt« text here. With »ImageFilterUseTitleTag« you can decide whether the »title« should be used.

#### ImageFilterUseWebp

With »ImageFilterUseWebp« you can decide whether you want to use »WebP«. You can find more information about »WebP« here, for example:

* https://developers.google.com/speed/webp?hl=en

#### ImageFilterImageWebpQuality

The desired quality of the images in »WebP« format can be set here. The lower the number, the poorer the image quality and the smaller the image file. Please note that Datenstrom Yellow already compresses the images when they are uploaded to your web server. It is best to set »ImageUploadJpegQuality« from »80« to »95« if you are using »ImageFilter«.

#### ImageFilterImageJpegQuality

The desired quality of the images in »JPG/JPEG« format can be set here. The lower the number, the poorer the image quality and the smaller the image file. Please note that Datenstrom Yellow already compresses the images when they are uploaded to your web server. It is best to set »ImageUploadJpegQuality« from »80« to »95« if you are using »ImageFilter«.

#### ImageFilterDefaultImfi

A default filter can be defined here. This filter is then applied to all filters if no other filter is specified as a css class.

## Troubleshooting
If the »WebP« image is displayed as plain text on your website, this is probably due to the settings of the used web server.

Add the following lines to your `.htaccess`:

```
<IfModule mod_mime.c>
  # Media files
    AddType image/webp                                  webp
</IfModule>
```

I found this solution here: https://forum.getkirby.com/t/media-webp-files-shown-as-plain-text/30315/7


## Examples

|  -  | - | - |
| --- | --- | --- |
| <p><img src="01-vintage-rennrad.jpg" alt="original image"></p> | <p><img src="01-vintage-rennrad-sharpen.jpg" alt="sharpen filter"></p> | <p>sharpen filter</p> | 
| <p><img src="01-vintage-rennrad.jpg" alt="original image"></p> | <p><img src="01-vintage-rennrad-contrast.jpg" alt="contrast filter"></p> | <p>contrast filter</p> |


## ImageFilter, ImageFilterCollection and CatchImage

»ImageFilter« is a Datenstrom Yellow extension, for applying image filters and »WebP« conversation.
For more or own image filters, install and perhaps modify the Yellow »ImageFilterCollection« extension.
If you need images on your »blog-start.html«, have a look to the Yellow »CatchImage« extension.

»ImageFilter« is the main program. »ImageFilterCollection« and »CatchImage« are plugins for »ImageFilter«. »ImageFilter« is required for using »ImageFilterCollection« and »CatchImage«.

## Helpful links

* https://github.com/PetersOtto/yellow-ImageFilter
* https://github.com/PetersOtto/yellow-ImageFilterCollection
* https://github.com/PetersOtto/yellow-catchImage
* https://www.php.net/manual/de/ref.image.php
* https://www.php.net/manual/de/function.imagefilter.php
* https://developers.google.com/speed/webp?hl=en
* https://forum.getkirby.com/t/media-webp-files-shown-as-plain-text/30315/7


## Developer
PetersOtto. [Get help](https://datenstrom.se/yellow/help/)

Thx to: 
* [@GiovanniSalmeri](https://github.com/GiovanniSalmeri), for helping me with your `php` skills.
* [@pftnhr](https://github.com/pftnhr), for the [yellow-absoluteimage](https://github.com/pftnhr/yellow-absoluteimage) extension. The code of this extension gave me the decisive idea.
* [@datenstrom](https://github.com/datenstrom), for developing Yellow &#128512;
