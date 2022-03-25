<?php
namespace App\Traits;
trait ResponseAPI{

    public function coreResponse($message,$data=null,$statusCode,$isSuccess=true){
        if(!$message){
            return response()->json([
                "message"=>$message,
            ]);
        }

        if($isSuccess){
            return response()->json([
                "message"=>$message,
                "error"=>false,
                "code"=>$statusCode,
                "data"=>$data
            ]);
        }else{
            return response()->json([
                "message"=>$message,
                "error"=>true,
                "code"=>$statusCode,
            ],$statusCode);
        }
    }

    public function success($message,$data,$statusCode=200){
        return $this->coreResponse($message,$data,$statusCode);
    }

    public function error($message,$statusCode=500){
        return $this->coreResponse($message,null,$statusCode,false);
    }

    
    

}