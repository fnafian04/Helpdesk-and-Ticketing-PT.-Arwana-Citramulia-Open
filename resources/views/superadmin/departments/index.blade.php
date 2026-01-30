@extends('layouts.superadmin')
@section('title', 'Manajemen Departemen')

@section('css')
<style>
    /* Style Tambahan untuk Tabel & Modal */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-title { font-size: 24px; font-weight: 700; color: #333; margin: 0; }
    
    .btn-add { 
        background: #1565c0; color: white; padding: 12px 25px; 
        border-radius: 10px; font-weight: 600; border: none; cursor: pointer; 
        display: flex; align-items: center; gap: 8px; transition: 0.3s; 
    }
    .btn-add:hover { background: #0d47a1; }
    
    .table-container { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); }
    .dept-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .dept-table th { text-align: left; color: #888; padding: 15px; border-bottom: 2px solid #f0f0f0; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .dept-table td { padding: 15px; border-bottom: 1px solid #f9f9f9; font-size: 14px; color: #333; vertical-align: middle; }
    
    /* Tombol Aksi */
    .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; margin-right: 5px; }
    .btn-edit { background: #fff3e0; color: #f57c00; } 
    .btn-del { background: #ffebee; color: #d62828; } 

    /* Modal */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 999; backdrop-filter: blur(2px); }
    .modal-box { background: white; width: 450px; padding: 30px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 5px; }
    .form-input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; }
    
    /* Loading Spinner pada Tabel */
    .loading-row { text-align: center; color: #666; font-style: italic; padding: 20px; }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h1 class="page-title">Departemen</h1>
        <button class="btn-add" onclick="openModal()"><i class="fa-solid fa-plus"></i> Tambah Baru</button>
    </div>

    <div class="table-container">
        <table class="dept-table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Nama Departemen</th>
                    <th>Kode (Auto)</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <tr><td colspan="4" class="loading-row">Sedang memuat data...</td></tr>
            </tbody>
        </table>
    </div>

    <div id="deptModal" class="modal-overlay">
        <div class="modal-box">
            <h3 style="margin-bottom: 20px;" id="modalTitle">Tambah Departemen</h3>
            <form onsubmit="handleSave(event)">
                <div class="form-group">
                    <label class="form-label">Nama Departemen</label>
                    <input type="text" id="deptName" class="form-input" placeholder="Contoh: IT Support" required>
                </div>
                
                <div style="text-align: right; margin-top: 25px;">
                    <button type="button" onclick="closeModal()" style="background:white; border:1px solid #ddd; padding:10px 20px; border-radius:8px; cursor:pointer; margin-right: 10px;">Batal</button>
                    <button type="submit" style="background:#1565c0; color:white; border:none; padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:600;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // URL Relative (Otomatis ikut localhost:8000)
    const API_URL = '/api/departments';

    // Variabel state untuk Edit Mode
    let isEditMode = false;
    let currentId = null;

    // 1. FUNGSI MENGAMBIL DATA (READ)
    async function loadDepartments() {
        const tbody = document.getElementById('table-body');
        
        try {
            // Fetch ke API
            const response = await fetch(API_URL, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Gagal memuat data');

            const result = await response.json();
            // Handle jika response dibungkus {data: []} atau langsung []
            const departments = result.data || result; 

            // Render ke HTML
            let html = '';
            if (departments.length === 0) {
                html = '<tr><td colspan="4" class="loading-row">Belum ada data departemen.</td></tr>';
            } else {
                departments.forEach(dept => {
                    // Visualisasi Kode (Karena di DB tidak ada kolom kode)
                    const displayCode = `DEPT-${dept.id.toString().padStart(3, '0')}`;
                    
                    html += `
                        <tr id="row-${dept.id}">
                            <td>${dept.id}</td>
                            <td><strong>${dept.name}</strong></td>
                            <td>
                                <span style="background:#e3f2fd; color:#1565c0; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">
                                    ${displayCode}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button class="btn-icon btn-edit" onclick="openEdit(${dept.id}, '${dept.name}')">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn-icon btn-del" onclick="handleDelete(${dept.id}, '${dept.name}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            tbody.innerHTML = html;

        } catch (error) {
            console.error(error);
            tbody.innerHTML = `<tr><td colspan="4" class="loading-row" style="color:red">Error: ${error.message}</td></tr>`;
        }
    }

    // Panggil saat halaman diload
    document.addEventListener('DOMContentLoaded', loadDepartments);


    // 2. FUNGSI MODAL (Buka/Tutup)
    function openModal() {
        isEditMode = false;
        currentId = null;
        document.getElementById('modalTitle').innerText = "Tambah Departemen";
        document.getElementById('deptName').value = "";
        document.getElementById('deptModal').style.display = 'flex';
    }

    function openEdit(id, name) {
        isEditMode = true;
        currentId = id;
        document.getElementById('modalTitle').innerText = "Edit Departemen";
        document.getElementById('deptName').value = name;
        document.getElementById('deptModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('deptModal').style.display = 'none';
    }

    // 3. FUNGSI SIMPAN (CREATE & UPDATE)
    async function handleSave(event) {
        event.preventDefault(); // Mencegah reload form

        const name = document.getElementById('deptName').value;
        
        // Tentukan URL & Method (POST untuk Baru, PUT untuk Edit)
        const url = isEditMode ? `${API_URL}/${currentId}` : API_URL;
        const method = isEditMode ? 'PUT' : 'POST';

        try {
            // Ambil CSRF Token dari meta tag layout
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken // WAJIB untuk Session Auth
                },
                body: JSON.stringify({ name: name })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Gagal menyimpan data');
            }

            // Sukses
            closeModal();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: isEditMode ? 'Data diperbarui!' : 'Data ditambahkan!',
                timer: 1500,
                showConfirmButton: false
            });
            
            loadDepartments(); // Refresh tabel

        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
        }
    }

    // 4. FUNGSI HAPUS (DELETE)
    function handleDelete(id, name) {
        Swal.fire({
            title: `Hapus ${name}?`,
            text: "Data tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d62828',
            confirmButtonText: 'Ya, Hapus'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    const response = await fetch(`${API_URL}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    if (!response.ok) throw new Error('Gagal menghapus');

                    Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                    loadDepartments(); // Refresh tabel

                } catch (error) {
                    Swal.fire('Error', 'Gagal menghapus data.', 'error');
                }
            }
        })
    }
</script>
@endsection