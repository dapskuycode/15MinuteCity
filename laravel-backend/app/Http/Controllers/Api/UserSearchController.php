<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class UserSearchController extends Controller
{
    public function index() {
        return response()->json(UserSearch::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'search_radius' => 'nullable|integer',
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        $searchId = DB::table('user_searches')->insertGetId([
            'search_location' => DB::raw("ST_SetSRID(ST_Point($lng, $lat), 4326)"),
            'search_radius' => $request->search_radius ?? 900,
            'user_ip' => $request->ip(),
            'session_id' => null, // tidak dikirim, maka null
        ]);

        return response()->json(['message' => 'Search location saved', 'id' => $searchId], 201);
    }

    public function show($id) {
        return response()->json(UserSearch::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $search = UserSearch::findOrFail($id);

        $validated = $request->validate([
            'search_location' => 'sometimes|required',
            'search_address' => 'nullable|string',
            'search_radius' => 'nullable|integer',
            'search_timestamp' => 'nullable|date',
            'user_ip' => 'nullable|string|max:45',
            'session_id' => 'nullable|string|max:255'
        ]);

        $search->update($validated);
        return response()->json($search);
    }

    public function destroy($id) {
        $search = UserSearch::findOrFail($id);
        $search->delete();
        return response()->json(['message' => 'Deleted']);
    }

    

}
