<?php

namespace App\Http\Controllers;

use App\Models\favourite;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
                
            ]
            );
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
