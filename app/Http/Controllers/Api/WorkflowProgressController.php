<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkflowProgress;
use Illuminate\Http\Request;

class WorkflowProgressController extends Controller
{
    // GET /api/workflow/{phase}
    public function show(string $phase)
    {
        $progress = WorkflowProgress::where('phase', $phase)->first();

        if (!$progress) {
            return response()->json([
                'phase'       => $phase,
                'step_states' => [],
                'done'        => 0,
                'total'       => 0,
                'started_at'  => null,
                'ended_at'    => null,
            ]);
        }

        return response()->json($progress);
    }

    // POST /api/workflow/{phase}
    public function update(Request $request, string $phase)
    {
        $validated = $request->validate([
            'step_states' => 'required|array',
            'done'        => 'required|integer|min:0',
            'total'       => 'required|integer|min:0',
            'started_at'  => 'nullable|date',
            'ended_at'    => 'nullable|date',
        ]);

        $progress = WorkflowProgress::updateOrCreate(
            ['phase' => $phase],
            $validated
        );

        return response()->json($progress);
    }
}
