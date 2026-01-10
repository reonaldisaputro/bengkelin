<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Specialist;
use Illuminate\Http\Request;

class SpecialistController extends Controller
{
    /**
     * Display a listing of specialists.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $specialists = Specialist::all();

        return response()->json([
            'success' => true,
            'message' => 'Specialists retrieved successfully',
            'data' => $specialists
        ], 200);
    }

    /**
     * Display the specified specialist.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $specialist = Specialist::find($id);

        if (!$specialist) {
            return response()->json([
                'success' => false,
                'message' => 'Specialist not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Specialist retrieved successfully',
            'data' => $specialist
        ], 200);
    }
}
