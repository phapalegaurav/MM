<?php

    class Image extends BaseController {
        
    
        public static function uploadToCDN($originalFileName, ) {

            $fileext = explode(".",$originalFileName);
            $fileextension = $fileext[count($fileext)-1];
            $fileName = uniqid(rand(),true) . "." . $fileextension;
        }