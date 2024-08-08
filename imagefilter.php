<?php
// ImageFilter is an Datenstrom Yellow extension for applying image filters.
// For more image filters, install the Yellow ImageFilterCollection extension.

class YellowImagefilter
{
    const VERSION = '0.9.6';

    public $yellow;  // access to API

    // Handle initialisation
    public function onLoad($yellow){
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("imageFilterDevMode", "0"); 
        $this->yellow->system->setDefault("imageFilterUseTitleTag", "0");
        $this->yellow->system->setDefault("imageFilterUseWebp", "1"); 
        $this->yellow->system->setDefault("imageFilterImageWebpQuality", "60"); 
        $this->yellow->system->setDefault("imageFilterImageJpegQuality", "80"); 
        $this->yellow->system->setDefault("imageFilterDefaultImfi", "imfi-original");
    }

    // Handle page content in HTML format
    public function onParseContentHtml($page, $text){
        $output = null;
        $callback = function ($matches) {

            $defaultFilter = strtolower($this->yellow->system->get("imageFilterDefaultImfi"));
            $defaultFilter = explode('-', $defaultFilter);
            $defaultFilter = preg_replace('/\s+/', '', $defaultFilter[1]);

            // Split »img-tag« and create the new one 
            preg_match('/<img src="(.*?)"/i', $matches[0], $srcMatches);
            preg_match('/width="(.*?)"/i', $matches[0], $widthMatches);
            preg_match('/height="(.*?)"/i', $matches[0], $heightMatches);
            preg_match('/alt="(.*?)"/i', $matches[0], $altMatches);
            preg_match('/class="(.*?)"/i', $matches[0], $classMatches);
            preg_match('/imfi-(.*?)\W/', $matches[0], $choosedFilter); 
            
            if(empty($widthMatches)){
                $widthMatches = "";
            }else{
                $widthMatches = $widthMatches[1];
            }

            if(empty($heightMatches)){
                $heightMatches = "";
            }else{
                $heightMatches = $heightMatches[1];
            }

            if(empty($altMatches)){
                $altMatches = "";
            }else{
                $altMatches = $altMatches[1];
            }

            if(empty($classMatches)){
                $classMatches = "";
            }else{
                $classMatches = $classMatches[1];
            }

            if (!empty($choosedFilter)){
                $choosedFilter = strtolower($choosedFilter[1]);
                $choosedFilter = preg_replace('/\s+/', '', $choosedFilter);
            } 
            
            // Original Link and Filename
            $srcOriginal = $srcMatches[1]; 
            $srcOriginalParts = explode('/', $srcMatches[1]); 
            $filenameOriginal = end($srcOriginalParts); 
            $filnameOriginalParts = explode('.', $filenameOriginal); 
            $srcOriginalInside = $this->yellow->lookup->findMediaDirectory('coreImageLocation') . $filenameOriginal; 
            $type = $filnameOriginalParts[1]; 

            if (empty($choosedFilter)) { 
                $choosedFilter = $defaultFilter;
                if ($defaultFilter == "original" && $this->yellow->system->get("imageFilterUseWebp") == 1){
                    $choosedFilter = "webp";
                } 
            }

            if ($choosedFilter == "original" || $type == "gif"){ 
                   $choosedFilter = "";
            }

            // New Link and Filename
            if ($this->yellow->system->get("imageFilterUseWebp") == 1 && $type != "gif"){ 
                $filenameNew = $filnameOriginalParts[0] . '-' . $choosedFilter . '.webp'; 
            }else{
                $filenameNew = $filnameOriginalParts[0] . '-' . $choosedFilter . '.' . $type; 
            }
   
            $pathNew = $this->yellow->system->get('coreServerBase') . '/' . $this->yellow->lookup->findMediaDirectory('coreImageLocation') . $choosedFilter . '/'; 
            $srcNew = $pathNew . $filenameNew; 
            $pathNewInside = $this->yellow->lookup->findMediaDirectory('coreImageLocation') . $choosedFilter . '/'; 
            $srcNewInside = $pathNewInside . $filenameNew; 

            // Generate Output
            if (!empty($choosedFilter)) {
                $filterAvailableInternal = null;
                $filterAvailableExternal = null;

                if (method_exists($this,$choosedFilter)) {
                    $filterAvailableInternal = true;
                }

                if ($this->yellow->extension->isExisting("imagefiltercollection")) {
                    if (method_exists($this->yellow->extension->get("imagefiltercollection"), $choosedFilter)) {
                        $filterAvailableExternal = true;
                    }
                }

                if ($filterAvailableInternal == true || $filterAvailableExternal == true) {

                    if (!is_dir($pathNewInside)) {
                        mkdir($pathNewInside);
                    }

                    if ($filterAvailableInternal == true) {
                        $this->generateNewImageInternal($choosedFilter, $srcOriginalInside, $srcNewInside, $type);
                    } elseif ($filterAvailableExternal == true) {
                            $this->generateNewImageExternal($choosedFilter, $srcOriginalInside, $srcNewInside, $type);
                    }   

                    $output = "<img src=\"$srcNew\" ";

                } else {
                    $output = "<img src=\"$srcOriginal\" ";
                }

            } else {
                $output = "<img src=\"$srcOriginal\" ";
            }

            $output .= " width=\"$widthMatches\"";
            $output .= " height=\"$heightMatches\"";
            $output .= " alt=\"$altMatches\"";
            if ($this->yellow->system->get("imageFilterUseTitleTag") == 1){
                $output .= " title=\"$altMatches\"";
            }
            if ($classMatches != ""){
                $output .= " class=\"$classMatches\"";
            }
            $output .= '>';
            return $output;
            
        };
        $output = preg_replace_callback('/<img(.*?)>/i', $callback, $text);  
        return $output;
    }

