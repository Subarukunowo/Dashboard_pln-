<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $apiUrl = "https://script.google.com/home/projects/1is62_Jn28pKkUZysp3-RkKFkdlHLsaUsi8TcyvN540Q4la2gXGKGqWG5/edit?hl=id";
        
        // Mengambil data dari Google Sheets via Apps Script
        $response = Http::withoutVerifying()->get($apiUrl);
        
        if ($response->successful()) {
            $data = $response->json();
            
            // Contoh: Mengambil data khusus sheet 'BPP'
            $bppData = $data['BPP'] ?? [];
            
            return view('dashboard', compact('bppData'));
        }

        return view('dashboard')->with('error', 'Gagal mengambil data');
    }
}
