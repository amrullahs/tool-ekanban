<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Scan Produksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-execute {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-execute:hover {
            background: linear-gradient(45deg, #c82333, #a71e2a);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        .alert {
            border: none;
            border-radius: 0.5rem;
        }
        .textarea-container {
            position: relative;
        }
        .char-counter {
            position: absolute;
            bottom: 10px;
            right: 15px;
            font-size: 0.8rem;
            color: #6c757d;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-trash-alt me-2"></i>
                            Delete Scan Produksi
                        </h4>
                        <small>Menghapus data scan produksi berdasarkan daftar barcode</small>
                    </div>
                    <div class="card-body">
                        <!-- Alert Messages -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Terjadi kesalahan:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Form -->
                        <form method="POST" action="{{ route('delete-scan-produksi.execute') }}" id="deleteForm">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="barcode_list" class="form-label fw-bold">
                                    <i class="fas fa-barcode me-2"></i>
                                    Daftar Barcode
                                </label>
                                <div class="textarea-container">
                                    <textarea 
                                        class="form-control @error('barcode_list') is-invalid @enderror" 
                                        id="barcode_list" 
                                        name="barcode_list" 
                                        rows="15" 
                                        placeholder="Masukkan daftar barcode, satu barcode per baris...&#10;&#10;Contoh:&#10;DNQR-1752453053-BRKT FR DR LWR RH67461-BZ110D03 FRAMEPRBRK17001012&#10;DNQR-1755131443-BRACKET, FR RR LWR UP RH67453-BZ140D26 FRAMEPRBRK09001001&#10;DNQR-1754872257-BRACKET, FR RR LWR UP RH67453-BZ140D26 FRAMEPRBRK09001002"
                                        style="resize: vertical; min-height: 300px; padding-bottom: 30px;"
                                    >{{ old('barcode_list') }}</textarea>
                                    <div class="char-counter" id="charCounter">0 karakter</div>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Masukkan satu barcode per baris. Script akan mengupdate field berikut menjadi null:
                                    <code>parent_item</code>, <code>scan_produksi_id</code>, <code>scanned_produksi_at</code>, 
                                    <code>status_submit_produksi</code>, <code>lot_prod</code>, <code>submit_produksi_at</code>
                                </div>
                                @error('barcode_list')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Server Info -->
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-server me-2"></i>
                                    Informasi Server
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>SSH Server:</strong> 38.47.67.48:22<br>
                                        <strong>User:</strong> ubuntu
                                    </div>
                                    <div class="col-md-6">
                                        <strong>MongoDB:</strong> 127.0.0.1:28118<br>
                                        <strong>Database:</strong> sdi_testing<br>
                                        <strong>Collection:</strong> m_kanban_generate_details
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary me-md-2" onclick="clearForm()">
                                    <i class="fas fa-eraser me-2"></i>
                                    Clear
                                </button>
                                <button type="submit" class="btn btn-execute" id="executeBtn">
                                    <i class="fas fa-play me-2"></i>
                                    <span id="btnText">Eksekusi Script</span>
                                    <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Warning Card -->
                <div class="card mt-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Peringatan Penting
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Script ini akan <strong>menghapus data scan produksi</strong> secara permanen</li>
                            <li>Pastikan barcode yang dimasukkan sudah benar</li>
                            <li>Proses ini <strong>tidak dapat dibatalkan</strong></li>
                            <li>Backup data sebelum menjalankan script jika diperlukan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter
        const textarea = document.getElementById('barcode_list');
        const charCounter = document.getElementById('charCounter');
        
        function updateCharCounter() {
            const text = textarea.value;
            const lines = text.split('\n').filter(line => line.trim() !== '').length;
            charCounter.textContent = `${text.length} karakter, ${lines} baris`;
        }
        
        textarea.addEventListener('input', updateCharCounter);
        updateCharCounter(); // Initial count
        
        // Form submission with loading state
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            const executeBtn = document.getElementById('executeBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            // Show loading state
            executeBtn.disabled = true;
            btnText.textContent = 'Mengeksekusi...';
            btnSpinner.classList.remove('d-none');
            
            // Confirm before submit
            if (!confirm('Apakah Anda yakin ingin mengeksekusi script delete scan produksi? Proses ini tidak dapat dibatalkan.')) {
                e.preventDefault();
                // Reset button state
                executeBtn.disabled = false;
                btnText.textContent = 'Eksekusi Script';
                btnSpinner.classList.add('d-none');
            }
        });
        
        // Clear form function
        function clearForm() {
            if (confirm('Apakah Anda yakin ingin menghapus semua input?')) {
                document.getElementById('barcode_list').value = '';
                updateCharCounter();
            }
        }
        
        // Auto-hide alerts after 10 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success') || alert.classList.contains('alert-danger')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 10000);
    </script>
</body>
</html>