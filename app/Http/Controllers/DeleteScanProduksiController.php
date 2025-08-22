<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;

class DeleteScanProduksiController extends Controller
{
    /**
     * Menampilkan halaman form delete scan produksi
     */
    public function index()
    {
        return view('delete-scan-produksi.index');
    }

    /**
     * Memproses eksekusi script delete scan produksi
     */
    public function execute(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'barcode_list' => 'required|string|min:1',
        ], [
            'barcode_list.required' => 'Daftar barcode harus diisi',
            'barcode_list.min' => 'Daftar barcode tidak boleh kosong',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Parse barcode list dari textarea
            $barcodeText = trim($request->barcode_list);
            $barcodeArray = array_filter(array_map('trim', explode("\n", $barcodeText)));
            
            if (empty($barcodeArray)) {
                return back()->with('error', 'Tidak ada barcode yang valid ditemukan')->withInput();
            }

            // Generate MongoDB script
            $mongoScript = $this->generateMongoScript($barcodeArray);
            
            // Eksekusi script via SSH
            $result = $this->executeMongoScriptViaSSH($mongoScript);
            
            if ($result['success']) {
                return back()->with('success', 'Script berhasil dieksekusi. ' . $result['message'])->withInput();
            } else {
                return back()->with('error', 'Gagal mengeksekusi script: ' . $result['message'])->withInput();
            }
            
        } catch (\Exception $e) {
            Log::error('Error executing delete scan produksi script: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Generate MongoDB script berdasarkan barcode list
     */
    private function generateMongoScript(array $barcodeList)
    {
        $barcodeJson = json_encode($barcodeList, JSON_UNESCAPED_SLASHES);
        
        return '
// Pastikan menggunakan database yang benar
use sdi_testing;

// List barcode yang akan di-update
var barcodeList = ' . $barcodeJson . ';

print("======================================");
print("PROSES UPDATE FIELD MENJADI NULL");
print("Database: sdi_testing");
print("Barcode: " + JSON.stringify(barcodeList));
print("======================================\n");

// STEP 1: Verifikasi dokumen yang akan di-update
print("🔍 Mencari dokumen dengan barcode yang sesuai...");
var documentsToUpdate = db.getCollection("m_kanban_generate_details")
    .find({ "barcode": { $in: barcodeList } })
    .toArray();

if (documentsToUpdate.length === 0) {
    print("❌ Tidak ada dokumen yang ditemukan dengan barcode tersebut.");
    quit();
}
print("✅ Ditemukan " + documentsToUpdate.length + " dokumen.\n");

// STEP 2: Eksekusi update
print("🔄 Memproses update field menjadi null...");
var updateResult = db.getCollection("m_kanban_generate_details").updateMany(
    { "barcode": { $in: barcodeList } },
    {
        $set: {
            "parent_item": null,
            "scan_produksi_id": null,
            "scanned_produksi_at": null,
            "status_submit_produksi": null,
            "lot_prod": null,
            "submit_produksi_at": null
        }
    }
);

// STEP 3: Verifikasi hasil update
print("🔍 Memverifikasi hasil update...");
var updatedDocuments = db.getCollection("m_kanban_generate_details")
    .find({ "barcode": { $in: barcodeList } })
    .toArray();

var successfullyUpdated = 0;
updatedDocuments.forEach(function(doc) {
    var isNull = doc.parent_item === null &&
                 doc.scan_produksi_id === null &&
                 doc.scanned_produksi_at === null &&
                 doc.status_submit_produksi === null &&
                 doc.lot_prod === null &&
                 doc.submit_produksi_at === null;

    if (isNull) successfullyUpdated++;
});

print("\n======================================");
print("📊 HASIL UPDATE:");
print("   ✅ Jumlah dokumen ditemukan: " + documentsToUpdate.length);
print("   ✅ Jumlah dokumen di-update: " + updateResult.modifiedCount);
print("   ✅ Berhasil di-set ke null: " + successfullyUpdated);
print("🏁 Selesai: " + new Date().toISOString());
print("======================================");
';
    }

    /**
     * Eksekusi MongoDB script via SSH
     */
    private function executeMongoScriptViaSSH($mongoScript)
    {
        try {
            // Konfigurasi SSH
            $sshHost = '38.47.67.48';
            $sshPort = 22;
            $sshUser = 'ubuntu';
            $sshPassword = '54nk31Dharma2025..!';
            $mongoHost = '127.0.0.1';
            $mongoPort = 28118;
            
            // Simpan script ke file temporary
            $tempScriptPath = '/tmp/mongo_delete_scan_' . time() . '.js';
            
            // Buat koneksi SSH menggunakan phpseclib
            $ssh = new SSH2($sshHost, $sshPort);
            if (!$ssh->login($sshUser, $sshPassword)) {
                throw new \Exception('Gagal autentikasi SSH ke server ' . $sshHost);
            }
            
            // Upload script ke server menggunakan SFTP
            $sftp = new SFTP($sshHost, $sshPort);
            if (!$sftp->login($sshUser, $sshPassword)) {
                throw new \Exception('Gagal autentikasi SFTP ke server ' . $sshHost);
            }
            
            if (!$sftp->put($tempScriptPath, $mongoScript)) {
                throw new \Exception('Gagal mengupload script ke server');
            }
            
            // Eksekusi script MongoDB
            $mongoCommand = "mongo --host {$mongoHost}:{$mongoPort} < {$tempScriptPath}";
            $output = $ssh->exec($mongoCommand);
            
            if ($output === false) {
                throw new \Exception('Gagal mengeksekusi command MongoDB');
            }
            
            // Hapus file temporary
            $ssh->exec("rm -f {$tempScriptPath}");
            
            // Log output untuk debugging
            Log::info('MongoDB Script Output: ' . $output);
            
            return [
                'success' => true,
                'message' => 'Script berhasil dieksekusi. Output: ' . substr($output, 0, 500) . (strlen($output) > 500 ? '...' : ''),
                'full_output' => $output
            ];
            
        } catch (\Exception $e) {
            Log::error('SSH/MongoDB execution error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}