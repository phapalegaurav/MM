<?php 
    require_once __DIR__.'/Error.php';
    require_once __DIR__.'/BaseController.php';
    

    class FeaturedContent extends BaseController {
        public static function getFeaturedContent($list_name) {
            $app = \Slim\Slim::getInstance();
            $response = $app->response();
            
            $featured_list_bean = R::findOne('featured_list', 'name=?', array($list_name));
            if($featured_list_bean == null) {
                $response->status(404);
                $response->body(json_encode(Error::getErrorArray(ErrorCode::FeaturedContentListDoesNotExist, ErrorMessage::FeaturedContentListDoesNotExist)));
            } else {
                $featured_lists = R::exportAll($featured_list_bean);
                $featured_list = $featured_lists[0];
                
                $count = $user->systemId = $app->request()->get('count');
                error_log("Count: $count");
                if(!isset($count)) {
                    $count = 20;
                }
                
                $featured_content_beans = R::find(
                                            'featured_content', 
                                            'featuredListId = :feauredListId ORDER BY position LIMIT :limit', 
                                            array(
                                                ':feauredListId' => $featured_list['id'],
                                                ':limit' => intval($count)
                                            )
                                       );

                $featured_content_arr = array();
                $featured_content = R::exportAll($featured_content_beans);
                foreach($featured_content as $content) {
                    $contentId = $content['contentId'];
                    $content = Content::getContentByIdInternal($contentId);
                    if($content != null) {
                        $featured_content_arr[] = $content;
                    }
                }
                
                $featured_content_arr = array('featured_content' => $featured_content_arr);
                $response->body(json_encode($featured_content_arr));
            }
        }
    }
?>