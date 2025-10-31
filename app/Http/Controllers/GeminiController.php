<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeminiController extends Controller
{
    public function generateCourseDescription(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string'],
        ]);

        return response()->json([
            'description' => 'This is a placeholder course description. Integrate Google Generative AI here.',
        ]);
    }

    public function poseOfTheDay()
    {
        return response()->json([
            'name' => 'Mountain Pose',
            'instructions' => 'Stand tall, press your feet into the ground, and reach your arms overhead.',
            'benefits' => 'Improves posture and grounding. Replace with Gemini output once integrated.',
        ]);
    }
}
