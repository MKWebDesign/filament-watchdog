<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Forensic Analysis</h3>
            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                Start Analysis
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white mb-3">File System Analysis</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Files scanned:</span>
                        <span class="text-gray-900 dark:text-white font-medium">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Changes detected:</span>
                        <span class="text-gray-900 dark:text-white font-medium">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Last scan:</span>
                        <span class="text-gray-900 dark:text-white font-medium">Never</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white mb-3">Network Analysis</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Connections monitored:</span>
                        <span class="text-gray-900 dark:text-white font-medium">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Suspicious activity:</span>
                        <span class="text-gray-900 dark:text-white font-medium">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Blocked IPs:</span>
                        <span class="text-gray-900 dark:text-white font-medium">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h4 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Ready for Analysis</h4>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Click "Start Analysis" to begin forensic examination.</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
