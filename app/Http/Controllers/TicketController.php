<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    // 1. Fungsi untuk MENAMPILKAN Form (Ini yang tadi hilang)
    public function create()
    {
        // Pastikan folder view kamu namanya 'tickets' (jamak/plural)
        return view('tickets.create'); 
    }

    // 2. Fungsi untuk MENYIMPAN Data
    public function store(Request $request)
    {
        // 1. Sesuaikan Validasi (Ganti 'title' jadi 'subject')
        $request->validate([
            'subject' => 'required|max:255', // <--- Ubah ini
            'description' => 'required',
        ]);

        // 2. Simpan Data
        Ticket::create([
            'ticket_number' => 'TKT-' . time(), 
            'requester_id' => Auth::id(), 
            
            'subject' => $request->subject, // <--- Ambil dari input 'subject'
            'category_id' => $request->category_id ?? 1, // <--- Ambil input kategori
            'description' => $request->description,
            
            // Default Values
            'status_id' => 1,
            'channel' => 'Web',
        ]);

        return redirect()->route('dashboard')->with('success', 'Tiket berhasil dibuat!');
    }
}