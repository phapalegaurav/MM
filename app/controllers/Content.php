<?php 
    require_once __DIR__.'/Error.php';
    require_once __DIR__.'/BaseController.php';
    require_once __DIR__.'/../config/app.php';
    
    const MEDIA_PREFIX = "rtmp://54.203.40.238/vod/mp4:";
    //const MEDIA_PREFIX = "https://s3-us-west-2.amazonaws.com/marathimultiplex/media/";

    class Content extends BaseController {
        
        public static function getContentById($id) {
            $app = \Slim\Slim::getInstance();
            $response = $app->response();
            
            $content = Content::getContentByIdInternal($id);
            if($content == null) {
                $response->status(404);
                $response->body(json_encode(Error::getErrorArray(ErrorCode::ContentDoesNotExist, ErrorMessage::ContentDoesNotExist)));
            } else {
                $content = array('content' => $content);
                $response->body(json_encode($content));
                return $content;                            //
            }
        }
        
        public static function getContentByIdInternal($id) {
            
            error_log("getting content for id: $id");
            $content_bean = R::findOne('content', 'id=?', array($id));
            if($content_bean == null) {
                return null;
            } else {
                $contents = R::exportAll($content_bean);
                $content = $contents[0];
                
                $entity_id = $content['entityId'];
                $entity_bean = R::findOne('entity', 'id=?', array($entity_id));
                $entities = R::exportAll($entity_bean);
                $entity = $entities[0];
                
                $entity['id'] = intval($content['id']);        // Setting content Id
                // If tags are set for this content, use it.
                if(isset($entity['ownTags'])) {
                    $entity_tags = $entity['ownTags'][0];
                    $entity['description'] = $entity_tags['description'];
                    unset($entity['ownTags']);
                }
                
                // Add artists
                $artists = R::getAll( 'select ea.role as role, a.name from entity_artist ea, artist a where ea.artistId = a.id and ea.entityId = :entityId',
                    array(':entityId'=> $entity_id)
                );
                $entity['artists'] = array();
                if(isset($artists) && !empty($artists)) {
                    foreach($artists as $artist) {
                        $role = $artist['role'];
                        $entity['artists'][$role][] = $artist['name'];
                    }
                }
                
                // Add images
                $appConfig = getAppConfig();
                $banners = array();
                $thumbnails = array();
                
                if(isset($entity['banner'])) {
                    
                    foreach($appConfig['images']['banner']['default_sizes'] as $size) {
                        $banner = $appConfig['images']['banner']['cdn_prefix'] . $size . "/" . $entity['banner'];
                        $banners[$size] = $banner; 
                    }
                    
                } else {
                    foreach($appConfig['images']['banner']['default_sizes'] as $size) {
                        $banner = $appConfig['images']['banner']['cdn_prefix'] . $size . "/" . "default.png";
                        $banners[$size] = $banner;
                    }
                }
                $entity['banners'] = $banners;
                
                if(isset($entity['thumbnail'])) {
                    foreach($appConfig['images']['thumbnail']['default_sizes'] as $size) {
                        $thumbnail = $appConfig['images']['thumbnail']['cdn_prefix'] . $size . "/" . $entity['thumbnail'];
                        $thumbnails[$size] = $thumbnail;
                    }
                } else {
                    foreach($appConfig['images']['thumbnail']['default_sizes'] as $size) {
                        $thumbnail = $appConfig['images']['thumbnail']['cdn_prefix'] . $size . "/" . "default.png";
                        $thumbnails[$size] = $thumbnail;
                    }
                }
                $entity['thumbnails'] = $thumbnails;
                
                
                // Add media files
                $media = R::getAll( 'select partNumber, location, length, size from media where content_id = :content_id',
                        array(':content_id'=> $id)
                );
                
                foreach($media as $key => $media_file) {
                    $media_file['location'] = MEDIA_PREFIX . $media_file['location'];
                    $media[$key] = $media_file; 
                }
                
                $entity['media'] = $media;
                
                // Remove unnecessary fields.
                unset($entity['created_at']);
                unset($entity['updated_at']);
                
                return $entity;
            }
        }
    }
    
?>