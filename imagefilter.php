<?php
// ImageFilter is an Datenstrom Yellow extension for applying image filters.
// For more image filters, install the Yellow ImageFilterCollection extension.

class YellowImagefilter
{
    const VERSION = '0.9.4';

    public $yellow;  // access to API

    // Handle initialisation
    public function onLoad($yellow){
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("imageFilterUseTitleTag", "1");
        $this->yellow->system->setDefault("imageFilterDefaultImfi", "imfi-original");
    }

    // Handle page content in HTML format
    public function onParseContentHtml($page, $text){
        $output = null;
        $callback = function ($matches) {

            // Default Filter aufbereiten
            // $defaultFilter = $this->yellow->system->get("defaultImfi");
            $defaultFilter = strtolower($this->yellow->system->get("imageFilterDefaultImfi"));
            $defaultFilter = explode('-', $defaultFilter);
            $defaultFilter = preg_replace('/\s+/', '', $defaultFilter[1]);

            // img-Tag in Teile zerlegt, um daraus den Tag nachher neu aufzubauen.
            // Split »img-tag« and create the new one 
            preg_match('/<img src="(.*?)"/i', $matches[0], $srcMatches);
            preg_match('/width="(.*?)"/i', $matches[0], $widthMatches);
            preg_match('/height="(.*?)"/i', $matches[0], $heightMatches);
            preg_match('/alt="(.*?)"/i', $matches[0], $altMatches);
            preg_match('/class="(.*?)"/i', $matches[0], $classMatches);
            preg_match('/imfi-(.*?)\W/', $matches[0], $choosedFilter); 
            
            // Problem wenn Array nicht vorhanden.
            // Mache aus leerem Array eine leere Variable
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

            // Wenn Array leer ist, setze leeren String.
            // Ändere Filternamen zu Kleinbuchstaben.
            // Leerzeichen löschen.
            if (!empty($choosedFilter)){
                $choosedFilter = strtolower($choosedFilter[1]);
                $choosedFilter = preg_replace('/\s+/', '', $choosedFilter);
            } 
            
            if (empty($choosedFilter)) {
                $choosedFilter = $defaultFilter;
            }

            if ($choosedFilter == "original"){
                $choosedFilter = "";
            }

            // Original Link and Filename
            $srcOriginal = $srcMatches[1]; // Link auf Original Source mit Dateinamen und Endung ermittelt
            $srcOriginalParts = explode('/', $srcMatches[1]); // Original Source Pfad wird aufgeteilt 
            $filenameOriginal = end($srcOriginalParts); // Original Dateiname mit Endung ermittelt
            $filnameOriginalParts = explode('.', $filenameOriginal); // Original Dateiname wird in Name und Endung aufgeteilt
            $srcOriginalInside = $this->yellow->lookup->findMediaDirectory('coreImageLocation') . $filenameOriginal; // Interne Link zum Original Source (ohne »coreServerBase«) wird erstellt
            $type = $filnameOriginalParts[1]; // Dateiendung wier ermittelt

            // New Link and Filename
            $filenameNew = $filnameOriginalParts[0] . '-' . $choosedFilter . '.' . $type; // Neuer Dateiname mit Endung wird erstellt
            $pathNew = $this->yellow->system->get('coreServerBase') . '/' . $this->yellow->lookup->findMediaDirectory('coreImageLocation') . $choosedFilter . '/'; // Link zum Filter-Ordner ohne Dateinamen
            $srcNew = $pathNew . $filenameNew; // Neuer Link mit Dateinamen und Endung
            $pathNewInside = $this->yellow->lookup->findMediaDirectory('coreImageLocation') . $choosedFilter . '/'; // Neuer Interner Pfad
            $srcNewInside = $pathNewInside . $filenameNew; // Neuer Interner Link zur Source mit Dateinamen und Endung erstellen

            // Wenn ein Filter angegeben wurde, wird in dieser Datei und in »ImageFilterCollection« kontrolliert, ob die Funktion vorhanden ist.  
            // Wenn noch kein Ordner existiert, dann wird eine neuer Ordner und das neue Bild angelegt.
            // Wenn nicht, bleibt alles beim alten. 
        
            // Generate Output
            if (!empty($choosedFilter)) {
                $filterAvailableInternal = null;
                $filterAvailableExternal = null;

                if (method_exists($this,$choosedFilter)) {
                    $filterAvailableInternal = true;
                }

                if ($this->yellow->extension->isExisting("imagefiltercollection")) {
                    if (method_exists($this->yellow->extension->get("imagefiltercollection"),$choosedFilter)) {
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
            if ($this->yellow->system->get("imageFilterUseTitleTag") == "1"){
                $output .= " title=\"$altMatches\"";
            }
            if ($classMatches != ""){
                $output .= " class=\"$classMatches\"";
            }
            $output .= '>';
            return $output;
            
        };
        // Es wird nach »"/<img src=\"(.*?)\"/i"« in »$text« gesucht und durch »$output« ($callback) ersetzt!
        $output = preg_replace_callback('/<img(.*?)>/i', $callback, $text);  
        return $output;
    }

    // Generate the new Image Internal (this file)
    public function generateNewImageInternal($choosedFilter, $srcOriginal, $srcNewInside, $type)
    {
        if (!file_exists($srcNewInside)) {
            $image = $this->loadImage($srcOriginal, $type);
            call_user_func(array($this, $choosedFilter), $image);;
            $this->saveImage($image, $srcNewInside, $type);
        }
    }

    // Generate the new Image Externel (ImageFilterCollection extension)
    public function generateNewImageExternal($choosedFilter, $srcOriginal, $srcNewInside, $type)
    {
        if (!file_exists($srcNewInside)) {
            $image = $this->loadImage($srcOriginal, $type);
            call_user_func(array($this->yellow->extension->get("imagefiltercollection"), $choosedFilter), $image);
            $this->saveImage($image, $srcNewInside, $type);
        }
    }

    // Load image from file
    public function loadImage($fileName, $type) {
        $image = false;
        switch ($type) {
            case "gif": $image = @imagecreatefromgif($fileName); break;
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
            case "gif": $ok = @imagegif($image, $fileName); break;
            case "jpeg": $ok = @imagejpeg($image, $fileName); break;
            case "jpg": $ok = @imagejpeg($image, $fileName); break;
            case "png": $ok = @imagepng($image, $fileName); break;
        }
        return $ok;
    }

    // Sharpen
    public function sharpen($image){
        $sharpen = array([0, -2, 0], [-2, 11, -2], [0, -2, 0]);
        imageconvolution($image, $sharpen, 3, 0);
        return $image;
    }

    // Contrast
    public function contrast($image){
        imagefilter($image, IMG_FILTER_CONTRAST, -20);
        return $image;
    }
}
