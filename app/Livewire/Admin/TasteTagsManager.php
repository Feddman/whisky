<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\TasteTagCategory;
use App\Models\TasteTag;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TasteTagsManager extends Component
{
    public $categories;
    public $selectedCategoryId = null;

    public $newCategoryName = '';
    public $editingCategoryId = null;
    public $editingCategoryName = '';
    public $editingCategoryEmoji = '';
    public $newCategoryEmoji = '';

    public $newTagName = '';
    public $editingTagId = null;
    public $editingTagName = '';
    public $editingTagOrder = 0;

    protected $rules = [
        'newCategoryName' => 'required|string|max:100',
        'newCategoryEmoji' => 'nullable|string|max:8',
        'editingCategoryName' => 'required|string|max:100',
        'editingCategoryEmoji' => 'nullable|string|max:8',
        'newTagName' => 'required|string|max:100',
        'editingTagName' => 'required|string|max:100',
    ];

    public function mount()
    {
        // restrict admin access to the primary test admin user
        if (! Auth::check() || (Auth::id() !== 1 && (Auth::user()->email ?? '') !== 'test@example.com')) {
            abort(403);
        }

        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = TasteTagCategory::orderBy('order')->get();
        if (! $this->selectedCategoryId && $this->categories->isNotEmpty()) {
            $this->selectedCategoryId = $this->categories->first()->id;
        }
    }

    public function selectCategory($id)
    {
        $this->selectedCategoryId = $id;
        $this->resetTagForms();
    }

    public function createCategory()
    {
        $this->validateOnly('newCategoryName');
        $slug = Str::slug($this->newCategoryName);
        $order = TasteTagCategory::max('order') + 10;
        $cat = TasteTagCategory::create([
            'slug' => $slug,
            'name' => $this->newCategoryName,
            'emoji' => $this->newCategoryEmoji,
            'order' => $order,
        ]);
        $this->newCategoryName = '';
        $this->newCategoryEmoji = '';
        $this->loadCategories();
        $this->selectedCategoryId = $cat->id;
        session()->flash('message', 'Category created.');
    }

    public function startEditCategory($id)
    {
        $c = TasteTagCategory::findOrFail($id);
        $this->editingCategoryId = $c->id;
        $this->editingCategoryName = $c->name;
        $this->editingCategoryEmoji = $c->emoji;
    }

    public function updateCategory()
    {
        $this->validateOnly('editingCategoryName');
        $c = TasteTagCategory::findOrFail($this->editingCategoryId);
        $c->update(['name' => $this->editingCategoryName, 'slug' => Str::slug($this->editingCategoryName)]);
        $c->update(['emoji' => $this->editingCategoryEmoji]);
        $this->editingCategoryId = null;
        $this->editingCategoryName = '';
        $this->editingCategoryEmoji = '';
        $this->loadCategories();
        session()->flash('message', 'Category updated.');
    }

    public function deleteCategory($id)
    {
        $c = TasteTagCategory::findOrFail($id);
        // move tags to null (or delete) - we'll set category_id null
        TasteTag::where('category_id', $c->id)->update(['category_id' => null]);
        $c->delete();
        $this->loadCategories();
        session()->flash('message', 'Category deleted.');
    }

    public function createTag()
    {
        $this->validateOnly('newTagName');
        if (! $this->selectedCategoryId) return;
        $slug = Str::slug($this->newTagName);
        $order = TasteTag::where('category_id', $this->selectedCategoryId)->max('order') ?? 0;
        $order = $order + 1;
        TasteTag::create([
            'slug' => $slug,
            'name' => $this->newTagName,
            'category_id' => $this->selectedCategoryId,
            'order' => $order,
        ]);
        $this->newTagName = '';
        $this->loadCategories();
        $this->resetTagForms();
        session()->flash('message', 'Tag created.');
    }

    public function startEditTag($id)
    {
        $t = TasteTag::findOrFail($id);
        $this->editingTagId = $t->id;
        $this->editingTagName = $t->name;
        $this->editingTagOrder = $t->order;
        $this->selectedCategoryId = $t->category_id;
    }

    public function updateTag()
    {
        $this->validateOnly('editingTagName');
        $t = TasteTag::findOrFail($this->editingTagId);
        $t->update([
            'name' => $this->editingTagName,
            'slug' => Str::slug($this->editingTagName),
            'order' => $this->editingTagOrder,
            'category_id' => $this->selectedCategoryId,
        ]);
        $this->editingTagId = null;
        $this->editingTagName = '';
        $this->editingTagOrder = 0;
        $this->loadCategories();
        $this->resetTagForms();
        session()->flash('message', 'Tag updated.');
    }

    public function deleteTag($id)
    {
        $t = TasteTag::findOrFail($id);
        $t->delete();
        $this->loadCategories();
        session()->flash('message', 'Tag deleted.');
    }

    public function resetTagForms()
    {
        $this->newTagName = '';
        $this->editingTagId = null;
        $this->editingTagName = '';
        $this->editingTagOrder = 0;
    }

    protected $listeners = ['refreshLists' => 'loadCategories'];

    public function render()
    {
        $categories = TasteTagCategory::orderBy('order')->get();
        $tags = TasteTag::where('category_id', $this->selectedCategoryId)->orderBy('order')->get();

        return view('livewire.admin.taste-tags-manager', compact('categories', 'tags'));
    }
}
