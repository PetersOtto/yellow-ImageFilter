<?php
// »ImageFilter« is a Datenstrom Yellow extension, for applying image filters and »webp« conversation.
// For more or own image filters, install and perhaps modify the Yellow »ImageFilterCollection« extension.
// If you need images on your »blog-start.html«, have a look to the Yellow »CatchImage« extension.


class YellowImagefilter
{
    const VERSION = '0.9.7';

    public $yellow;  // access to API

    // Handle initialisation
    public function onLoad($yellow){
        $this->yellow = $yellow;
        $this->yellow->system->setDefault('imageFilterDevMode', '0'); 
        $this->yellow->system->setDefault('imageFilterUseTitleTag', '0');
        $this->yellow->system->setDefault('imageFilterUseWebp', '1'); 
        $this->yellow->system->setDefault('imageFilterImageWebpQuality', '60'); 
        $this->yellow->system->setDefault('imageFilterImageJpegQuality', '80'); 
        $this->yellow->system->setDefault('imageFilterDefaultImfi', 'imfi-original');
    }

    // Start with main method
    public function onParseContentHtml($page, $text){
        $output = null;
        $callback = function ($matches) {

            // Split the »img-tag« 
            preg_match('/<img src="(.*?)"/i', $matches[0], $srcMatches);
            preg_match('/width="(.*?)"/i', $matches[0], $widthMatches);
            preg_match('/height="(.*?)"/i', $matches[0], $heightMatches);
            preg_match('/alt="(.*?)"/i', $matches[0], $altMatches);
            preg_match('/class="(.*?)"/i', $matches[0], $classMatches);
            preg_match('/imfi-(.*?)\W/', $matches[0], $classFilter); 

            // Filter
            $defaultFilter = $this->getFilter($this->yellow->system->get('imageFilterDefaultImfi'));
            $classFilter = $this->setMatchesToAttribute($classFilter, 1);
            $classFilter = $this->getFilter($classFilter);
            $choosedFilter = null;

            // Set matches to img attribute
            $widthAttribute = $this->setMatchesToAttribute($widthMatches, 1);
            $heightAttribute = $this->setMatchesToAttribute($heightMatches, 1);
            $altAttribute = $this->setMatchesToAttribute($altMatches, 1);
            $classAttribute = $this->setMatchesToAttribute($classMatches, 1);

            // Original link and filename
            $srcOriginal = $srcMatches[1]; 
            $srcOriginalParts = explode('/', $srcMatches[1]); 
            $filenameOriginal = end($srcOriginalParts); 
            $filnameOriginalParts = explode('.', $filenameOriginal); 
            $srcOriginalInside = $this->yellow->lookup->findMediaDirectory('coreImageLocation') . $filenameOriginal; 
            $originalType = strtolower($filnameOriginalParts[1]); 
            $isTypeAllowed = $this->checkIfTypeIsAllowed($originalType);
            $useWebp = $this->yellow->system->get('imageFilterUseWebp');
            $isClassFilterOriginal = null;

            if ($classFilter === 'original'){
                $isClassFilterOriginal = true;
            }

            if ($classFilter === 'webp' || $classFilter === 'original' || empty($classFilter)){
                $classFilter = '';
            }
            if ($defaultFilter === 'webp' || $defaultFilter === 'original' || empty($defaultFilter)){
                $defaultFilter = '';
            }

            // Check if filter are available
            $isFilterClassAvailableExternal = $this->checkIfFilterIsAvailableExternal($classFilter); 
            $isFilterClassAvailableInternal = $this->checkIfFilterIsAvailableInternal($classFilter);
            $isFilterClassAvailable = $this->checkIfFilterDefaultIsAvailable($classFilter);

            $isFilterDefaultAvailableExternal = $this->checkIfFilterIsAvailableExternal($defaultFilter);
            $isFilterDefaultAvailableInternal = $this->checkIfFilterIsAvailableInternal($defaultFilter);
            $isFilterDefaultAvailable = $this->checkIfFilterDefaultIsAvailable($defaultFilter);

            $isFilterAvailableInternal = null;

            $isFilterAvailable = $this->checkIfFilterIsAvailable($isFilterClassAvailableExternal, $isFilterClassAvailableInternal, $isFilterDefaultAvailable); 
            
            // Find out what filter is the right filter
            if ($isFilterClassAvailable === true){
                $choosedFilter = $classFilter;
            }
                        
            if ($isFilterClassAvailable === false && $isFilterDefaultAvailable === true){ 
                $choosedFilter = $defaultFilter;
            }

            if ($isFilterAvailable === false){
                $choosedFilter = '';
            }

            if ($isClassFilterOriginal === true){
                $choosedFilter = '';
            }

            // Use »webp« if selected in »system.ini«
            if (empty($choosedFilter) && $useWebp == 1 && $isClassFilterOriginal !== true){ 
                $choosedFilter = 'webp';
                $isFilterAvailable = true;
                $isFilterAvailableInternal = true;
            }

            // New link and filename
            if ($useWebp == 1 && $isTypeAllowed === true){ 
                $filenameNew = $filnameOriginalParts[0] . '-' . $choosedFilter . '.webp'; 
            }else{
                $filenameNew = $filnameOriginalParts[0] . '-' . $choosedFilter . '.' . $originalType; 
            }

            $pathNew = $this->yellow->system->get('coreServerBase') . '/' . $this->yellow->lookup->findMediaDirectory('coreImageLocation') . $choosedFilter . '/'; 
            $srcNew = $pathNew . $filenameNew; 
            $pathNewInside = $this->yellow->lookup->findMediaDirectory('coreImageLocation') . $choosedFilter . '/'; 
            $srcNewInside = $pathNewInside . $filenameNew; 

            // Generate output
            if (!empty($choosedFilter)) {

                if ($isFilterAvailable === true && $isTypeAllowed === true) {

                    if (!is_dir($pathNewInside)) {
                        mkdir($pathNewInside);
                    }
                    
                    if ($isFilterClassAvailableInternal === true || $isFilterDefaultAvailableInternal === true || $isFilterAvailableInternal === true) {
                        $this->generateNewImageInternal($choosedFilter, $srcOriginalInside, $srcNewInside, $originalType, $isTypeAllowed);  // *******************
                    } elseif ($isFilterClassAvailableExternal === true || $isFilterDefaultAvailableExternal === true) {
                        $this->generateNewImageExternal($choosedFilter, $srcOriginalInside, $srcNewInside, $originalType, $isTypeAllowed); // *******************
                    }   

                    $output = '<img src="' . $srcNew . '"';

                } else {
                    $output = '<img src="' . $srcOriginal . '"';
                }

            } else {
                $output = '<img src="' . $srcOriginal . '"';
            }
            if ($originalType != 'svg' && $isTypeAllowed === true){
                $output .= ' width="' . $widthAttribute . '"';
                $output .= ' height="' . $heightAttribute . '"';
            }
            $output .= ' alt="' . $altAttribute . '"';
            if ($this->yellow->system->get("imageFilterUseTitleTag") == 1){
                $output .= ' title="' . $altAttribute . '"';
            }
            if ($classAttribute != ""){
                $output .= ' class="' . $classAttribute . '"';
            }
            $output .= '>';
            return $output;
            
        };
        $output = preg_replace_callback('/<img(.*?)>/i', $callback, $text);  
        return $output;
    }

