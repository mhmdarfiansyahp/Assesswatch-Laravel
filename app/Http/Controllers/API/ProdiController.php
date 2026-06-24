<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProdiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Prodi::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_prodi' => 'required|string|max:100|unique:prodi,nama_prodi',
        ]);

        $prodi = Prodi::create([
            'nama_prodi' => $request->nama_prodi,
            'status' => true,
        ]);
        return response()->json([
            'message' => 'Prodi created',
            'data' => $prodi
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(Prodi::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $prodi = Prodi::findOrFail($id);
        $request->validate([
            'nama_prodi' => [
                'required',
                'string',
                'max:100',
                Rule::unique('prodi', 'nama_prodi')->ignore($id),
            ],
            'status' => 'required|boolean',
        ]);

        $prodi->update([
            'nama_prodi' => $request->nama_prodi,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Prodi updated',
            'data' => $prodi
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $prodi = Prodi::findOrFail($id);
        $prodi->delete();
        return response()->json(['message' => 'Prodi deleted']);
    }
}
