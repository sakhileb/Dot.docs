<div>
    @if($show)
    {{-- Backdrop --}}
    <div class="fixed inset-0 z-40 bg-black/50" wire:click="$set('show', false)"></div>

    {{-- Modal --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Save as Template</h2>
                <button wire:click="$set('show', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template Name</label>
                    <input wire:model="name" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select wire:model="category" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" class="capitalize">{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description <span class="text-gray-400">(optional)</span></label>
                    <textarea wire:model="description" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Brief description of this template…"></textarea>
                </div>

                @if(auth()->user()->currentTeam)
                <div class="flex items-center gap-2">
                    <input wire:model="shareWithTeam" type="checkbox" id="share-team" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <label for="share-team" class="text-sm text-gray-700 dark:text-gray-300">Share with {{ auth()->user()->currentTeam->name }}</label>
                </div>
                @endif
            </div>

            <div class="px-6 pb-5 flex justify-end gap-2">
                <button wire:click="$set('show', false)" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition">Cancel</button>
                <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg font-medium transition">
                    <span wire:loading.class="opacity-50">Save Template</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
