<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Projects\Card;
use App\Models\Projects\Checklist;
use App\Models\Projects\ChecklistItem;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function store(Request $request, Card $card)
    {
        $checklist = $card->checklists()->create([
            'title'    => $request->input('title', 'Чек-лист'),
            'position' => ($card->checklists()->max('position') ?? -1) + 1,
        ]);
        return response()->json($checklist->load('items'));
    }

    public function destroy(Checklist $checklist)
    {
        $checklist->delete();
        return response()->noContent();
    }

    public function addItem(Request $request, Checklist $checklist)
    {
        $item = $checklist->items()->create([
            'title'    => $request->validate(['title' => 'required|string|max:255'])['title'],
            'position' => ($checklist->items()->max('position') ?? -1) + 1,
        ]);
        return response()->json($item);
    }

    public function toggleItem(ChecklistItem $item)
    {
        $item->update(['is_done' => ! $item->is_done]);
        return response()->json($item);
    }

    public function destroyItem(ChecklistItem $item)
    {
        $item->delete();
        return response()->noContent();
    }
}
