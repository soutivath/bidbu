<?php

namespace App\Http\Controllers;

use App\Models\Buddhist;
use App\Models\RecommendedBuddhist;
use carbon\Carbon;
use Illuminate\Http\Request;

class RecommendedBuddhistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $recommended = RecommendedBuddhist::with('buddhist')->pluck('buddhist')->paginate(20);
        return response()->json(["data", $recommended], 200);
    }
    public function allBuddhist(Request $request)
    {
        $buddhist = Buddhist::where('end_time', '>', Carbon::now())->with('recommended')->get();
        return response()->json(["data" => $buddhist]);
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
        // works here
        $request->validate(
            [
                'buddhist_id' => 'required|integer',
            ]
        );
        $found = RecommendedBuddhist::where([
            ['buddhist_id', $request->buddhist_id],
        ])->get();
        if ($found->isEmpty()) {
            $recommended = RecommendedBuddhist::Create([
                'buddhist_id' => $request->buddhist_id,
            ]);
            return response()->json(['Message' => 'Save complete'], 201);
        } else {
            foreach ($found as $item) {
                $item->delete();
            }
            return response()->json([
                'Message' => 'Remove recommended Complete',
            ], 200);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RecommendedBuddhist  $recommendedBuddhist
     * @return \Illuminate\Http\Response
     */
    public function show(RecommendedBuddhist $recommendedBuddhist)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RecommendedBuddhist  $recommendedBuddhist
     * @return \Illuminate\Http\Response
     */
    public function edit(RecommendedBuddhist $recommendedBuddhist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RecommendedBuddhist  $recommendedBuddhist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RecommendedBuddhist $recommendedBuddhist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RecommendedBuddhist  $recommendedBuddhist
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecommendedBuddhist $recommendedBuddhist)
    {
        //
    }

}
