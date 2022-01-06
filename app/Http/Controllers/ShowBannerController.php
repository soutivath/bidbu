<?php

namespace App\Http\Controllers;

use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Models\BannerTran;
use App\Models\Language;
use Exception;
use Illuminate\Http\Request;
use File;
use Illuminate\Database\RecordsNotFoundException;
use Image;
use Illuminate\Support\Facades\DB;
use Lcobucci\JWT\Encoding\JoseEncoder;

class ShowBannerController extends Controller
{
    /**
     * @param {[]Image,language_id}
     * @return JSON
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except(["index", "show"]);
        $this->middleware('isUserActive:api')->except(["index", "show"]);
    }
    public function post(Request $request)
    {

        $request->validate([
            'active' => 'required|boolean',
            'bannerArrays' => "array",
            'bannerArrays.*.image' => 'required|image|mimes:jpeg,png,jpg,PNG|max:30720',
            // 'bannerArrays.*.image.*' => 'mimes:jpeg,png,jpg,PNG|max:30720',
            'bannerArrays.*.language_id' => "required|exists:languages,id|distinct",

        ]);


        DB::beginTransaction();
        try {
            $banner = new Banner();
            $banner->active = $request->active;
            $banner->save();
            if (!\File::isDirectory(public_path("/banner_images"))) {
                \File::makeDirectory(public_path('/banner_images'), 493, true);
            }
            foreach ($request->bannerArrays as $bannersArray) {

                $fileExtension = $bannersArray["image"]->getClientOriginalExtension();
                $fileName = 'banner' . \uniqid() . "_" . time() . '.' . $fileExtension;
                $location = public_path("/banner_images/" . $fileName);
                Image::make($bannersArray["image"])->resize(640, 312, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($location);

                $bannerTrans = new BannerTran();
                $bannerTrans->image_path = $fileName;
                $bannerTrans->language_id = $bannersArray["language_id"];
                $bannerTrans->banner_id = $banner->id;
                $bannerTrans->save();
            }
            DB::commit();
            return response()->json([
                "message" => "Operation done"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                "message" => "Something went wrong"
            ], 500);
        }
    }


    public function update(Request $request, $banner_id)
    {
        $request->validate([
            'active' => 'required|boolean',
            'bannerArrays' => "array",
            'bannerArrays.*.bannerTran_id' => "required|integer|exists:banner_trans,id",
            'bannerArrays.*.language_id' => "sometimes|exists:languages,id|distinct",
            'bannerArrays.*.image' => 'sometimes|image|mimes:jpeg,png,jpg,PNG|max:30720',
            // 'bannerArrays.*.image.*' => 'mimes:jpeg,png,jpg,PNG|max:30720',
        ]);

        $banner = Banner::where("id", $banner_id)->first();
        if (!$banner) {
            return response()->json([
                "message" => "No banner found"
            ]);
        }


        DB::beginTransaction();
        try {
            if ($request->has("bannerArrays")) {

                foreach ($request->bannerArrays as $bannersArray) {
                    if (array_key_exists("language_id", $bannersArray) || array_key_exists("image", $bannersArray)) {

                        $bannerTran = BannerTran::where([
                            ["id", $bannersArray["bannerTran_id"]],
                            ["banner_id", $banner_id],
                            //  ["language_id",$bannersArray["language_id"]]
                        ])->first();

                        if ($bannerTran) {

                            if (array_key_exists("image", $bannersArray)) {
                                $fileExtension = $bannersArray["image"]->getClientOriginalExtension();
                                $fileName = 'banner' . \uniqid() . "_" . time() . '.' . $fileExtension;

                                $location = public_path("/banner_images/" . $fileName);
                                Image::make($bannersArray["image"])->resize(640, 312, function ($constraint) {
                                    $constraint->aspectRatio();
                                })->save($location);
                                $oldPath = public_path() . '/banner_images/' . $bannerTran->image_path;
                                if (\file_exists($oldPath)) {
                                    unlink(public_path() . '/banner_images/' . $bannerTran->image_path);
                                }
                                $bannerTran->image_path = $fileName;
                            }
                            if (array_key_exists("language_id", $bannersArray)) {
                                $bannerTran->language_id = $bannersArray["language_id"];
                            }

                            $bannerTran->save();
                        }
                    }
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
            throw $e;
            return $e->getMessage();
        }
    }

    public function destroy($banner_id)
    {
        $banner = Banner::findOrFail($banner_id);

        $bannerTrans = BannerTran::where("banner_id", $banner->id)->get();

        if ($bannerTrans->count() > 0) {
            foreach ($bannerTrans as $bannerTran) {

                $file = public_path() . '/banner_images/' . $bannerTran->image_path;

                if (\file_exists($file)) {
                    unlink(public_path() . '/banner_images/' . $bannerTran->image_path);
                }
            }
        }
        $banner->delete();
        return response()->json([
            "data" => $banner
        ], 200);
    }

    public function getAll(Request $request)
    {
        $banners = [];

        if ($request->input("language")) {
            $language = $request->input("language");
            $languageID = Language::where("name", $language)->first();
            if ($languageID) {
                $banners = Banner::with([
                    "banner_trans" => function ($query) use ($languageID) {
                        $query->where("language_id", $languageID->id);
                    }
                ])->paginate(30);
            }
        }
        if ($banners == null || count($banners) == 0) {
            $banners = Banner::with([
                "banner_trans" => function ($query) {
                    $query->where("language_id", 1);
                }
            ])->paginate(30);
        }

        return BannerResource::collection($banners);
    }

    public function show($banner_id)
    {
        try {
            $banner = Banner::where("id", $banner_id)->with("banner_trans")->paginate(30);
            return response()->json([
                "data" => $banner
            ]);
        } catch (RecordsNotFoundException $e) {
            return response()->json([
                "message" => "no record found"
            ]);
        }
    }


    public function quickActiveBanner(Request $request)
    {
        $request->validate([
            "banner_id" => "required|integer|exists:banners,id"
        ]);
        try {
            $banner = Banner::findOrFail($request->banner_id);
        } catch (RecordsNotFoundException $e) {
            return response()->json([
                "message" => "no record found"
            ]);
        }

        $banner->active = !$banner->active;
        $banner->save();
        return response()->json([
            "data" => $banner
        ]);
    }

    public function viewActiveBanner(Request $request)
    {
        $banners = null;

        if ($request->input("language")) {
            $language = $request->input("language");
            $languageID = Language::where("name", $language)->first();
            if ($languageID) {
                $banners = Banner::where("active", 1)->with([
                    "banner_trans" => function ($query) use ($languageID) {
                        $query->where("language_id", $languageID->id);
                    }
                ])->paginate(30);
            }
        }
        if ($banners == null || $banners->count() == 0) {
            $banners = Banner::where("active", 1)->with([
                "banner_trans" => function ($query) {
                    $query->where("language_id", 1);
                }
            ])->paginate(30);
        }

        return BannerResource::collection($banners);
        return response()->json([
            "data" => $banners
        ], 200);
    }
    public function viewNonActiveBanner(Request $request)
    {
        $banners = null;

        if ($request->input("language")) {
            $language = $request->input("language");
            $languageID = Language::where("name", $language)->first();
            if ($languageID) {
                $banners = Banner::where("active", 0)->with([
                    "banner_trans" => function ($query) use ($languageID) {
                        $query->where("language_id", $languageID->id);
                    }
                ])->paginate(30);
            }
        }
        if ($banners == null || $banners->count() == 0) {
            $banners = Banner::where("active", 1)->with([
                "banner_trans" => function ($query) {
                    $query->where("language_id", 0);
                }
            ])->paginate(30);
        }

        return BannerResource::collection($banners);
        return response()->json([
            "data" => $banners
        ], 200);
    }
}