    // Check if filter is available 
    public function checkIfFilterIsAvailable($isFilterClassAvailableExternal, $isFilterClassAvailableInternal, $isFilterDefaultAvailable)
    {
        if ($isFilterClassAvailableExternal || $isFilterClassAvailableInternal || $isFilterDefaultAvailable){
            $isFilterAvailable = true;
        } else {
            $isFilterAvailable = false;
        }
        return $isFilterAvailable;
    }

    // Check if default filter is available 
    public function checkIfFilterDefaultIsAvailable($toCheckFilter)
    {
        $isFilterDefaultAvailableExternal = $this->checkIfFilterIsAvailableExternal($toCheckFilter); 
        $isFilterDefaultAvailableInternal = $this->checkIfFilterIsAvailableInternal($toCheckFilter);

        if (($isFilterDefaultAvailableExternal || $isFilterDefaultAvailableInternal)) {
            $isFilterAvailable = true;
        } else {
            $isFilterAvailable = false;
        }
        return $isFilterAvailable;
    }

    // Check if »classFilter« is available 
    public function checkIfFilterClassIsAvailable($toCheckFilter)
    {
        $isFilterClassAvailableExternal = $this->checkIfFilterIsAvailableExternal($toCheckFilter); 
        $isFilterClassAvailableInternal = $this->checkIfFilterIsAvailableInternal($toCheckFilter);

        if (($isFilterClassAvailableExternal || $isFilterClassAvailableInternal)) {
            $isFilterAvailable = true;
        } else {
            $isFilterAvailable = false;
        }
        return $isFilterAvailable;
    }

