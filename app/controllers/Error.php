<?php

class Error {
    public static function getErrorArray($error_code,$error_message) {
        return array("errorCode"=>$error_code,"errorMessage"=>$error_message);
    }
    
    public static function getReflectionErrorCodeArray($ref_err_type){
        try{
            $ref_class = new ReflectionClass('ErrorCodes');
            $result["ErrorCode"] = $ref_class->getConstant($ref_err_type);
            $ref_class = new ReflectionClass('ErrorMessages');
            $result["ErrorMessage"] = $ref_class->getConstant($ref_err_type);
            	
            if("" == $result["ErrorCode"] || "" == $result["ErrorCode"]){
                $error = 'Always throw this error';
                throw new Exception($error);
            }
            	
            return $result;
        } catch(Exception $e){
            error_log("[ErrorCodes::getReflectionErrorCodeArray] Exception: ".$e->getMessage());
            return ErrorCodes::getErrorArray(ErrorCodes::Unknown,ErrorMessages::Unknown);
        }
    }    
}

class ErrorCode {	
	const UserDoesNotExist = 1;
	const EmailAlreadyUsed = 2;
	const ErrorWhileCreatingUser = 3;
	const ContentDoesNotExist = 4;
	const FeaturedContentListDoesNotExist = 5;
}
	
class ErrorMessage {
	const UserDoesNotExist = "User DOES NOT exist";
	const EmailAlreadyUsed = "Given email is already used.";
	const ErrorWhileCreatingUser = "There was an error while creating user"; 
	const ContentDoesNotExist = "Content DOES NOT exist";
	const FeaturedContentListDoesNotExist = "There DOES NOT exist featured content list with given name. Please specify correct listname";
	
	public static function userEmailAlreadyExists($email) {
		return "User with email '".$email."' already exists";
		
	}
}
