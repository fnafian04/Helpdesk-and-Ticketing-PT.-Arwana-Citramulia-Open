@extends('layouts.helpdesk')

@section('title', 'Dashboard - Helpdesk Arwana')

@section('css')
    @vite(['resources/css/dashboard-helpdesk.css'])
@endsection

@section('content')
    <div class="header-welcome">
        <div class="user-info">
            <h2>Halo, Tim Helpdesk! 👋</h2>
            <p>Pantau aktivitas tiket dan distribusi tugas teknisi hari ini.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card card-red">
            <div class="stat-info">
                <p>Belum Di-Assign</p>
                <h3>5</h3>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
        <div class="stat-card card-blue">
            <div class="stat-info">
                <p>Tiket Bulan Ini</p>
                <h3>145</h3>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-globe"></i></div>
        </div>
        <div class="stat-card card-purple">
            <div class="stat-info">
                <p>Teknisi Ready</p>
                <h3>4</h3>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-headset"></i></div>
        </div>
        <div class="stat-card card-orange">
            <div class="stat-info">
                <p>Open Tickets</p>
                <h3>12</h3>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-list-ul"></i></div>
        </div>
    </div>

    <div class="table-container">
        <div class="section-title">Tiket Baru (Butuh Penanganan)</div>
        <table class="urgent-table">
            <thead>
                <tr>
                    <th width="15%">ID TIKET</th>
                    <th width="30%">SUBJEK MASALAH</th>
                    <th width="20%">DEPARTEMEN</th>
                    <th width="20%">WAKTU DIBUAT</th>
                    <th width="15%">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>#TKT-009</b></td>
                    <td>
                        <div style="font-weight:600;">Server Ruko Down (Urgent)</div>
                    </td>
                    <td><span class="badge-dept dept-finance">Finance</span></td>
                    <td>10 Menit lalu</td>
                    <td>
                        <button class="btn-assign" onclick="openModal('TKT-009', 'Server Ruko Down')">
                            Pilih Teknisi <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td><b>#TKT-008</b></td>
                    <td>
                        <div style="font-weight:600;">Minta Akses SAP</div>
                    </td>
                    <td><span class="badge-dept dept-marketing">Marketing</span></td>
                    <td>30 Menit lalu</td>
                    <td>
                        <button class="btn-assign" onclick="openModal('TKT-008', 'Minta Akses SAP')">
                            Pilih Teknisi <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="modal-overlay" id="assignModal">
        <div class="modal-box">
            <h3 style="margin-bottom:20px; font-size:18px; font-weight:700;">Assign Teknisi</h3>

            <div
                style="margin-bottom:20px; background:#fff5f5; padding:15px; border-radius:10px; border: 1px solid #ffcdd2;">
                <div style="font-size: 12px; color: #d62828; font-weight: 600;" id="modalTicketId">#ID</div>
                <div style="font-weight: 700; font-size: 15px; color: #333; margin-top: 3px;" id="modalTicketSubject">
                    Subject</div>
            </div>

            <div style="margin-bottom: 10px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: #555;">Pilih
                    Teknisi:</label>
                <select class="form-select">
                    <option value="">-- Pilih Personil Tersedia --</option>
                    <option>👨‍🔧 Budi Santoso (Mekanik) - Ready</option>
                    <option>👨‍💻 Andi Pratama (IT) - Ready</option>
                    <option>⚡ Citra (Elektrikal) - Sibuk</option>
                </select>
            </div>

            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal()">Batal</button>
                <button class="btn-save" onclick="simpanAssignment()">Simpan & Kirim</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const modal = document.getElementById('assignModal');

        function openModal(id, subject) {
            document.getElementById('modalTicketId').innerText = '#' + id;
            document.getElementById('modalTicketSubject').innerText = subject;
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function simpanAssignment() {
            closeModal();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Tiket telah ditugaskan.',
                timer: 2000,
                showConfirmButton: false,
                confirmButtonColor: '#d62828'
            });
        }
    </script>
@endsection