    // Generate the new Image Internal (this file)
    public function generateNewImageInternal($choosedFilter, $srcOriginal, $srcNewInside, $type)
    {
        if ($this->yellow->system->get("imageFilterDevMode") == 1) {
            $gernerateNewImage = false;
        } else {
            $gernerateNewImage = file_exists($srcNewInside);
        }

        if (!$gernerateNewImage && $type != "gif") {
            $image = $this->loadImage($srcOriginal, $type);
            call_user_func(array($this, $choosedFilter), $image);
            if ($this->yellow->system->get("imageFilterUseWebp") == 1){ 
                $this->saveImage($image, $srcNewInside, 'webp');
            } else {
                $this->saveImage($image, $srcNewInside, $type);
            }
        }
    }

    // Generate the new Image Externel (ImageFilterCollection extension)
    public function generateNewImageExternal($choosedFilter, $srcOriginal, $srcNewInside, $type)
    {
        if ($this->yellow->system->get("imageFilterDevMode") == 1) {
            $gernerateNewImage = false;
        } else {
            $gernerateNewImage = file_exists($srcNewInside);
        }

        if (!$gernerateNewImage && $type != "gif") {
            $image = $this->loadImage($srcOriginal, $type);
            call_user_func(array($this->yellow->extension->get("imagefiltercollection"), $choosedFilter), $image);
            if ($this->yellow->system->get("imageFilterUseWebp") == 1){ 
                $this->saveImage($image, $srcNewInside, 'webp');
            } else {
                $this->saveImage($image, $srcNewInside, $type);
            }
        }
    }

    // Load image from file
    public function loadImage($fileName, $type) {
        $image = false;
        switch ($type) {
            case "webp": $image = @imagecreatefromwebp($fileName); break; 
            case "jpeg": $image = @imagecreatefromjpeg($fileName); break;
            case "jpg": $image = @imagecreatefromjpeg($fileName); break;
            case "png": $image = @imagecreatefrompng($fileName); 
                        $background = imagecolorallocate($image , 0, 0, 0);
                        imagecolortransparent($image, $background);
                        imagealphablending($image, false);
                        imagesavealpha($image, true);
                        break;
        }
        return $image;
    }

    // Save image to file
    public function saveImage($image, $fileName, $type) {
        $ok = false;
        switch ($type) {
            case "webp": $ok = @imagewebp($image, $fileName, $this->yellow->system->get("imageFilterImageWebpQuality")); break; 
            case "jpeg": $ok = @imagejpeg($image, $fileName, $this->yellow->system->get("imageFilterImageJpegQuality")); break; 
            case "jpg": $ok = @imagejpeg($image, $fileName, $this->yellow->system->get("imageFilterImageJpegQuality")); break; 
            case "png": $ok = @imagepng($image, $fileName); break;
        }
        return $ok;
    }

    // Do nothing. Only a helper in 
    // »webp« Workflow
    public function webp($image){
        return $image;
    }

    // Contrast
    public function contrast($image){
        imagefilter($image, IMG_FILTER_CONTRAST, -20);
        return $image;
    }

    // Sharpen
    public function lowsharpen($image){
        $cornerPixel = -1;
        $middlePixel = -1.2;
        $centerPixel = 20;
        $offset = 0;
        $this->sharpenCalculation($image, $cornerPixel, $middlePixel, $centerPixel, $offset);
        return $image;
    }

    public function sharpen($image){
        $cornerPixel = -1;
        $middlePixel = -2;
        $centerPixel = 20;
        $offset = 0;
        $this->sharpenCalculation($image, $cornerPixel, $middlePixel, $centerPixel, $offset);
        return $image;
    }

    public function sharpenCalculation($image,  $cornerPixel, $middlePixel, $centerPixel, $offset){
        $sharpenMatrix = array([$cornerPixel, $middlePixel, $cornerPixel], [$middlePixel, $centerPixel, $middlePixel], [$cornerPixel, $middlePixel, $cornerPixel]);
        $divisor = array_sum(array_map('array_sum', $sharpenMatrix));  
        imageconvolution($image, $sharpenMatrix, $divisor, $offset);
        return $image;
    }
}
