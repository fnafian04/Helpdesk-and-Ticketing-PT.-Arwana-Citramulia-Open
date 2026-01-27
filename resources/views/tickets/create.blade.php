<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Tiket Baru - Helpdesk Arwana</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/ticket-style.css'])
</head>

<body>

    <div class="top-bar">
        <div class="logo-area">
            <i class="fa-solid fa-headset"></i> Helpdesk
        </div>
        <a href="{{ route('dashboard') }}" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Buat Tiket Kendala</h2>
                <p>Isi form di bawah ini untuk melaporkan masalah.</p>
            </div>

            <div class="card-body">
                <form action="{{ route('tickets.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Subjek / Judul Masalah</label>
                        <input type="text" name="subject" class="form-control"
                            placeholder="Contoh: Internet di Ruang Meeting Mati" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori Masalah</label>
                        <select name="category_id" class="form-select">
                            <option value="1">Hardware (Perangkat Keras)</option>
                            <option value="2">Software (Aplikasi/Windows)</option>
                            <option value="3">Network (Jaringan/Internet)</option>
                            <option value="4">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Lengkap</label>
                        <textarea name="description" class="form-textarea" placeholder="Jelaskan detail masalahnya..." required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-paper-plane"></i> KIRIM TIKET
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>