    // Check if the extension »imageFilter« exist and if the filter is available in »imagefilter.php«
    public function checkIfFilterIsAvailableInternal($toCheckFilter)
    {
        $isFilterAvailableInternal = method_exists($this, $toCheckFilter);

        if ($isFilterAvailableInternal !== true) { // ===
            $isFilterAvailableInternal = false;
        } 
        return $isFilterAvailableInternal;
    }
    
    // Check if the extension »imageFillterCollection« exist and if the filter is available in »imagefiltercollection.php«
    public function checkIfFilterIsAvailableExternal($toCheckFilter)
    {
        if ($this->yellow->extension->isExisting('imagefiltercollection')) {
            $isFilterAvailableExternal = method_exists($this->yellow->extension->get('imagefiltercollection'), $toCheckFilter);
        } else {
            $isFilterAvailableExternal = false;
        }
        return $isFilterAvailableExternal;
    }

    // Generate the new image internal (this file)
    public function generateNewImageInternal($choosedFilter, $srcOriginal, $srcNewInside, $originalType, $isTypeAllowed)
    {
        if ($this->yellow->system->get('imageFilterDevMode') == 1) {
            $gernerateNewImage = false;
        } else {
            $gernerateNewImage = file_exists($srcNewInside);
        }

        if (!$gernerateNewImage && $isTypeAllowed === true) {
            $image = $this->loadImage($srcOriginal, $originalType);
            call_user_func(array($this, $choosedFilter), $image);
            if ($this->yellow->system->get('imageFilterUseWebp') == 1){ 
                $this->saveImage($image, $srcNewInside, 'webp');
            } else {
                $this->saveImage($image, $srcNewInside, $originalType);
            }
        }
    }

    // Generate the new image externel (ImageFilterCollection extension)
    public function generateNewImageExternal($choosedFilter, $srcOriginal, $srcNewInside, $originalType, $isTypeAllowed)
    {
        if ($this->yellow->system->get('imageFilterDevMode') == 1) {
            $gernerateNewImage = false;
        } else {
            $gernerateNewImage = file_exists($srcNewInside);
        }

        if (!$gernerateNewImage && $isTypeAllowed === true) {
            $image = $this->loadImage($srcOriginal, $originalType);
            call_user_func(array($this->yellow->extension->get('imagefiltercollection'), $choosedFilter), $image);
            if ($this->yellow->system->get('imageFilterUseWebp') == 1){ 
                $this->saveImage($image, $srcNewInside, 'webp');
            } else {
                $this->saveImage($image, $srcNewInside, $originalType);
            }
        }
    }

