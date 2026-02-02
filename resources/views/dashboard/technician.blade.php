@extends('layouts.technician')
@section('title', 'Dashboard Teknisi')

@section('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* CSS Dashboard Lama (Header & Stats) */
        .header-welcome { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .user-info h2 { font-size: 24px; color: #333; margin-bottom: 5px; }
        .user-info p { color: #888; font-size: 14px; }

        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03); border-bottom: 4px solid transparent; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-info h3 { font-size: 28px; color: #333; font-weight: 700; margin-bottom: 5px; }
        .stat-info p { color: #777; font-size: 13px; font-weight: 500; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .card-orange { border-color: #f57c00; } .card-orange .stat-icon { background: #fff3e0; color: #f57c00; }
        .card-green { border-color: #388e3c; } .card-green .stat-icon { background: #e8f5e9; color: #388e3c; }
        .card-blue { border-color: #1976d2; } .card-blue .stat-icon { background: #e3f2fd; color: #1976d2; }
        .card-red { border-color: #d62828; } .card-red .stat-icon { background: #ffebee; color: #d62828; }

        /* Task List Styles */
        .section-title { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px; }
        .task-card { background: white; padding: 25px; border-radius: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03); border-left: 6px solid #ccc; transition: 0.3s; }
        .task-card:hover { transform: translateX(5px); }
        .cat-mech { border-left-color: #d62828; } .cat-it { border-left-color: #1976d2; }
        .task-content h4 { font-size: 16px; font-weight: 600; color: #333; margin-bottom: 8px; }
        .task-meta { font-size: 13px; color: #777; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .task-meta i { color: #bbb; margin-right: 5px; }
        .badge-cat { padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; margin-left: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-mech { background: #ffebee; color: #d62828; } .badge-it { background: #e3f2fd; color: #1976d2; }

        .badge-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-status.assigned { background: #e3f2fd; color: #1565c0; }
        .badge-status.in-progress { background: #f3e5f5; color: #7b1fa2; }
        .badge-status.open { background: #fff3e0; color: #f57c00; }
        .badge-status.resolved { background: #e8f5e9; color: #2e7d32; }
        .badge-status.closed { background: #fce4ec; color: #c2185b; }

        .btn-update { background: #2e7d32; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; transition: 0.3s; text-decoration: none;}
        .btn-update:hover { background: #1b5e20; }

        /* Modal Styles (Copy dari Tasks) */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(4px); }
        .modal-box { background: white; width: 600px; max-width: 90%; padding: 30px; border-radius: 12px; box-shadow: 0 25px 50px rgba(0,0,0,0.25); animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .modal-title { font-size: 20px; font-weight: 700; color: #333; }
        .btn-close { background: none; border: none; font-size: 24px; color: #999; cursor: pointer; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 8px; }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; margin-bottom: 15px; }
        .form-textarea { height: 100px; resize: vertical; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #2e7d32; box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1); }

        .loading { text-align: center; padding: 20px; color: #666; }
        .error { background: #ffebee; color: #d62828; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .empty-state { text-align: center; padding: 40px 20px; color: #888; }
        .empty-state i { font-size: 48px; margin-bottom: 20px; opacity: 0.5; }
    </style>
@endsection

@section('content')
    <div class="header-welcome">
        <div class="user-info">
            <h2>Semangat Pagi, Teknisi! 🛠️</h2>
            <p>Prioritas: Menjaga Operasional Mesin & IT.</p>
        </div>
    </div>

    <div id="dashboardContent">
        <div class="loading">
            <p>Loading dashboard data...</p>
        </div>
    </div>

    <div id="modalUpdate" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Update Pengerjaan</h3>
                <button class="btn-close" onclick="closeModal('modalUpdate')">&times;</button>
            </div>
            
            <form id="updateForm">
                <div style="background: #e8f5e9; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c8e6c9;">
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
                    <textarea class="form-textarea" placeholder="Contoh: Sudah dilakukan penggantian bearing, mesin normal kembali." required></textarea>
                </div>

                <div style="text-align: right; margin-top: 10px;">
                    <button type="button" onclick="closeModal('modalUpdate')" style="background:white; border:1px solid #ddd; padding:10px 20px; border-radius:8px; cursor:pointer; margin-right: 10px;">Batal</button>
                    <button type="submit" style="background:#2e7d32; color:white; border:none; padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:600;">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Fetch dashboard data from API
    async function loadDashboard() {
        try {
            const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
            
            const response = await fetch('{{ url("/api/dashboard") }}', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            const data = result.data;

            // Build HTML
            let html = `
                <div class="stats-grid">
                    <div class="stat-card card-blue">
                        <div class="stat-info"><p>Total Ditangani</p><h3>${data.summary.total}</h3></div>
                        <div class="stat-icon"><i class="fa-solid fa-star"></i></div>
                    </div>
                    <div class="stat-card card-orange">
                        <div class="stat-info"><p>Tugas Assigned</p><h3>${data.summary.assigned}</h3></div>
                        <div class="stat-icon"><i class="fa-solid fa-tasks"></i></div>
                    </div>
                    <div class="stat-card card-red">
                        <div class="stat-info"><p>Sedang Dikerjakan</p><h3>${data.summary.in_progress}</h3></div>
                        <div class="stat-icon"><i class="fa-solid fa-hourglass-end"></i></div>
                    </div>
                    <div class="stat-card card-green">
                        <div class="stat-info"><p>Selesai Hari Ini</p><h3>${data.summary.solved_today}</h3></div>
                        <div class="stat-icon"><i class="fa-solid fa-check-circle"></i></div>
                    </div>
                </div>

                <h3 class="section-title">Daftar Tugas Anda</h3>
            `;

            if (data.my_tickets && data.my_tickets.length > 0) {
                data.my_tickets.forEach(ticket => {
                    const categoryClass = ticket.category.toLowerCase().includes('hardware') ? 'cat-mech' : 'cat-it';
                    const categoryBadgeClass = ticket.category.toLowerCase().includes('hardware') ? 'badge-mech' : 'badge-it';
                    const statusBadgeClass = getStatusBadgeClass(ticket.status);

                    html += `
                        <div class="task-card ${categoryClass}">
                            <div class="task-content">
                                <h4>${ticket.subject} <span class="badge-cat ${categoryBadgeClass}">${ticket.category}</span></h4>
                                <div class="task-meta">
                                    <span><i class="fa-solid fa-ticket"></i> ${ticket.ticket_number}</span>
                                    <span><i class="fa-solid fa-user"></i> ${ticket.requester}</span>
                                    <span><i class="fa-regular fa-clock"></i> ${ticket.created_at}</span>
                                    <span><span class="badge-status ${statusBadgeClass}">${ticket.status}</span></span>
                                </div>
                            </div>
                            <button class="btn-update" onclick="openUpdate('${ticket.ticket_number}', '${ticket.subject}', ${ticket.id})">
                                Update Status <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    `;
                });
            } else {
                html += `
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Tidak ada tugas yang ditugaskan kepada Anda saat ini</p>
                    </div>
                `;
            }

            document.getElementById('dashboardContent').innerHTML = html;

        } catch (error) {
            console.error('Error:', error);
            document.getElementById('dashboardContent').innerHTML = `
                <div class="error">
                    <strong>Error!</strong> Gagal memuat dashboard data. ${error.message}
                </div>
            `;
        }
    }

    function getStatusBadgeClass(status) {
        const statusMap = {
            'Open': 'open',
            'Assigned': 'assigned',
            'In Progress': 'in-progress',
            'Resolved': 'resolved',
            'Closed': 'closed'
        };
        return statusMap[status] || 'open';
    }

    // Logic Modal Update
    function openUpdate(ticketNumber, subject, ticketId) {
        document.getElementById('uSubject').innerText = ticketNumber + " - " + subject;
        document.getElementById('modalUpdate').dataset.ticketId = ticketId;
        document.getElementById('modalUpdate').style.display = 'flex';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Simulasi Simpan Data
    document.getElementById('updateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        closeModal('modalUpdate');
        
        let timerInterval;
        Swal.fire({
            title: 'Menyimpan...',
            html: 'Mohon tunggu sebentar.',
            timer: 1000,
            timerProgressBar: true,
            didOpen: () => { Swal.showLoading(); },
            willClose: () => { clearInterval(timerInterval); }
        }).then((result) => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Status tiket berhasil diperbarui.',
                confirmButtonColor: '#2e7d32',
            }).then(() => {
                // Reload dashboard
                loadDashboard();
            });
        });
    });

    // Close on click outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }

    // Load dashboard on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboard();
    });
</script>
@endsection