<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Projects\Board;
use App\Models\Projects\BoardLabel;
use Illuminate\Http\Request;

class BoardLabelController extends Controller
{
    public function index(Board $board)
    {
        return response()->json($board->labels);
    }

    public function store(Request $request, Board $board)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name'  => 'required|string|max:50',
            'color' => 'required|string|max:20',
        ]);
        $label = $board->labels()->create([
            'name'     => $data['name'],
            'color'    => $data['color'],
            'position' => ($board->labels()->max('position') ?? -1) + 1,
        ]);
        return response()->json($label);
    }

    public function update(Request $request, BoardLabel $label)
    {
        $this->requireAdmin();
        $label->update($request->validate([
            'name'  => 'sometimes|string|max:50',
            'color' => 'sometimes|string|max:20',
        ]));
        return response()->json($label);
    }

    public function destroy(BoardLabel $label)
    {
        $this->requireAdmin();
        $label->delete();
        return response()->noContent();
    }

    private function requireAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole(['Super_Admin', 'IT_Admin']), 403);
    }
}
