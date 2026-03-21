<div x-data="{ 
    activeMedia: null,
    items: {{ json_encode($getState()) }},
    init() {
        if (this.items && this.items.length > 0) {
            this.activeMedia = this.items[0];
        }
    }
}" class="fi-fzf-gallery flex border border-gray-200 rounded-lg overflow-hidden h-[600px] bg-white dark:bg-gray-900 dark:border-gray-700 w-full">
    <!-- File List (Left) -->
    <div class="w-2/5 min-w-[250px] border-r border-gray-200 dark:border-gray-700 overflow-y-auto bg-gray-50 dark:bg-gray-800/50 flex-shrink-0">
        <template x-for="(item, index) in items" :key="index">
            <div 
                @click="activeMedia = item"
                :class="{ 'bg-primary-500 !text-white': activeMedia === item, 'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300': activeMedia !== item }"
                class="px-4 py-3 cursor-pointer transition-colors flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 last:border-0 group"
            >
                <div class="flex-shrink-0 w-10 h-10 rounded border border-gray-200 dark:border-gray-600 overflow-hidden bg-white flex items-center justify-center">
                    <template x-if="item.thumb">
                        <img :src="item.thumb" class="w-full h-full object-cover" @@error="$el.src = 'https://placehold.co/100x100?text=No+Thumb'" />
                    </template>
                    <template x-if="!item.thumb">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </template>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold" x-text="item.name"></div>
                    <div class="text-[10px] opacity-70 uppercase tracking-tight" x-text="item.size"></div>
                </div>
            </div>
        </template>
        <div x-show="!items || items.length === 0" class="p-8 text-center text-gray-500 italic">
            No files available.
        </div>
    </div>

    <!-- Preview Pane (Right) -->
    <div class="flex-1 flex flex-col bg-gray-100 dark:bg-black/20 min-w-0 overflow-hidden">
        <div class="flex-1 flex items-center justify-center p-6 relative group overflow-hidden">
            <template x-if="activeMedia">
                <div class="relative w-full h-full flex items-center justify-center">
                    <img 
                        :src="activeMedia.url" 
                        class="max-w-full max-h-full object-contain shadow-2xl rounded-sm"
                        @@error="$el.src = 'https://placehold.co/600x400?text=Image+Not+Found'"
                    />
                    <a 
                        :href="activeMedia.url" 
                        target="_blank" 
                        class="absolute top-4 right-4 bg-black/60 hover:bg-black/80 text-white p-2 rounded-full transition-opacity opacity-0 group-hover:opacity-100 flex items-center justify-center shadow-lg"
                        title="Open Original"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </div>
            </template>
            <template x-if="!activeMedia">
                <div class="text-gray-400 flex flex-col items-center gap-4">
                    <svg class="w-16 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-xs uppercase tracking-widest font-medium">Select a file to preview</span>
                </div>
            </template>
        </div>
        
        <!-- Metadata bar -->
        <div x-show="activeMedia" class="px-6 py-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-widest font-bold">
            <div class="flex items-center gap-4">
                <span x-text="activeMedia.name" class="text-gray-900 dark:text-gray-100"></span>
                <span class="opacity-30">|</span>
                <span x-text="activeMedia.size"></span>
            </div>
            <div>
                <span x-text="(items.indexOf(activeMedia) + 1) + ' / ' + items.length"></span>
            </div>
        </div>
    </div>
</div>

<style>
    .fi-fzf-gallery ::-webkit-scrollbar {
        width: 4px;
    }
    .fi-fzf-gallery ::-webkit-scrollbar-track {
        background: transparent;
    }
    .fi-fzf-gallery ::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    .dark .fi-fzf-gallery ::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
    }
</style>
