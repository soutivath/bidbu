<?php

namespace App\Http\Controllers;

use App\Http\Resources\FavoriteResource;
use App\Models\favourite;
use Auth;
use Illuminate\Http\Request;

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

    public function index()
    {
        /* $favorite = favourite::where(
        'user_id', Auth::id()
        )->with('buddhist')->orderBy("created_at", "desc")->get();*/
        $favorite = favourite::leftJoin("buddhists", 'favourites.buddhist_id', "=", "buddhists.id")
            ->where('buddhists.end_time', '>', Carbon::now())
            ->with("buddhist")
            ->orderBy("favourites.created_at", "desc")
            ->get();
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
