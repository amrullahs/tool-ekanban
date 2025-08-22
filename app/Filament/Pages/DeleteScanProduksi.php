<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class DeleteScanProduksi extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static string $view = 'filament.pages.delete-scan-produksi';
    protected static ?string $navigationLabel = 'Delete Scan Produksi';
    protected static ?string $title = 'Delete Scan Produksi';
    protected static ?string $navigationGroup = 'Tools';
    protected static ?int $navigationSort = 10;

    public ?string $barcodes = '';

    public function getBarcodeCountProperty(): int
    {
        if (empty($this->barcodes)) {
            return 0;
        }
        
        $lines = array_filter(array_map('trim', explode("\n", $this->barcodes)));
        return count($lines);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('barcodes')
                    ->label('Barcode List')
                    ->placeholder('Masukkan barcode, satu per baris...')
                    ->rows(10)
                    ->required()
                    ->helperText('Masukkan daftar barcode yang akan dihapus scan produksinya, satu barcode per baris.'),
            ]);
    }

    public function executeScript(): void
    {
        try {
            // Validasi input
            $validator = Validator::make(['barcodes' => $this->barcodes], [
                'barcodes' => 'required|string|min:1',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $barcodes = $this->barcodes;
            $barcodeList = array_filter(array_map('trim', explode("\n", $barcodes)));

            if (empty($barcodeList)) {
                Notification::make()
                    ->title('Error')
                    ->body('Tidak ada barcode yang valid ditemukan.')
                    ->danger()
                    ->send();
                return;
            }

            // Generate MongoDB script
            $mongoScript = $this->generateMongoScript($barcodeList);

            // Execute script via SSH
            $result = $this->executeMongoScriptViaSSH($mongoScript);

            if ($result['success']) {
                Notification::make()
                    ->title('Berhasil')
                    ->body('Script berhasil dieksekusi. ' . count($barcodeList) . ' barcode diproses.')
                    ->success()
                    ->send();
                
                // Clear form
                $this->form->fill(['barcodes' => '']);
            } else {
                Notification::make()
                    ->title('Error')
                    ->body('Gagal mengeksekusi script: ' . $result['message'])
                    ->danger()
                    ->send();
            }

        } catch (ValidationException $e) {
            Notification::make()
                ->title('Validation Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Log::error('Delete Scan Produksi Error: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function generateMongoScript(array $barcodeList): string
    {
        $barcodeListStr = implode('", "', $barcodeList);
        
        return '
use sdi_testing;
print("Database: sdi_testing");
print("Collection: m_kanban_generate_details");
print("Barcodes to process: ' . count($barcodeList) . '");

var barcodes = ["' . $barcodeListStr . '"];
var result = db.getCollection("m_kanban_generate_details").updateMany(
    { "barcode": { "$in": barcodes } },
    { "$set": { 
        "scan_fg_date": null, 
        "scan_fg_shift": null, 
        "scan_fg_pic": null 
    } }
);

print("Matched documents: " + result.matchedCount);
print("Modified documents: " + result.modifiedCount);
print("Script execution completed");
        ';
    }

    private function executeMongoScriptViaSSH(string $script): array
    {
        try {
            // SSH Configuration
            $sshHost = '38.47.67.48';
            $sshPort = 22;
            $sshUser = 'ekanban';
            $sshPassword = 'Sdi@2022';
            
            // MongoDB Configuration
            $mongoHost = '127.0.0.1';
            $mongoPort = 28118;
            
            // Create SSH connection
            $ssh = new SSH2($sshHost, $sshPort);
            if (!$ssh->login($sshUser, $sshPassword)) {
                return ['success' => false, 'message' => 'SSH login failed'];
            }
            
            // Create SFTP connection for file upload
            $sftp = new SFTP($sshHost, $sshPort);
            if (!$sftp->login($sshUser, $sshPassword)) {
                return ['success' => false, 'message' => 'SFTP login failed'];
            }
            
            // Create temporary script file
            $tempScriptPath = '/tmp/mongo_delete_scan_' . time() . '.js';
            
            // Upload script to server
            if (!$sftp->put($tempScriptPath, $script)) {
                return ['success' => false, 'message' => 'Failed to upload script'];
            }
            
            // Execute MongoDB script
            $command = "mongo --host {$mongoHost}:{$mongoPort} < {$tempScriptPath}";
            $output = $ssh->exec($command);
            
            // Clean up temporary file
            $sftp->delete($tempScriptPath);
            
            // Log the output for debugging
            Log::info('MongoDB Script Output: ' . $output);
            
            return ['success' => true, 'message' => 'Script executed successfully', 'output' => $output];
            
        } catch (\Exception $e) {
            Log::error('SSH/MongoDB Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('execute')
                ->label('Eksekusi Script')
                ->icon('heroicon-o-play')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Eksekusi')
                ->modalDescription('Apakah Anda yakin ingin mengeksekusi script ini? Tindakan ini akan menghapus data scan produksi secara permanen.')
                ->modalSubmitActionLabel('Ya, Eksekusi')
                ->action('executeScript'),
                
            Action::make('clear')
                ->label('Clear Form')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->action(function () {
                    $this->barcodes = '';
                    
                    Notification::make()
                        ->title('Form Cleared')
                        ->body('Form telah dibersihkan.')
                        ->success()
                        ->send();
                }),
        ];
    }


}