<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Information -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                🗑️ Delete Scan Produksi Tool
            </h3>
            
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Informasi Tool</h4>
                        <div class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                            Tool ini akan menghapus data scan produksi (set field menjadi null) berdasarkan daftar barcode yang diinput.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            {{ $this->form }}
        </div>

        <!-- Execution Log -->
        @if(!empty($executionLog))
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                📋 Hasil Eksekusi
            </h3>
            
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 font-mono text-sm overflow-auto max-h-96">
                {!! $executionLog !!}
            </div>
        </div>
        @endif
        
        <!-- Processing Result -->
        @if(!empty($processingResult))
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                📊 Ringkasan Hasil
            </h3>
            
            @if(isset($processingResult['summary']))
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $processingResult['summary']['total'] }}</div>
                    <div class="text-sm text-blue-800 dark:text-blue-300">Total Barcode</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $processingResult['summary']['updated'] }}</div>
                    <div class="text-sm text-green-800 dark:text-green-300">Berhasil Update</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $processingResult['summary']['not_found'] }}</div>
                    <div class="text-sm text-yellow-800 dark:text-yellow-300">Tidak Ditemukan</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $processingResult['summary']['failed'] }}</div>
                    <div class="text-sm text-red-800 dark:text-red-300">Gagal Update</div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Warning Section -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-red-800 dark:text-red-200">
                        ⚠️ PERINGATAN PENTING
                    </h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Backup Data:</strong> Pastikan Anda telah melakukan backup database sebelum menjalankan script ini.</li>
                            <li><strong>Operasi Irreversible:</strong> Tindakan ini akan menghapus data scan produksi secara permanen dan tidak dapat dibatalkan.</li>
                            <li><strong>Verifikasi Barcode:</strong> Pastikan daftar barcode yang diinput sudah benar dan sesuai dengan yang ingin dihapus.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>