    // Load image from file
    public function loadImage($fileName, $originalType) {
        $image = false;
        switch ($originalType) {
            case 'webp': $image = @imagecreatefromwebp($fileName); break; 
            case 'jpeg': $image = @imagecreatefromjpeg($fileName); break;
            case 'jpg': $image = @imagecreatefromjpeg($fileName); break;
            case 'png': $image = @imagecreatefrompng($fileName); 
                        $background = imagecolorallocate($image , 0, 0, 0);
                        imagecolortransparent($image, $background);
                        imagealphablending($image, false);
                        imagesavealpha($image, true);
                        break;
        }
        return $image;
    }

    // Save image to file
    public function saveImage($image, $fileName, $originalType) {
        $ok = false;
        switch ($originalType) {
            case 'webp': $ok = @imagewebp($image, $fileName, $this->yellow->system->get('imageFilterImageWebpQuality')); break; 
            case 'jpeg': $ok = @imagejpeg($image, $fileName, $this->yellow->system->get('imageFilterImageJpegQuality')); break; 
            case 'jpg': $ok = @imagejpeg($image, $fileName, $this->yellow->system->get('imageFilterImageJpegQuality')); break; 
            case 'png': $ok = @imagepng($image, $fileName); break;
        }
        return $ok;
    }

    // Check if filter contain »imfi« and return filter without »imfi«
    public function getFilter($toCheckImageFilter)
    {
        if ((strpos($toCheckImageFilter, 'imfi') !== false)) {
            $toCheckImageFilter = strtolower($toCheckImageFilter);
            $toCheckImageFilter = explode('-', $toCheckImageFilter);
            $toCheckImageFilter = preg_replace('/\s+/', '', $toCheckImageFilter[1]);
        } else {
            $toCheckImageFilter = strtolower($toCheckImageFilter);
            $toCheckImageFilter = trim($toCheckImageFilter);
        }
        return $toCheckImageFilter;
    }

    // Put the »preg_match« image matches into the attribut variables for the new image tag
    public function setMatchesToAttribute($matches, $key)
    {
        if(empty($matches)){
            $attribute = '';
        }else{
            $attribute = $matches[$key];
        }
        return $attribute;
    }

    // Check if image type is allowed
    public function checkIfTypeIsAllowed($toCheckType)
    {
        $allowedImageTypes = 'png, webp, jpeg, jpg';
        $checkTypeResult = strpos($allowedImageTypes, $toCheckType);
            if ($checkTypeResult !== false){
                $checkTypeResult = true;
            } else{
                $checkTypeResult = false;
            }
        return $checkTypeResult;
    }

    // Do nothing. Only a helper in »webp« Workflow
    public function webp($image){
        return $image;
    }

    // Contrast filter
    public function contrast($image){
        imagefilter($image, IMG_FILTER_CONTRAST, -20);
        return $image;
    }

    // Sharpen filter
    // Sharpen
    public function lowsharpen($image){
        $cornerPixel = -1;
        $middlePixel = -1.2;
        $centerPixel = 20;
        $offset = 0;
        $this->sharpenCalculation($image, $cornerPixel, $middlePixel, $centerPixel, $offset);
        return $image;
    }

    // Low sharpen
    public function sharpen($image){
        $cornerPixel = -1.5;
        $middlePixel = -3;
        $centerPixel = 25;
        $offset = 0;
        $this->sharpenCalculation($image, $cornerPixel, $middlePixel, $centerPixel, $offset);
        return $image;
    }

    // Calculation for sharpen filters
    public function sharpenCalculation($image,  $cornerPixel, $middlePixel, $centerPixel, $offset){
        $sharpenMatrix = array([$cornerPixel, $middlePixel, $cornerPixel], [$middlePixel, $centerPixel, $middlePixel], [$cornerPixel, $middlePixel, $cornerPixel]);
        $divisor = array_sum(array_map('array_sum', $sharpenMatrix));  
        imageconvolution($image, $sharpenMatrix, $divisor, $offset);
        return $image;
    }
}
