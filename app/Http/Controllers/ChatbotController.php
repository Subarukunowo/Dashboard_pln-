<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $question = $request->input('question');

        // 1. Suntikkan Environment Variables
        putenv("GEMINI_API_KEY=" . env('GEMINI_API_KEY'));
        putenv("GAS_DATA_URL=" . env('GAS_DATA_URL'));

        // 2. Siapkan Path (Pastikan .env sudah benar)
        $pythonPath = env('PYTHON_BIN_PATH');
        $scriptPath = base_path('python/chatbot_logic.py');

        // 3. Susun Command (Gunakan tanda kutip untuk path Windows yang ada spasinya)
        $command = "\"$pythonPath\" \"$scriptPath\" " . escapeshellarg($question);

        // 4. Eksekusi
        $output = shell_exec($command . " 2>&1");

        // 5. Kembalikan Response JSON
        return response()->json([
            'status' => 'success',
            'data' => $output ? trim($output) : "Maaf, tidak ada respon dari sistem."
        ]);
    }
}