<?php 

    /*
    $details = getFBUserDetails("100001672579934");
    print_r("Received RESPONSE: \n\n" . print_r($details, true));
    */
    
    function getFBUserDetails($fb_id) {
        $profile = file_get_contents("https://graph.facebook.com/$fb_id");
        return json_decode($profile, true);
    }
?>