<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\BannerTran;
use App\Models\Language;
use Illuminate\Http\Request;
use File;
use Image;
use Illuminate\Support\Facades\DB;

class ShowBannerController extends Controller
{
    /**
     * @param {[]Image,language_id}
     * @return JSON
     */
    public function post(Request $request)
    {

        $request->validate([
            'bannerArrays.*.image'=>'required|array',
            'bannerArrays.*.image.*' => 'image|mimes:jpeg,png,jpg,PNG|max:30720',
            'bannerArrays.*.language_id' => "exists:languages,id",
            'active'=>'required|boolean'
        ]);
        $banner = new Banner();
        $banner->active = $request->active;
        $banner->save();
            DB::beginTransaction();
            try {
                if (!\File::isDirectory(public_path("/banner_images"))) {
                    \File::makeDirectory(public_path('/banner_images'), 493, true);
                }
                foreach ($request->bannerArrays as $bannersArray) {
                    $fileExtension = $bannersArray->image->getClientOriginalExtension();
                    $fileName = 'banner' . \uniqid() . "_" . time() . '.' . $fileExtension;
                    $location = public_path("/banner_images/" . $fileName);
                    Image::make($image)->resize(640, 312, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($location);

                    $bannerTrans = new BannerTran();
                    $bannerTrans->image_path = $fileName;
                    $bannerTrans->language_id = $bannersArray->language_id;
                    $bannerTrans->banner_id = $banner->id;
                    $bannerTrans->save();
                }
                DB::commit();
                return response()->json([
                    "message" => "Operation done"
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }

    }


    public function update(Request $request, $banner_id)
    {
        $request->validate([
            'bannerArrays.*.image'=>'sometimes|array',
            'bannerArrays.*.image.*' => 'image|mimes:jpeg,png,jpg,PNG|max:30720',
            'bannerArrays.*.language_id' => "exists:languages,id",
            'active'=>'required|boolean'
        ]);

        $banner = Banner::where("id", $banner_id)->first();
        if (!$banner) {
            return response()->json([
                "message" => "No banner found"
            ]);
        }
        DB::beginTransaction();
        try {
            foreach($request->bannerArrays as $bannersArray)
            {
                $bannerTran = BannerTran::where([
                    ["banner_id",$banner_id],
                    ["lanaguage_id",$bannersArray->language_id]
                ])->first();

                if($bannerTran)
                {
                    if($request->hasFile($bannersArray->image))
                    {
                        $fileExtension = $bannersArray->image->getClientOriginalExtension();
                    $fileName = 'banner' . \uniqid() . "_" . time() . '.' . $fileExtension;
                    $location = public_path("/banner_images/" . $fileName);
                    Image::make($bannersArray->image)->resize(640, 312, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($location);
                    $oldPath = public_path() . '/banner_images/' . $bannerTran->image_path;
                    if (\file_exists($oldPath)) {
                        unlink(public_path() . '/banner_images/' . $bannerTran->image_path);
                    }
                     $bannerTran->image_path = $fileName;
                    }
                    $bannerTran->language_id = $bannersArray->language_id;
                    $bannerTran->save();
                }
            }

            $banner->active = $request->active;
            $banner->save();

            DB::commit();
            return response()->json([
                "message" => "Operation done"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }

    }

    public function destroy($banner_id)
    {
        $banner = Banner::findOrFail($banner_id);
        $banner->delete();
        $bannerTrans = BannerTran::where("banner_id",$banner->id)->get();
        if($bannerTrans->count()>0)
        {
            foreach($bannerTrans as $bannerTran)
            {
                $file = public_path() . '/banner_images/' . $bannerTran->image_path;
                if (\file_exists($file)) {
                    unlink(public_path() . '/banner_images/' . $bannerTran->image_path);
                }
            }
        }
        return response()->json([
            "data"=>$banner
        ],200);
    }

    public function getAll(Request $request)
    {
        $banners =[];
        if($request->input("language"))
        {
            //supported language [lao,english]
          //  $queryString = $request->input('language');
            $allBanners = Banner::with(['banner_trans.language'=>function($query,$request){

                $query->where("name",$request->input('language'));
            }])->get();
            if($allBanners->count==0)
            {
                $allBanners = Banner::with(['banner_trans.language'=>function($query){
                    $query->where("name","lao");
                }])->get();
            }
        }
        return response()->json([
            "data"=>$banners
        ],200);
    }

    public function show($banner_id)
    {
        $banner = Banner::findOrFail($banner_id)->with("banner_trans");
        return response()->json([
            "data"=>$banner
        ]);
    }


    public function quickActiveBanner(Request $request)
    {
       $request->validate([
           "banner_id"=>"required|integer|exists:banners,id"
       ]);
       $banner = Banner::findOrFail($request->banner_id);
       $banner->active = !$banner->active;
       $banner->save();
       return response()->json([
           "data"=>$banner
       ]);

    }

    public function viewActiveBanner(Request $request)
    {
        $banners =[];
        if($request->input("language"))
        {
            //supported language [lao,english]
          //  $queryString = $request->input('language');
            $allBanners = Banner::where("active",1)->with(['banner_trans.language'=>function($query,$request){
                $query->where("name",$request->input('language'));
            }])->get();
            if($allBanners->count==0)
            {
                $allBanners = Banner::where("active",1)->with(['banner_trans.language'=>function($query){
                    $query->where("name","lao");
                }])->get();
            }
        }
        return response()->json([
            "data"=>$banners
        ],200);
    }
    public function viewNonActiveBanner(Request $request)
    {
        $banners =[];
        if($request->input("language"))
        {
            //supported language [lao,english]
          //  $queryString = $request->input('language');
            $allBanners = Banner::where("active",0)->with(['banner_trans.language'=>function($query,$request){
                $query->where("name",$request->input('language'));
            }])->get();
            if($allBanners->count==0)
            {
                $allBanners = Banner::where("active",0)->with(['banner_trans.language'=>function($query){
                    $query->where("name","lao");
                }])->get();
            }
        }
        return response()->json([
            "data"=>$banners
        ],200);
    }


}
