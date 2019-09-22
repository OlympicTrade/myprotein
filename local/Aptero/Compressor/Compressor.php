<?php
namespace Aptero\Compressor;

use ApplicationAdmin\Model\Settings;
use Leafo\ScssPhp\Compiler;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

class Compressor {
    protected $baseDir = '';

    static public function getPublicLink($type, $platform = '') {
        $file = '/';

        if($platform) {
            $file .= $platform . '/';
        }

        $file .= $type . '/';

        $settings = Settings::getInstance();

        $verName = ($platform ? $platform . '_' : '') . $type;

        $version = $settings->get('css_js_version')->$verName;

        $file .= 'mini-' . $version . '.' . $type;

        return $file;
    }

    public function getFile($type, $platform, $new = false)
    {
        $file = PUBLIC_DIR . '/';

        if($platform) {
            $file .= $platform . '/';
        }

        $file .= $type . '/';

        $settings = Settings::getInstance();

        $verName = ($platform ? $platform . '_' : '') . $type;

        if(!$new && !$settings->get('css_js_version')->$verName) {
            return false;
        }

        if($new) {
            $version = date('YmdHis');
            $settings->get('css_js_version')->$verName = $version;
            $settings->save();
        } else {
            $version = $settings->get('css_js_version')->$verName;
        }

        $file .= 'mini-' . $version . '.' . $type;

        if (!$new && !file_exists($file)) {
            return false;
        }

        $this->removeOldFiles($type, $platform, $file);

        return $file;
    }

    public function removeOldFiles($type, $platform, $exclude = '')
    {
        $fileMask = PUBLIC_DIR . '/';

        if($platform) {
            $fileMask .= $platform . '/';
        }

        $fileMask .= $type . '/mini-*.' . $type;

        foreach (glob($fileMask) as $filename) {
            if($exclude && $exclude == $filename) {
                continue;
            }
            @unlink($filename);
        }
    }

    public function compress($files, $type, $platform = '')
    {
        $file = $this->getFile($type, $platform);

        if(!$file) {
            $this->updateFile($files, $platform, $type);
            return;
        }

        $miniTime = filemtime($file);
        foreach($files as $file) {
            if(filemtime($file) > $miniTime) {
                $this->updateFile($files, $platform, $type);
                return;
            }
        }
    }

    public function updateFile($files, $platform, $type)
    {
        $newFile = $this->getFile($type, $platform, true);

        /*if($type == 'css') {
            $scss = new Compiler();
            $content = $scss->compile($content);
        }*/

        $content = '';
        foreach($files as $file) {
            if((new \SplFileInfo($file))->getExtension() == 'scss') {
                $scss = new Compiler();
                $content .= $scss->compile(file_get_contents($file));
            } else {
                $content .= file_get_contents($file);
            }
        }

        /*if($type == 'css') {
            $scss = new Compiler();
            $content = $scss->compile($content);
        }*/

        $h = fopen($newFile, 'w');
        fwrite($h, $content);
        fclose($h);

        switch($type) {
            case 'css':
                $minifier = new CSS();
                break;
            case 'js':
                $minifier = new JS();
                break;
        }

        $minifier->add($content);
        $minifier->minify($newFile);
    }
}