<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Projects\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::active()->latest()->get();
        return view('boards.index', compact('boards'));
    }

    public function create()
    {
        return view('boards.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $board = Board::create($data);

        // Стартовые списки по умолчанию
        foreach (['К исполнению', 'В работе', 'На согласовании', 'Завершено'] as $i => $title) {
            $board->lists()->create(['title' => $title, 'position' => $i]);
        }

        return redirect()->route('boards.show', $board);
    }

    public function show(Board $board)
    {
        $board->load([
            'lists.cards' => fn ($q) => $q->orderBy('position'),
            'lists.cards.assignee',
        ]);

        return view('boards.show', compact('board'));
    }

    public function edit(Board $board)
    {
        return view('boards.edit', compact('board'));
    }

    public function update(Request $request, Board $board)
    {
        $board->update($request->validate([
            'title'       => 'sometimes|string|max:255',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_archived' => 'boolean',
        ]));

        return back();
    }

    public function destroy(Board $board)
    {
        $board->delete();
        return redirect()->route('boards.index');
    }
}
