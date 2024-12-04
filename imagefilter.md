# ImageFilter, ImageFilterCollection and CatchImage

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

----
## ImageFilter
----

With »ImageFilter« it is possible to apply image filters to the images on the website. The original image will not be change. New images are created and stored in subfolders. A default filter can be specified in the »yellow-system.ini«

In addition, you can select in the »yellow-system.ini« whether the new images should be saved in the webp format. The quality of the webp images can also be set there. 

## Before using »ImageFilter«

»ImageFilter« must compress the new images, otherwise the image file will be too large. However, Datenstrom Yellow already compresses the images when they are uploaded to your web server. However, the value of the compression can be set in the »yellow-system.ini«. I recommend to set “ImageUploadJpegQuality” from “80” to “95” when using “ImageFilter”.

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

### Troubleshooting
If the »WebP« image is displayed as plain text on your website, this is probably due to the settings of the used web server.

Add the following lines to your `.htaccess`:

```
<IfModule mod_mime.c>
  # Media files
    AddType image/webp                                  webp
</IfModule>
```

I found this solution here: https://forum.getkirby.com/t/media-webp-files-shown-as-plain-text/30315/7

### Changelog

04.12.2024
* Clean up the source code

----
## ImageFilterCollection
----

»ImageFilterCollection« is an plugin for ImageFilter. »ImageFilterCollection« offers space for your own filters and contains four example filters. These examples show how a filter is constructed. Unless absolutely necessary, I will not update »ImageFilterCollection«. This way your own filters are safe in »ImageFilterCollection«. 

»ImageFilter« is required.

### How to use

Have a look to »ImageFilter«
* https://github.com/PetersOtto/yellow-ImageFilter

»ImageFilterCollection« contains the following four filters:

* imfi-beach --> beach filter
* imfi-beachvi --> beach filter with vignette
* imfi-bw --> black and white filter
* imfi-bwvi --> black and white filter with vignette

These filters can be used, but are actually intended as an aid when developing your own filters.

### Your own filter

The structure is as follows:

```
public function yourFilterName($image){
    
    *** insert the commands here ***
    *** https://www.php.net/manual/de/ref.image.php ***
    
    return $image;
}
```

### Changelog

04.12.2024
* Clean up the source code

----
## CatchImage
----

The extension is named »CatchImage« because these images should catch the user and bring them to the post.

Datenstrom Yellow shows the title of the post and a small excerpt of the post on the start page of the blog »start-blog.html«.
If you want to create a portfolio page, you often need an image on the start page of the blog »start-blog.html«.
This can be implemented with »CatchImage«. In addition, the full range of functions of »ImageFilter« can be used. 

»ImageFilter« is required.

### How to use

You should customize the layout file `start-blog.html` with css after installation. Basic css knowledge is required.  
In the easiest case change the code of your `start-blog.html` like the following example. Insert the following code where the images should appear: 

```
<?php 
  $baseUrl = $page->getBase($multiLanguage = false); 
  $filenametype = $page->get("Catchimage"); 
  $catchImageAltText = $page->get("catchimagealttext"); 
  $catchImageFilter = $page->get("catchimagefilter"); 
  $catchImageTitle = $page->getHtml("titleContent"); 
  echo $this->yellow->extension->get("catchimage")->getCatchImage($filenametype, $baseUrl, $catchImageAltText, $catchImageFilter, $catchImageTitle)
?>
```

With the standard stockholm theme, it could look like this:

```
More Code above
<div class="<?php echo $page->getHtml("entryClass") ?>">
<?php 
  $baseUrl = $page->getBase($multiLanguage = false);
  $filenametype = $page->get("Catchimage"); 
  $catchImageAltText = $page->get("catchimagealttext"); 
  $catchImageFilter = $page->get("catchimagefilter");
  $catchImageTitle = $page->getHtml("titleContent");
  echo $this->yellow->extension->get("catchimage")->getCatchImage($filenametype, $baseUrl, $catchImageAltText, $catchImageFilter, $catchImageTitle);
?>
</div>
More code below

```

Complete the header of your blog posts with the following lines:
```
Catchimage: your-image.jpg
Catchimagealttext: image alt text
Catchimagefilter: imfi-lowsharpen

```

With the standard stockholm theme, it could look like this:

```
---
Title: Blog example page
Published: 2020-04-07
Author: Datenstrom
Layout: blog
Tag: Example
Catchimage: photo.jpg
Catchimagealttext: Desk with computer, cell phone, coffee, notebook and pencil case.
Catchimagefilter: imfi-lowsharpen
---
```

Now you should customize the layout file (start-blog.html) with css. For example, you could start with the following code. Put it into the `stockholm.css`. But remember, this is really just a mini example!

```
.main{
    flex-wrap: wrap;
    display: flex;
}

.entry{
    width: calc(50% - 0.4rem);
    padding: 0 0.2rem 0 0.2rem;
    
}
```

Have a look to »ImageFilter«
* https://github.com/PetersOtto/yellow-ImageFilter

### Changelog

04.12.2024
* Clean up the source code

----