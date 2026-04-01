<div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">AI Document & Image Generator</h3>

        <div>
            <x-label for="type" value="Generator Type" />
            <select id="type" wire:model="type" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="general">General Document</option>
                <option value="outline">Outline Generator</option>
                <option value="blog">Blog / Article</option>
                <option value="email">Email / Letter</option>
                <option value="report">Report / Proposal</option>
                <option value="seo">SEO Content</option>
                <option value="translation">Translation</option>
            </select>
        </div>

        <div>
            <x-label for="topic" value="Topic" />
            <x-input id="topic" type="text" class="mt-1 block w-full" wire:model="topic" placeholder="e.g. Quarterly Marketing Plan" />
            <x-input-error for="topic" class="mt-1" />
        </div>

        <div>
            <x-label for="prompt" value="Prompt" />
            <textarea id="prompt" wire:model="prompt" rows="5" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="Describe exactly what you want to generate..."></textarea>
            <x-input-error for="prompt" class="mt-1" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <x-label for="tone" value="Tone" />
                <select id="tone" wire:model="tone" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="professional">Professional</option>
                    <option value="friendly">Friendly</option>
                    <option value="formal">Formal</option>
                    <option value="casual">Casual</option>
                    <option value="persuasive">Persuasive</option>
                </select>
            </div>

            <div>
                <x-label for="length" value="Length" />
                <select id="length" wire:model="length" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="short">Short</option>
                    <option value="medium">Medium</option>
                    <option value="long">Long</option>
                </select>
            </div>
        </div>

        <div>
            <x-label for="audience" value="Target Audience" />
            <x-input id="audience" type="text" class="mt-1 block w-full" wire:model="audience" />
        </div>

        @if ($type === 'translation')
            <div>
                <x-label for="targetLanguage" value="Target Language" />
                <x-input id="targetLanguage" type="text" class="mt-1 block w-full" wire:model="targetLanguage" placeholder="e.g. French, isiZulu, Spanish" />
            </div>
        @endif

        <x-button class="w-full justify-center" wire:click="generate" wire:loading.attr="disabled" wire:target="generate">
            <span wire:loading.remove wire:target="generate">Generate Document</span>
            <span wire:loading wire:target="generate">Generating...</span>
        </x-button>

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">AI Image Generation</h4>

            <div>
                <x-label for="imagePrompt" value="Image Prompt" />
                <textarea id="imagePrompt" wire:model="imagePrompt" rows="3" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="Describe the image you want..."></textarea>
                <x-input-error for="imagePrompt" class="mt-1" />
            </div>

            <div>
                <x-label for="imageSize" value="Image Size" />
                <select id="imageSize" wire:model="imageSize" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="256x256">256 x 256</option>
                    <option value="512x512">512 x 512</option>
                    <option value="1024x1024">1024 x 1024</option>
                </select>
            </div>

            <x-button class="w-full justify-center" wire:click="generateImage" wire:loading.attr="disabled" wire:target="generateImage">
                <span wire:loading.remove wire:target="generateImage">Generate Image</span>
                <span wire:loading wire:target="generateImage">Generating image...</span>
            </x-button>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Generated Preview</h3>
            <x-button wire:click="saveAsDocument" wire:loading.attr="disabled" wire:target="saveAsDocument">Save As Document</x-button>
        </div>

        @if ($generatedImageUrl)
            <div class="mb-4">
                <x-label value="Generated Image" />
                <img src="{{ $generatedImageUrl }}" alt="Generated image" class="mt-2 max-h-72 rounded-md border border-gray-200 dark:border-gray-600" />
            </div>
        @endif

        @if ($generatedTitle)
            <div class="space-y-4">
                <div>
                    <x-label value="Title" />
                    <x-input type="text" class="mt-1 block w-full" wire:model="generatedTitle" />
                </div>

                <div>
                    <x-label value="Content" />
                    <textarea wire:model="generatedContent" rows="18" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="md:col-span-2">
                        <x-label value="SEO Title" />
                        <x-input type="text" class="mt-1 block w-full" wire:model="seoTitle" />
                    </div>
                    <div class="md:col-span-2">
                        <x-label value="SEO Description" />
                        <textarea wire:model="seoDescription" rows="3" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <x-label value="SEO Keywords" />
                        <x-input type="text" class="mt-1 block w-full" wire:model="seoKeywords" placeholder="keyword one, keyword two" />
                    </div>
                </div>
            </div>
        @else
            <div class="h-[520px] flex items-center justify-center rounded-lg border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400">
                Generate a document to preview and edit it here.
            </div>
        @endif
    </div>
</div>
</div>
