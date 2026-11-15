<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Kk_file_types
{
    public static function coders()
    {
        return array('image/jpeg'=>'JPEG','image/png'=>'PNG','image/webp'=>'WEBP','image/heic'=>'HEIC','image/heif'=>'HEIC','image/heic-sequence'=>'HEIC','image/heif-sequence'=>'HEIC','image/avif'=>'AVIF','image/bmp'=>'BMP','image/x-ms-bmp'=>'BMP','image/x-bmp'=>'BMP','image/tiff'=>'TIFF','image/gif'=>'GIF','image/jp2'=>'JP2','image/jxl'=>'JXL');
    }
    public static function detect($path)
    {
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($path);
        // Older libmagic may not identify HEIF/AVIF containers. Check file bytes,
        // never trust a browser MIME or a filename extension.
        if($mime==='application/octet-stream') {
            $head=file_get_contents($path,FALSE,NULL,0,64);
            if(substr($head,4,4)==='ftyp') {
                $brands=str_split(substr($head,8),4);
                if(array_intersect($brands,array('avif','avis'))) $mime='image/avif';
                elseif(array_intersect($brands,array('heic','heix','hevc','hevx','mif1','msf1'))) $mime='image/heif';
            }
        }
        if($mime!=='application/pdf' && !isset(self::coders()[$mime])) throw new RuntimeException('Format tidak didukung. Gunakan PDF, JPG, PNG, WEBP, HEIC/HEIF, AVIF, BMP, TIFF, GIF, JP2, atau JXL.');
        return $mime;
    }
}
