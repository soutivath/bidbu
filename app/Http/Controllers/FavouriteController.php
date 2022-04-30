<?php

namespace App\Http\Controllers;

use App\Http\Resources\FavoriteResource;
use App\Models\favourite;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Constants\QueryConstant;
class FavouriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware("auth:api");
        $this->middleware('isUserActive:api');

    }

    public function index(Request $request)
    {
        $perPage = QueryConstant::PERPAGE_PAGINATE_DEFAULT;
       
        if($request->has("perPage")){
            $convertedPerPage = (int)$request->perPage;
           


            if($convertedPerPage!=0){
                $perPage = (int)$request->perPage;
            }

            if($convertedPerPage>50){
                $perPage = (int)$request->perPage;
            }
        }
        /* $favorite = favourite::where(
        'user_id', Auth::id()
        )->with('buddhist')->orderBy("created_at", "desc")->get();*/


        // 'id' => $this->id,
        // 'buddhist_id' => $this->buddhist->id,
        // 'user_id' => $this->user->id,
        // 'name' => $this->name,
        // 'place' => $this->buddhist->place,
        // 'time_remain' => Carbon::now()->lessThan(Carbon::parse($this->end_time)) ? Carbon::now()->diffInSeconds(Carbon::parse($this->end_time)) : 0,
        // 'picture' => $allImage,
        // 'is_verify'=>$this->file_verify_status==VerifyStatus::APPROVED?true:false

        $favorite = favourite::select(["favourites.id as id","buddhists.id as buddhist_id","buddhists.user_id as user_id","buddhists.name","buddhists.place"
        ,"buddhists.end_time","buddhists.image_path","verifies.file_verify_status"])->leftJoin("buddhists", 'favourites.buddhist_id', "=", "buddhists.id")
      ->leftJoin("verifies","buddhists.user_id","=","verifies.user_id")
            ->where([
                ['buddhists.end_time', '>', Carbon::now()],
                ['favourites.user_id',Auth::id()]])
           //->with("buddhist")
            ->orderBy("favourites.created_at", "desc")
            ->paginate($perPage);

            
        if (empty($favorite)) {
            return response()->json([
                "message" => "no favorite yet",
            ]);
        }
        //return Response()->json(['data'=>$favorite],200);
        return FavoriteResource::collection($favorite);
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

        $request->validate(
            [
                'buddhist_id' => 'required|integer',
            ]
        );
        $found = favourite::where([
            ['buddhist_id', $request->buddhist_id],
            ['user_id', Auth::id()],
        ])->get();

        if ($found->isEmpty()) {

            $favorite = favourite::Create([
                'user_id' => Auth::id(),
                'buddhist_id' => $request->buddhist_id,
            ]);
            return response()->json(['Message' => 'Save complete'], 201);
        } else {
            foreach ($found as $item) {
                $item->delete();
            }
            return response()->json([
                'Message' => 'Delete favourite Complete',
            ], 200);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\favourite  $favourite
     * @return \Illuminate\Http\Response
     */
    public function show(favourite $favourite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\favourite  $favourite
     * @return \Illuminate\Http\Response
     */
    public function edit(favourite $favourite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\favourite  $favourite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, favourite $favourite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\favourite  $favourite
     * @return \Illuminate\Http\Response
     */
    public function destroy(favourite $favourite)
    {
        //
    }
}
