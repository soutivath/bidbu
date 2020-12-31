<?php

namespace App\Http\Controllers;

use Auth;
use Image;
use Carbon\Carbon;
use App\Models\Buddhist;
use Illuminate\Http\Request;
use App\Http\Resources\BuddhistResource;
use App\Http\Resources\BuddhistResourceCollection;
use Illuminate\Support\Facades\Storage;
use File;
class BuddhistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except('index','buddhistType');
    }
    public function index()
    {
        
      $bud = Buddhist::whereDate('end_time','>',Carbon::now())->with('type')->get();
    return BuddhistResource::collection($bud);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|min:3|max:30|string',
            'detail'=>'required|string|max:255',
            'end_datetime'=>'required|date|date_format:Y-m-d H:i:s|after:now',
            'price'=>'required|integer',
            'images' =>'required|array|max:5',
            'images.*'=>'image|mimes:jpeg,png,jpg,PNG|max:8192',
            'type_id'=>'required|string',
        ]);
        $bud = new Buddhist();
        $bud->name = $request->name;
        $bud->detail = $request->detail;
        $bud->price= $request->price;
        $bud->start_time = Carbon::now();
        $bud->end_time= $request->end_datetime;
        $bud->highest_price = $request->price;
        
            $folderName = uniqid()."_".time();
            if(!\File::isDirectory(public_path("/buddhist_images")))
               {
                   \File::makeDirectory(public_path('/buddhist_images'),493,true);
               }
               if(!\File::isDirectory(public_path("/buddhist_images/".$folderName)))
               {
                \File::makeDirectory(public_path('/buddhist_images/'.$folderName),493,true);
               }
               
            foreach($request->images as $image)
            {  
                $fileExtension = $image->getClientOriginalExtension();
                $fileName = 'buddhist'.\uniqid()."_".time().'.'.$fileExtension;
                $location = public_path("/buddhist_images/".$folderName."/".$fileName);
                Image::make($image)->resize(800,800)->save($location);
               
            }
            
            $bud->image_path = $folderName;
    
        $bud->user_id = Auth::id();
        $bud->type_id = $request->type_id;
        $bud->save();
        try{
           $database = app('firebase.database');
            $reference = $database->getReference('buddhist/'.$bud->id.'/')
            ->push([
                'uid'=>Auth::user()->firebase_uid, //owner id
                'price'=>$request->price, // owner start price
                'buddhist_id'=>$request->buddhist_id,
            ]);
            return response()->json(['data'=>$bud],201);
          
       }
        catch(Exception $e)
        {
           $bud->destroy();
            return response()->json(['Message'=>'Something went wrong'.$e]);
        }

     //get Highest Data
      /*  $database = app('firebase.database');
        $reference = $database->getReference('buddhist/2/')
        ->orderByChild('price')
        ->limitToLast(1)
        ->getSnapshot()
        ->getValue();
       dd($reference);*/
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Buddhist  $buddhist
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        $bud = Buddhist::findOrFail($id);
        $files= File::files(public_path('/buddhist_images/'.$bud->image_path."/"));
        foreach($files as $file){
            $file_path = pathinfo($file);
            dd($file_path['dirname']);
        }
        return response()->json(["data"=>$file],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Buddhist  $buddhist
     * @return \Illuminate\Http\Response
     */
    public function edit(Buddhist $buddhist,$id)
    {
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Buddhist  $buddhist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $request->validate([
            'name'=>'required|min:3|max:30|string',
            'detail'=>'required|string|max:255',
            'end_datetime'=>'required|date|date_format:Y-m-d H:i:s|after:now',
            'price'=>'required|integer',
            'highest_price'=>'required|integer',
           // 'type_id'=>'required|string',
           // 'user_id'=>'required|string',
        ]);
        $bud = Buddhist::findOrFail($id);
        $bud->name = $request->name;
        $bud->detail = $request->detail;
        $bud->price= $request->price;
        $bud->end_datetime= $request->end_datetime;
        $bud->highest_price = $request->price;
        $bud->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Buddhist  $buddhist
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        $bud = Buddhist::findOrFail($id);
        $database = app('firebase.database');
        $reference = $database->getReference('buddhist/'.$bud->id.'/')->remove();
        $path = public_path().'/buddhist_images/'.$bud->image_path;
        
        if(\File::isDirectory($path))
        {
            \File::deleteDirectory($path);
           
        }
        $bud->delete();
        return \response()->json(['message'=>'delete data complete'],200);
    }


    public function getHigh()
    {
/*$database = app('firebase.database');
        $reference = $database->getReference('buddhist/2/')
        ->orderByChild('price')
        ->limitToLast(1)
        ->getSnapshot()
        ->getValue();
       dd($reference);*/
       $database = app('firebase.database');
       $reference = $database->getReference('buddhist/2/')
       ->orderByChild('price')
       ->limitToLast(1)
       ->getSnapshot();
       $highest_price=0;
       $data = $reference->getValue();
      foreach($data as $key=>$eachData);
      {
       $highest_price= $eachData['price'];  
      }
      dd($highest_price);
    }


    public function bidding(Request $request,$id)
    {
        $request->validate([
            'bidding_price'=>'required|integer'
        ]);
        $bud = Buddhist::find($id);
        //get Highest Price
        $database = app('firebase.database');
        $reference = $database->getReference('buddhist/'.$bud->id.'/')
        ->orderByChild('price')
        ->limitToLast(1)
        ->getSnapshot();
        $highest_price=0;
        $data = $reference->getValue();
       foreach($data as $key=>$eachData);
       {
        $highest_price= $eachData['price'];  
       }
        if($request->bidding_price>$highest_price)
        {
          
             $reference = $database->getReference('buddhist/'.$bud->id.'/')
             ->push([
                 'uid'=>Auth::user()->firebase_uid, //bidder id
                 'price'=>$request->bidding_price   // new highest price
             ]);
            $bud->highest_price = $request->bidding_price;
            $bud->save();
            return response()->json(["data"=>"Successfully"],200);
        }
        else{
            return response()->json(["data"=>"Your bidding price must more than highest price"],400);
        }
    }

    public function buddhistType($type_id)
    {
        $buddhists = Buddhist::where('type_id',$type_id)->with('type')->get();
        return response()->json(['data'=>$buddhists],200);
    }
}
