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

    <div id="dashboardContent">
        <div class="loading">
            <p>Loading dashboard data...</p>
        </div>
    </div>


@endsection

@section('scripts')
    <script>
    
    // Fetch dashboard data from API (SINGLE FETCH ONLY)
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
                    <div class="stat-card card-red">
                        <div class="stat-info"><p>Belum Di-Assign</p><h3>${data.summary.unassigned}</h3></div>
                        <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    </div>
                    <div class="stat-card card-blue">
                        <div class="stat-info"><p>Tiket Minggu Ini</p><h3>${data.summary.this_week}</h3></div>
                        <div class="stat-icon"><i class="fa-solid fa-globe"></i></div>
                    </div>
                    <div class="stat-card card-purple">
                        <div class="stat-info"><p>Teknisi Ready</p><h3>${data.summary.technicians}</h3></div>
                        <div class="stat-icon"><i class="fa-solid fa-headset"></i></div>
                    </div>
                    <div class="stat-card card-orange">
                        <div class="stat-info"><p>Assign Hari Ini</p><h3>${data.summary.assigned_today}</h3></div>
                        <div class="stat-icon"><i class="fa-solid fa-list-ul"></i></div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="section-title">🎯 Tiket Baru (Butuh Penanganan)</div>
                    <table class="urgent-table">
                        <thead>
                            <tr>
                                <th width="15%">ID TIKET</th>
                                <th width="30%">SUBJEK MASALAH</th>
                                <th width="20%">KATEGORI</th>
                                <th width="20%">WAKTU DIBUAT</th>
                                <th width="15%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (data.unassigned_tickets && data.unassigned_tickets.length > 0) {
                data.unassigned_tickets.forEach(ticket => {
                    const deptClass = getCategoryClass(ticket.category);
                    html += `
                        <tr>
                            <td><b>${ticket.ticket_number}</b></td>
                            <td><div style="font-weight:600;">${ticket.subject}</div></td>
                            <td><span class="badge-dept ${deptClass}">${ticket.category}</span></td>
                            <td>${ticket.created_at}</td>
                            <td>
                                <button class="btn-assign" onclick="window.location.href = '{{ url('/helpdesk/tickets') }}/' + ${ticket.id}">
                                    Lihat Detail <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html += `
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            <div class="empty-state">
                                <i class="fa-solid fa-check-circle"></i>
                                <p>Semua tiket sudah di-assign!</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            html += `
                        </tbody>
                    </table>
                </div>
            `;

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

    function getCategoryClass(category) {
        if (category.toLowerCase().includes('hardware')) return 'dept-hardware';
        if (category.toLowerCase().includes('account')) return 'dept-account';
        return 'dept-other';
    }

    // Load dashboard on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboard();
    });
</script>
@endsection
