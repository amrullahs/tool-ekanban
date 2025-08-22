<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\Client as MongoClient;

class DeleteScanProduksi extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static string $view = 'filament.pages.delete-scan-produksi';
    protected static ?string $title = 'Delete Scan Produksi';
    protected static ?string $navigationLabel = 'Delete Scan Produksi';
    protected static ?string $navigationGroup = 'Tools';
    
    public ?string $barcode_input = null;
    public bool $confirmed = false;
    public array $processingResult = [];
    public string $executionLog = '';
    
    // SSH Configuration
    public bool $ssh_enabled = false;
    public ?string $ssh_host = null;
    public ?string $ssh_port = '22';
    public ?string $ssh_user = null;
    public ?string $ssh_password = null;
    
    // MongoDB Configuration
    public ?string $mongo_host = 'localhost';
    public ?string $mongo_port = '27017';
    public ?string $mongo_database = 'sdi_testing';
    public ?string $mongo_collection = 'm_kanban_generate_details';
    
    public function mount(): void
    {
        // Load configuration from config file
        $config = config('delete-scan-produksi');
        
        // Load SSH configuration
        $this->ssh_enabled = $config['ssh']['enabled'] ?? false;
        $this->ssh_host = $config['ssh']['host'] ?? null;
        $this->ssh_port = $config['ssh']['port'] ?? '22';
        $this->ssh_user = $config['ssh']['user'] ?? null;
        $this->ssh_password = $config['ssh']['password'] ?? null;
        
        // Load MongoDB configuration
        $this->mongo_host = $config['mongoHost'] ?? 'localhost';
        $this->mongo_port = $config['mongoPort'] ?? '27017';
        $this->mongo_database = $config['mongoDatabase'] ?? 'sdi_testing';
        $this->mongo_collection = $config['mongoCollection'] ?? 'm_kanban_generate_details';
        
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Konfigurasi MongoDB')
                    ->description('Konfigurasi koneksi database MongoDB')
                    ->schema([
                        TextInput::make('mongo_host')
                            ->label('MongoDB Host')
                            ->required()
                            ->default('localhost'),
                        TextInput::make('mongo_port')
                            ->label('MongoDB Port')
                            ->required()
                            ->default('27017'),
                        TextInput::make('mongo_database')
                            ->label('Database Name')
                            ->required()
                            ->default('sdi_testing'),
                        TextInput::make('mongo_collection')
                            ->label('Collection Name')
                            ->required()
                            ->default('m_kanban_generate_details'),
                    ])
                    ->columns(2)
                    ->collapsible(),
                    
                Section::make('Konfigurasi SSH (Opsional)')
                    ->description('Konfigurasi koneksi SSH untuk akses remote')
                    ->schema([
                        Toggle::make('ssh_enabled')
                            ->label('Aktifkan SSH')
                            ->live(),
                        TextInput::make('ssh_host')
                            ->label('SSH Host')
                            ->visible(fn ($get) => $get('ssh_enabled')),
                        TextInput::make('ssh_port')
                            ->label('SSH Port')
                            ->default('22')
                            ->visible(fn ($get) => $get('ssh_enabled')),
                        TextInput::make('ssh_user')
                            ->label('SSH Username')
                            ->visible(fn ($get) => $get('ssh_enabled')),
                        TextInput::make('ssh_password')
                            ->label('SSH Password')
                            ->password()
                            ->visible(fn ($get) => $get('ssh_enabled')),
                    ])
                    ->columns(2)
                    ->collapsible(),
                    
                Section::make('Pemrosesan Barcode')
                    ->description('Input barcode yang akan diproses')
                    ->schema([
                        Textarea::make('barcode_input')
                            ->label('Daftar Barcode')
                            ->placeholder('Masukkan barcode, satu per baris...')
                            ->rows(10)
                            ->required()
                            ->helperText('Masukkan satu barcode per baris. Contoh: BC001, BC002, BC003'),
                            
                        Checkbox::make('confirmed')
                            ->label('Saya memahami bahwa operasi ini akan menghapus data secara permanen')
                            ->required()
                            ->helperText('Centang untuk mengkonfirmasi bahwa Anda memahami risiko operasi ini'),
                    ]),
            ]);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('execute')
                ->label('Eksekusi Script')
                ->color('danger')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Eksekusi')
                ->modalDescription('Apakah Anda yakin ingin menjalankan script penghapusan? Operasi ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Eksekusi')
                ->action('executeScript'),
        ];
    }
    
    public function executeScript(): void
    {
        $this->validate();
        
        if (!$this->confirmed) {
            Notification::make()
                ->title('Konfirmasi Diperlukan')
                ->body('Anda harus mengkonfirmasi pemahaman risiko operasi ini.')
                ->danger()
                ->send();
            return;
        }
        
        try {
            // Use form configuration
            $config = [
                'mongoHost' => $this->mongo_host,
                'mongoPort' => $this->mongo_port,
                'mongoDatabase' => $this->mongo_database,
                'mongoCollection' => $this->mongo_collection,
                'fieldsToNullify' => [
                    "parent_item",
                    "scan_produksi_id",
                    "scanned_produksi_at",
                    "status_submit_produksi",
                    "lot_prod",
                    "submit_produksi_at"
                ],
                'ssh' => [
                    'enabled' => $this->ssh_enabled,
                    'host' => $this->ssh_host,
                    'port' => $this->ssh_port,
                    'user' => $this->ssh_user,
                    'password' => $this->ssh_password,
                ]
            ];
            
            // Parse barcodes
            $barcodes = array_filter(
                array_map('trim', explode("\n", $this->barcode_input)),
                fn($barcode) => !empty($barcode)
            );
            
            if (empty($barcodes)) {
                throw new \Exception('Tidak ada barcode yang valid ditemukan.');
            }
            
            // Initialize log
            $this->executionLog = "[" . date('Y-m-d H:i:s') . "] 🚀 Memulai eksekusi untuk " . count($barcodes) . " barcode\n";
            
            // Process barcodes
            $result = $this->processBarcodes($barcodes, $config);
            
            // Update result
            $this->processingResult = $result;
            
            // Add completion log
            $this->executionLog .= "[" . date('Y-m-d H:i:s') . "] 🎉 Eksekusi selesai!\n";
            $this->executionLog .= "[" . date('Y-m-d H:i:s') . "] 📊 Total: {$result['total']}, Updated: {$result['updated']}, Not Found: {$result['not_found']}, Failed: {$result['failed']}\n";
            
            // Show appropriate notification based on results
            if ($result['updated'] > 0) {
                Notification::make()
                    ->title('Eksekusi Berhasil')
                    ->body("Berhasil memproses {$result['total']} barcode. {$result['updated']} berhasil diupdate.")
                    ->success()
                    ->send();
            } elseif ($result['not_found'] > 0 && $result['failed'] == 0) {
                Notification::make()
                    ->title('Tidak Ada Data Ditemukan')
                    ->body("Memproses {$result['total']} barcode. {$result['not_found']} tidak ditemukan di database. Periksa nama database dan collection.")
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('Eksekusi Gagal')
                    ->body("Memproses {$result['total']} barcode. {$result['failed']} gagal diproses.")
                    ->danger()
                    ->send();
            }
                
        } catch (\Exception $e) {
            $this->executionLog .= "[" . date('Y-m-d H:i:s') . "] ❌ Error: " . $e->getMessage() . "\n";
            
            Log::error('Delete Scan Produksi Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            Notification::make()
                ->title('Eksekusi Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    private function processBarcodes(array $barcodes, array $config): array
    {
        $result = [
            'total' => count($barcodes),
            'updated' => 0,
            'not_found' => 0,
            'failed' => 0
        ];
        
        try {
            // Connect to MongoDB using direct client
            $mongoUri = "mongodb://{$config['mongoHost']}:{$config['mongoPort']}";
            $client = new MongoClient($mongoUri);
            $database = $client->selectDatabase($config['mongoDatabase']);
            $collection = $database->selectCollection($config['mongoCollection']);
            
            foreach ($barcodes as $index => $barcode) {
                $current = $index + 1;
                $this->executionLog .= "[" . date('Y-m-d H:i:s') . "] 🔄 Memproses barcode: {$barcode} ({$current}/{$result['total']})\n";
                
                try {
                    // Find document
                    $document = $collection->findOne(['barcode' => $barcode]);
                    
                    if (!$document) {
                        $this->executionLog .= "[" . date('Y-m-d H:i:s') . "] ❓ Tidak ditemukan: {$barcode}\n";
                        $result['not_found']++;
                        continue;
                    }
                    
                    $this->executionLog .= "[" . date('Y-m-d H:i:s') . "] 🔍 Ditemukan: {$barcode}\n";
                    
                    // Update document
                    $updateData = [];
                    foreach ($config['fieldsToNullify'] as $field) {
                        $updateData[$field] = null;
                    }
                    
                    $updateResult = $collection->updateOne(
                        ['barcode' => $barcode],
                        ['$set' => $updateData]
                    );
                    
                    if ($updateResult->getModifiedCount() > 0) {
                        $this->executionLog .= "[" . date('Y-m-d H:i:s') . "] ✅ Berhasil diupdate: {$barcode}\n";
                        $result['updated']++;
                    } else {
                        $this->executionLog .= "[" . date('Y-m-d H:i:s') . "] ❌ Gagal update: {$barcode}\n";
                        $result['failed']++;
                    }
                    
                } catch (\Exception $e) {
                    $this->executionLog .= "[" . date('Y-m-d H:i:s') . "] ❌ Error processing {$barcode}: " . $e->getMessage() . "\n";
                    $result['failed']++;
                }
            }
            
        } catch (\Exception $e) {
            throw new \Exception('MongoDB connection error: ' . $e->getMessage());
        }
        
        return $result;
    }
}