<?php 
    require_once __DIR__.'/Error.php';
    require_once __DIR__.'/BaseController.php';
    

    class User extends BaseController {
        
        public static function addUser() {
            $app = \Slim\Slim::getInstance();
            $response = $app->response();
            
            try {
                
                $email = $app->request()->post('email');
                $loginType = $app->request()->post('loginType');
            
                $existingUser = R::findOne('user',
                        'loginType=:loginType and email=:email',
                        array(
                                ':loginType' => $loginType,
                                ':email' => $email
                        )
                );
                
            
                error_log("Received email: " . $email);
                error_log("Received login type: " . $loginType);
            
            
                if($existingUser != null) {
                    error_log("User exists already");
                    $userId = $existingUser->id;
                } else {
                    $user = R::dispense('user');
                    $user->systemId = $app->request()->post('systemId');
                    $user->systemId = $app->request()->post('systemId');
                    $user->name = $app->request()->post('name');
                    $user->email = $email;
                    $user->password = md5($app->request()->post('password'));
                    $user->loginType = $loginType;
            
                    $extradata_arr = array();
                    switch($loginType) {
                        case MMConstants::LOGIN_TYPE_FACEBOOK:
                            $extradata_arr['token'] = $app->request()->post('token');
                            break;
                        case MMConstants::LOGIN_TYPE_TWITTER:
                            $extradata_arr['token1'] = $app->request()->post('token1');
                            $extradata_arr['token2'] = $app->request()->post('token2');
                            break;
                    }
            
                    $user->extradata = json_encode($extradata_arr);
                    $userId = R::store($user);
                }
            
                User::getUserById($userId);
            } catch(RedBean_Exception_SQL $ex) {
                error_log(print_r($ex, true));
                $response->status(400);
                $response->body(json_encode(Error::getErrorArray(ErrorCode::ErrorWhileCreatingUser, ErrorMessage::ErrorWhileCreatingUser)));
            }
        }
        
        public static function getUserById($id) {
            $app = \Slim\Slim::getInstance();
            $response = $app->response();
            
            $user_bean = R::findOne('user', 'id=?', array($id));
            if($user_bean == null) {
                $response->status(404);
                $response->body(json_encode(Error::getErrorArray(ErrorCode::UserDoesNotExist, ErrorMessage::UserDoesNotExist)));
            } else {
                $users = R::exportAll($user_bean);
                $user = $users[0];
            
                $extradata_arr = json_decode($user['extradata'], true);
                $user = array_merge($user, $extradata_arr);
                unset($user['password']);
                unset($user['extradata']);
                
                $user = array('user' => $user);
                $response->body(json_encode($user));
            }
        }
    }
?>