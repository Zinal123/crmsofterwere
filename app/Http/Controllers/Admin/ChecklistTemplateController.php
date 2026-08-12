<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateItem;
use Illuminate\Http\Request;

class ChecklistTemplateController extends Controller
{
    public function index()
    {
        return view('admin.checklist-templates.index', [
            'templates' => ChecklistTemplate::with('items')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ChecklistTemplate::create([
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.checklist-templates.index')
            ->with('success', 'Template created.');
    }

    public function update(Request $request, $id)
    {
        $template = ChecklistTemplate::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $template->update([
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.checklist-templates.index')
            ->with('success', 'Template updated.');
    }

    public function destroy($id)
    {
        ChecklistTemplate::findOrFail($id)->delete();

        return redirect()->route('admin.checklist-templates.index')
            ->with('success', 'Template deleted.');
    }

    public function storeItem(Request $request, $id)
    {
        $template = ChecklistTemplate::findOrFail($id);

        $data = $request->validate([
            'description' => 'required|string|max:255',
        ]);

        $nextPosition = (int) $template->items()->max('position') + 1;

        $template->items()->create([
            'description' => $data['description'],
            'position' => $nextPosition,
        ]);

        return redirect()->route('admin.checklist-templates.index')
            ->with('success', 'Step added.');
    }

    public function destroyItem($itemId)
    {
        ChecklistTemplateItem::findOrFail($itemId)->delete();

        return redirect()->route('admin.checklist-templates.index')
            ->with('success', 'Step removed.');
    }
}
