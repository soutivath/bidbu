<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrivateController extends Controller
{
    public function __construct(){
        $this->middleware("auth:api");
        $this->middleware("checkAdminIsActive:api");
    }
  

    public function getImageFileVerify($folder,$image_name){
      
       if(Storage::disk("private_verify")->exists($folder.DIRECTORY_SEPARATOR.$image_name)){
        $image = base64_encode(file_get_contents(base_path("resources/private/verify/".$folder.DIRECTORY_SEPARATOR.$image_name)));
        return response()->json(["data"=>$image]);  
      }
        else{
          return response()->json(["data"=>"","message"=>"image not found","success"=>false],404);  
        }

    }


}
