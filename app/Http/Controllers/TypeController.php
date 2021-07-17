<?php

namespace App\Http\Controllers;

use App\Http\Resources\TypeResources;
use App\Models\Type;
use File;
use Illuminate\Http\Request;
use Image;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        //  $this->middleware('auth:api')->except(["index", "show"]);
    }
    public function index()
    {
        $type = Type::all();
        return TypeResources::collection($type);
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
        // if (Auth::user()->hasRole("admin") || Auth::user()->hasRole("superadmin")) {

        $request->validate([
            'name' => 'required|string|max:30',
            'image' => 'required',
            'image.*' => 'image|mimes:jpeg,png,jpg|max:8192',
        ]);
        if (!\File::isDirectory(public_path("/type_images"))) {
            \File::makeDirectory(public_path('/type_images'), 493, true);
        }
        $file = $request->image;
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = 'type_image' . '_' . time() . '_' . \uniqid() . '.' . $fileExtension;
        $location = public_path("/type_images/" . $fileName);
        Image::make($file)->resize(300, 300)->save($location);

        $type = Type::create([
            'name' => $request->name,
            'image_path' => $fileName,
        ]);
        return response(['data' => $type], 201);
        //  } else {
        //     return response(['message' => 'Permission denied'], 403);
        //  }
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
        if ($type != null) {
            return response()->json(['data' => $type], 200);

        }
        return response()->json(["message" => "Data not found"], 404);
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
    public function update(Request $request, $id)
    {
        // if (Auth::user()->hasRole("admin") || Auth::user()->hasRole("superadmin")) {

        $this->validate($request, array( // Removed `[]` from the array.
            'name' => 'required|string|max:30',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:8192',
        ));

        $type = Type::findOrFail($id);

        $type->name = $request->name;
        if ($request->hasFile('image')) {
            $file = $request->image;
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = 'type_image' . '_' . time() . '_' . \uniqid() . '.' . $fileExtension;
            $location = public_path("/type_images/" . $fileName);
            Image::make($file)->resize(300, 300)->save($location);

            $path = public_path() . '/type_images/' . $type->image_path;
            if (\file_exists($path)) {
                unlink(public_path() . '/type_images/' . $type->image_path);
            }
            $type->image_path = $fileName;
        }
        $type->save();
        return response(['data' => $type], 200);
        //  } else {
        //      return response(['message' => 'Permission denied'], 403);
        //   }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            "id" => "required|integer",
        ]);
        //  if (Auth::user()->hasRole("admin") || Auth::user()->hasRole("superAdmin")) {
        $type = Type::findOrFail($request->id);
        $path = public_path() . '/type_images/' . $type->image_path;
        if (\file_exists($path)) {
            unlink(public_path() . '\type_images/' . $type->image_path);
        }
        $type->delete();
        return response(['data' => $type], 200);
        //   } else {
        //      return response(['message' => 'Permission denied'], 403);
        //   }
    }

}
