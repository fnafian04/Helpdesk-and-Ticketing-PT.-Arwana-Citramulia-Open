@extends('layouts.technician')
@section('title', 'Dashboard Teknisi')

@section('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/dashboard-technician.css'])
@endsection

@section('content')
    <div class="header-welcome">
        <div class="user-info">
            <h2>Semangat Pagi, Teknisi! 🛠️</h2>
            <p>Prioritas: Menjaga Operasional Mesin & IT.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card card-orange">
            <div class="stat-info">
                <p>Tugas Pending</p>
                <h3>2</h3>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
        </div>
        <div class="stat-card card-green">
            <div class="stat-info">
                <p>Selesai Hari Ini</p>
                <h3>3</h3>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-check-circle"></i></div>
        </div>
        <div class="stat-card card-blue">
            <div class="stat-info">
                <p>Total Ditangani</p>
                <h3>45</h3>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-star"></i></div>
        </div>
    </div>

    <h3 class="section-title">Daftar Tugas Anda</h3>

    <div class="task-card cat-mech">
        <div class="task-content">
            <h4>Ganti Bearing Motor Conveyor Line 2 <span class="badge-cat badge-mech">Mechanical</span></h4>
            <div class="task-meta">
                <span><i class="fa-solid fa-user"></i> Spv. Produksi</span>
                <span><i class="fa-solid fa-triangle-exclamation"></i> Mesin Bunyi Kasar</span>
                <span><i class="fa-regular fa-clock"></i> 30 Menit lalu</span>
            </div>
        </div>
        <button class="btn-update" onclick="openUpdate('#TKT-002', 'Ganti Bearing Motor')">
            Update Status <i class="fa-solid fa-arrow-right"></i>
        </button>
    </div>

    <div class="task-card cat-it">
        <div class="task-content">
            <h4>Install Ulang PC Admin Gudang <span class="badge-cat badge-it">IT Support</span></h4>
            <div class="task-meta">
                <span><i class="fa-solid fa-user"></i> Staff Gudang</span>
                <span><i class="fa-solid fa-laptop-code"></i> Windows Error</span>
                <span><i class="fa-regular fa-clock"></i> 2 Jam lalu</span>
            </div>
        </div>
        <button class="btn-update" onclick="openUpdate('#TKT-005', 'Install Ulang PC')">
            Update Status <i class="fa-solid fa-arrow-right"></i>
        </button>
    </div>

    <div id="modalUpdate" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Update Pengerjaan</h3>
                <button class="btn-close" onclick="closeModal('modalUpdate')">&times;</button>
            </div>

            <form id="updateForm">
                <div
                    style="background: #e8f5e9; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c8e6c9;">
                    <strong style="color: #2e7d32; font-size: 13px;">Tiket: <span id="uSubject">...</span></strong>
                </div>

                <div class="form-group">
                    <label class="form-label">Status Pengerjaan</label>
                    <select class="form-select" id="uStatus">
                        <option value="On Progress">Sedang Dikerjakan (On Progress)</option>
                        <option value="Waiting Sparepart">Menunggu Sparepart</option>
                        <option value="Pending Vendor">Pending Vendor</option>
                        <option value="Resolved">Selesai (Resolved)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tindakan Perbaikan / Catatan Teknisi</label>
                    <textarea class="form-textarea" placeholder="Contoh: Sudah dilakukan penggantian bearing, mesin normal kembali."
                        required></textarea>
                </div>

                <div style="text-align: right; margin-top: 10px;">
                    <button type="button" onclick="closeModal('modalUpdate')"
                        style="background:white; border:1px solid #ddd; padding:10px 20px; border-radius:8px; cursor:pointer; margin-right: 10px;">Batal</button>
                    <button type="submit"
                        style="background:#2e7d32; color:white; border:none; padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:600;">Simpan
                        Laporan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // 1. Logic Modal Update
        function openUpdate(id, subject) {
            document.getElementById('uSubject').innerText = id + " - " + subject;
            document.getElementById('modalUpdate').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // 2. Simulasi Simpan Data
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            closeModal('modalUpdate');

            let timerInterval;
            Swal.fire({
                title: 'Menyimpan...',
                html: 'Mohon tunggu sebentar.',
                timer: 1000,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            }).then((result) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Status tiket berhasil diperbarui.',
                    confirmButtonColor: '#2e7d32',
                });
            });
        });

        // Close on click outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
            }
        }
    </script>
@endsection
