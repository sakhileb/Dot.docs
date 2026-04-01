<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 space-y-5">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Build Form Fields</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add reusable fields, preview the generated HTML form, then sync it directly into the document.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <x-label for="label" value="Field Label" />
                <x-input id="label" type="text" class="mt-1 block w-full" wire:model="label" placeholder="Customer Name" />
                <x-input-error for="label" class="mt-1" />
            </div>

            <div>
                <x-label for="fieldType" value="Field Type" />
                <select id="fieldType" wire:model.live="fieldType" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="text">Text</option>
                    <option value="email">Email</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Select</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                </select>
                <x-input-error for="fieldType" class="mt-1" />
            </div>

            <div>
                <x-label for="placeholder" value="Placeholder" />
                <x-input id="placeholder" type="text" class="mt-1 block w-full" wire:model="placeholder" placeholder="Enter a value" />
                <x-input-error for="placeholder" class="mt-1" />
            </div>

            <div class="md:col-span-2">
                <x-label for="helpText" value="Help Text" />
                <x-input id="helpText" type="text" class="mt-1 block w-full" wire:model="helpText" placeholder="Shown below the field in the generated form" />
                <x-input-error for="helpText" class="mt-1" />
            </div>

            @if (in_array($fieldType, ['select', 'radio'], true))
                <div class="md:col-span-2">
                    <x-label for="optionsText" value="Options" />
                    <textarea id="optionsText" wire:model="optionsText" rows="5" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="basic|Basic Plan&#10;pro|Pro Plan"></textarea>
                    <x-input-error for="optionsText" class="mt-1" />
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Enter one option per line. Use <span class="font-semibold">value|Label</span> to control the submitted value.</p>
                </div>
            @endif

            <div class="md:col-span-2 flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="isRequired" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    Required field
                </label>

                <x-button wire:click="addField">Add Field</x-button>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Current Fields</h4>
                <button type="button" wire:click="syncToDocument" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition">
                    Sync To Document
                </button>
            </div>

            <div class="space-y-3">
                @forelse($fields as $field)
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $field->label }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ strtoupper($field->field_type) }} · name={{ $field->name }}{{ $field->is_required ? ' · required' : '' }}</p>
                            @if (!empty($field->help_text))
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $field->help_text }}</p>
                            @endif
                        </div>

                        <button type="button" wire:click="deleteField({{ $field->id }})" class="text-sm text-rose-600 hover:text-rose-700">
                            Remove
                        </button>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-6 text-sm text-center text-gray-500 dark:text-gray-400">
                        No form fields yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Generated Preview</h3>

        @if ($previewMarkup)
            <div class="prose max-w-none dark:prose-invert">
                {!! $previewMarkup !!}
            </div>
        @else
            <div class="h-80 flex items-center justify-center rounded-lg border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400">
                Add fields to generate a live preview.
            </div>
        @endif
    </div>
</div>