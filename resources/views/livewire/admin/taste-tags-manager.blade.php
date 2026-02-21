<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Taste Tags Manager</h2>
        <div class="text-sm text-zinc-500">Manage categories and tags</div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="col-span-1">
            <div class="mb-2 font-medium">Categories</div>
            <ul class="space-y-2">
                @foreach ($categories as $cat)
                    <li class="flex items-center justify-between">
                        <button wire:click="selectCategory({{ $cat->id }})" class="text-left text-sm truncate">{{ $cat->emoji ? $cat->emoji . ' ' : '' }}{{ $cat->name }}</button>
                        <div class="flex gap-1">
                            <button wire:click="startEditCategory({{ $cat->id }})" class="text-xs text-zinc-500">Edit</button>
                            <button wire:click="deleteCategory({{ $cat->id }})" class="text-xs text-rose-600">Delete</button>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-4">
                <div class="flex gap-2">
                    <input wire:model.defer="newCategoryEmoji" type="text" class="w-20 rounded-md border px-2 py-1" placeholder="Emoji" />
                    <input wire:model.defer="newCategoryName" type="text" class="flex-1 rounded-md border px-2 py-1" placeholder="New category name" />
                </div>
                <button wire:click="createCategory" class="mt-2 inline-flex items-center px-3 py-1 rounded bg-sky-600 hover:bg-sky-500 text-white">Add category</button>
            </div>
        </div>

        <div class="col-span-2">
            @php $selectedCategory = $categories->firstWhere('id', $selectedCategoryId); @endphp
            <div class="mb-2 font-medium">Tags @if($selectedCategory) (for: {{ $selectedCategory->emoji ? $selectedCategory->emoji . ' ' : '' }}{{ $selectedCategory->name }}) @else (for selected category) @endif</div>
            <div class="mb-2 flex gap-2">
                <input wire:model.defer="newTagName" type="text" class="rounded-md border px-2 py-1 flex-1" placeholder="New tag name" />
                <button wire:click="createTag" class="inline-flex items-center px-3 py-1 rounded bg-sky-600 hover:bg-sky-500 text-white">Add tag</button>
            </div>

            <ul class="space-y-2">
                @foreach ($tags as $tag)
                    <li class="flex items-center justify-between rounded border p-2">
                        <div class="flex items-center gap-3">
                            <div class="font-medium">{{ $tag->name }}</div>
                            <div class="text-xs text-zinc-500">slug: {{ $tag->slug }}</div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="startEditTag({{ $tag->id }})" class="text-xs text-zinc-500">Edit</button>
                            <button wire:click="deleteTag({{ $tag->id }})" class="text-xs text-rose-600">Delete</button>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if($editingTagId)
                <div class="mt-4">
                    <input wire:model.defer="editingTagName" type="text" class="rounded-md border px-2 py-1" />
                    <input wire:model.defer="editingTagOrder" type="number" class="rounded-md border px-2 py-1 ml-2 w-24" />
                    <button wire:click="updateTag" class="ml-2 inline-flex items-center px-3 py-1 rounded bg-sky-600 hover:bg-sky-500 text-white">Save</button>
                </div>
            @endif

            @if($editingCategoryId)
                <div class="mt-4">
                    <label class="text-sm">Edit category emoji</label>
                    <input wire:model.defer="editingCategoryEmoji" type="text" class="rounded-md border px-2 py-1 ml-2 w-24" />
                    <button wire:click="updateCategory" class="ml-2 inline-flex items-center px-3 py-1 rounded bg-sky-600 hover:bg-sky-500 text-white">Save category</button>
                </div>
            @endif
        </div>
    </div>
</div>
