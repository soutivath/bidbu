<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class LanguageController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:api");
        $this->middleware("checkAdminIsActive:api");
    }
    public function getAll()
    {
        $languages = Language::all();
        return response()->json(["data" => $languages], 200);
    }
    public function post(Request $request)
    {
        $request->validate([
            "name" => "required|string"
        ]);
        $language = Language::create([
            "name" => $request->name
        ]);
        return response()->json([
            "data" => $language
        ], 201);
    }
    public function update(Request $request, $language_id)
    {
        $request->validate([
            "name" => "required|string"
        ]);
        $language = Language::where("id", $language_id)->first();
        if ($language) {
            $language->name = $request->name;
            $language->save();
            return response()->json([
                "data" => $language
            ], 200);
        }
        return response()->json([
            "message" => "No data found"
        ]);
    }

    public function get($language_id)
    {
        try {
            $languages = Language::findOrFail($language_id);
            return response()->json([
                "data" => $languages
            ]);
        } catch (\Illuminate\Database\RecordsNotFoundException $e) {
            //throw $th;
           return response()->json([
               "message"=>"no record found"
           ],404);
        }
    }
    public function destroy($language_id)
    {
        try{
        $language = Language::findOrFail($language_id);
        if (!$language) {
            return response()->json([
                "message" => "This item not found"
            ], 404);
        }
        $language->delete();
        return response()->json([
            "data" => $language
        ], 200);
    }catch(RecordsNotFoundException $e)
    {
        return response()->json([
            "message"=>"no record found"
        ],400);
    }
    }
}
