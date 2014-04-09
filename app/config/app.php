<?php 

    // I could not find a better way to maintain and use app configs. Using constants instead.
    function getAppConfig() {
        $appConfig = array(
            'images' => array(
                    'thumbnail' => array (
                                    'default_sizes' => array (600, 300, 150),
                                    'cdn_prefix' => "https://s3-us-west-2.amazonaws.com/marathimultiplex/images/thumbnail/"
                                    ),
                    'banner' => array (
                                    'default_sizes' => array(1000, 500),
                                    'cdn_prefix' => "https://s3-us-west-2.amazonaws.com/marathimultiplex/images/banner/"
                                    )
            )
        );
        
        return $appConfig;
    }
     

    class configs {
        public static $thumbnail_default_sizes = array (600, 300, 150);
        public static $banner_default_sizes = array(1000, 500);

        const THUMBNAIL_PREFIX = "https://s3-us-west-2.amazonaws.com/marathimultiplex/images/thumbnail/";
        const BANNER_PREFIX = "https://s3-us-west-2.amazonaws.com/marathimultiplex/images/banner/";
    }
?>