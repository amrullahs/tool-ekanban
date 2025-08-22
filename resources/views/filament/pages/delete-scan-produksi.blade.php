<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Server Information Card -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-server class="w-5 h-5" />
                    Informasi Server
                </div>
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 dark:text-gray-400">SSH Server:</span>
                        <span class="text-gray-900 dark:text-gray-100">38.47.67.48:22</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 dark:text-gray-400">User:</span>
                        <span class="text-gray-900 dark:text-gray-100">ekanban</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 dark:text-gray-400">MongoDB:</span>
                        <span class="text-gray-900 dark:text-gray-100">127.0.0.1:28118</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 dark:text-gray-400">Database:</span>
                        <span class="text-gray-900 dark:text-gray-100">sdi_testing</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 dark:text-gray-400">Collection:</span>
                        <span class="text-gray-900 dark:text-gray-100">m_kanban_generate_details</span>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Main Form -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-5 h-5" />
                    Input Barcode
                </div>
            </x-slot>
            
            <form wire:submit="executeScript">
                {{ $this->form }}
                
                <div class="mt-6 flex justify-end gap-3">
                    <x-filament-actions::actions :actions="$this->getActions()" />
                </div>
            </form>
        </x-filament::section>

        <!-- Warning Section -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-warning-600">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    Peringatan Penting
                </div>
            </x-slot>
            
            <div class="bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg p-4">
                <ul class="space-y-2 text-sm text-warning-800 dark:text-warning-200">
                    <li class="flex items-start gap-2">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 mt-0.5 flex-shrink-0" />
                        Script ini akan <strong>menghapus data scan produksi</strong> secara permanen
                    </li>
                    <li class="flex items-start gap-2">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 mt-0.5 flex-shrink-0" />
                        Pastikan barcode yang dimasukkan sudah <strong>benar dan sesuai</strong>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 mt-0.5 flex-shrink-0" />
                        <strong>Backup data</strong> sebelum menjalankan script ini
                    </li>
                    <li class="flex items-start gap-2">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 mt-0.5 flex-shrink-0" />
                        Operasi ini <strong>tidak dapat dibatalkan</strong> setelah dieksekusi
                    </li>
                </ul>
            </div>
        </x-filament::section>

        <!-- Instructions Section -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-information-circle class="w-5 h-5" />
                    Petunjuk Penggunaan
                </div>
            </x-slot>
            
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-start gap-2">
                    <span class="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-full w-6 h-6 flex items-center justify-center text-xs font-medium flex-shrink-0">1</span>
                    <span>Masukkan daftar barcode pada textarea di atas, satu barcode per baris</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-full w-6 h-6 flex items-center justify-center text-xs font-medium flex-shrink-0">2</span>
                    <span>Pastikan semua barcode sudah benar dan sesuai dengan data yang ingin dihapus</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-full w-6 h-6 flex items-center justify-center text-xs font-medium flex-shrink-0">3</span>
                    <span>Klik tombol "Eksekusi Script" untuk menjalankan proses penghapusan</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-full w-6 h-6 flex items-center justify-center text-xs font-medium flex-shrink-0">4</span>
                    <span>Konfirmasi eksekusi pada dialog yang muncul</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-full w-6 h-6 flex items-center justify-center text-xs font-medium flex-shrink-0">5</span>
                    <span>Tunggu hingga proses selesai dan periksa notifikasi hasil</span>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>