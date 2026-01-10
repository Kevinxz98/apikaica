<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportedBugs; 
use Illuminate\Support\Facades\Auth;



class BugsController extends Controller
{
    public function newReport(Request $request)
    {

        $validated = $request->validate([
            'chatbot_id' => 'required|integer|exists:chatbots,id',
            'issueDescription' => 'required|string',
            'stepsToReproduce' => 'required|string',
        ]);

        $user = Auth::user();

        $validated['user_id'] = $user->id;
        $validated['name'] = $user->name;
        $validated['email'] = $user->email;

        $bug = ReportedBugs::create($validated);

        return response()->json([
            'message' => 'Reporte creado exitosamente',
            'description' => $bug
        ], 201);
    }
}
