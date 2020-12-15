<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;
class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index()
    {
        $type = Type::all();
        return response(['data'=>$type],200);
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
            'name'=>'required|string|max:30',
        ]);
        $type = Type::create([
            'name'=>$request->name
        ]);
        return response(['data'=>$type],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $type = Type::findOrFail($id);
        return response(['data'=>$type],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function edit(Type $type)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $this->validate($request, array(  // Removed `[]` from the array.
            'name'=>'required|string|max:30',
        ));
            $type = Type::findOrFail($id);
            $type->name =$request->name;
            $type->save();
            return response(['data'=>$type],200);
    
    }
  
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $type=Type::findOrFail($id);
        $type->delete();
        return response(['data'=>$type],200);
    }
}
