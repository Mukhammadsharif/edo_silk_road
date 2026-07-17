<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Projects\Board;
use App\Models\Projects\BoardList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardListController extends Controller
{
    private function requireAdmin(): void
    {
        abort_unless(
            auth()->user()?->canManageBoards(),
            403,
            'Недостаточно прав'
        );
    }
    public function store(Request $request, Board $board)
    {
        $this->requireAdmin();
        $data = $request->validate(['title' => 'required|string|max:255']);

        $list = $board->lists()->create([
            'title'    => $data['title'],
            'position' => ($board->lists()->max('position') ?? -1) + 1,
        ]);

        return response()->json($list);
    }

    public function update(Request $request, BoardList $list)
    {
        $this->requireAdmin();
        $this->authorizeList($list);

        $list->update($request->validate([
            'title' => 'required|string|max:255',
        ]));

        return response()->json($list);
    }

    public function move(Request $request, BoardList $list)
    {
        $this->authorizeList($list);

        $orderedIds = $request->validate([
            'ordered_ids'   => 'required|array',
            'ordered_ids.*' => 'integer|exists:board_lists,id',
        ])['ordered_ids'];

        DB::transaction(function () use ($orderedIds, $list) {
            foreach ($orderedIds as $position => $id) {
                BoardList::where('id', $id)
                    ->where('board_id', $list->board_id) // не дать тащить в чужую доску
                    ->update(['position' => $position]);
            }
        });

        return response()->noContent();
    }

    public function destroy(BoardList $list)
    {
        $this->requireAdmin();
        $this->authorizeList($list);
        $list->delete();
        return response()->noContent();
    }

    // Проверка, что список принадлежит доске, доступной текущему пользователю
    private function authorizeList(BoardList $list): void
    {
        abort_unless(Board::whereKey($list->board_id)->exists(), 404);
    }
}
