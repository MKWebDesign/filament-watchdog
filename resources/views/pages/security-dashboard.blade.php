<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2">
            <x-filament::section>
                <x-slot name="heading">
                    System Status
                </x-slot>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 {{ $systemStatus['fileMonitoring'] ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-full flex items-center justify-center">
                                @if($systemStatus['fileMonitoring'])
                                    <x-heroicon-o-check class="w-5 h-5 text-green-600 dark:text-green-400" />
                                @else
                                    <x-heroicon-o-x-mark class="w-5 h-5 text-red-600 dark:text-red-400" />
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">File Monitoring</div>
                            <div class="text-sm {{ $systemStatus['fileMonitoring'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $systemStatus['fileMonitoring'] ? 'Active' : 'Disabled' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 {{ $systemStatus['malwareDetection'] ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-full flex items-center justify-center">
                                @if($systemStatus['malwareDetection'])
                                    <x-heroicon-o-check class="w-5 h-5 text-green-600 dark:text-green-400" />
                                @else
                                    <x-heroicon-o-x-mark class="w-5 h-5 text-red-600 dark:text-red-400" />
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Malware Detection</div>
                            <div class="text-sm {{ $systemStatus['malwareDetection'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $systemStatus['malwareDetection'] ? 'Active' : 'Disabled' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 {{ $systemStatus['activityMonitoring'] ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-full flex items-center justify-center">
                                @if($systemStatus['activityMonitoring'])
                                    <x-heroicon-o-check class="w-5 h-5 text-green-600 dark:text-green-400" />
                                @else
                                    <x-heroicon-o-x-mark class="w-5 h-5 text-red-600 dark:text-red-400" />
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Activity Monitoring</div>
                            <div class="text-sm {{ $systemStatus['activityMonitoring'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $systemStatus['activityMonitoring'] ? 'Active' : 'Disabled' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 {{ $systemStatus['alertSystem'] ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-full flex items-center justify-center">
                                @if($systemStatus['alertSystem'])
                                    <x-heroicon-o-check class="w-5 h-5 text-green-600 dark:text-green-400" />
                                @else
                                    <x-heroicon-o-x-mark class="w-5 h-5 text-red-600 dark:text-red-400" />
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Alert System</div>
                            <div class="text-sm {{ $systemStatus['alertSystem'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $systemStatus['alertSystem'] ? 'Active' : 'Disabled' }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <div>
            <x-filament::section>
                <x-slot name="heading">
                    System Statistics
                </x-slot>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Files Monitored</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['totalFiles']) }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Modified Files</span>
                        <span class="text-lg font-semibold {{ $stats['modifiedFiles'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ number_format($stats['modifiedFiles']) }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Malware Detected</span>
                        <span class="text-lg font-semibold {{ $stats['malwareDetections'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ number_format($stats['malwareDetections']) }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Unresolved Alerts</span>
                        <span class="text-lg font-semibold {{ $stats['unresolvedAlerts'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ number_format($stats['unresolvedAlerts']) }}
                        </span>
                    </div>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
