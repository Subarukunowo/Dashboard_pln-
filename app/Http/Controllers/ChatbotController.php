<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $question   = $request->input('question');
        $pythonPath = env('PYTHON_BIN_PATH');
        $scriptPath = base_path('python/chatbot_logic.py');

        // 1. Log info awal untuk debugging
        \Log::info('ChatBot Request', [
            'question'      => $question,
            'python_path'   => $pythonPath,
            'script_path'   => $scriptPath,
            'python_exists' => file_exists($pythonPath),
            'script_exists' => file_exists($scriptPath),
        ]);

        // 2. Validasi file ada
        if (!file_exists($pythonPath)) {
            \Log::error('Python tidak ditemukan', ['path' => $pythonPath]);
            return response()->json([
                'status' => 'error',
                'data'   => 'Python tidak ditemukan: ' . $pythonPath
            ]);
        }

        if (!file_exists($scriptPath)) {
            \Log::error('Script Python tidak ditemukan', ['path' => $scriptPath]);
            return response()->json([
                'status' => 'error',
                'data'   => 'Script tidak ditemukan: ' . $scriptPath
            ]);
        }

        // 3. Jalankan Python dengan Process (lebih aman dari shell_exec)
        $process = new Process([$pythonPath, $scriptPath, $question]);

        // 4. Teruskan env variable ke proses Python
        $process->setEnv([
            'GEMINI_API_KEY' => env('GEMINI_API_KEY'),
            'GAS_DATA_URL'   => env('GAS_DATA_URL'),
        ]);

        $process->setTimeout(120);
        $process->run();

        // 5. Log hasil eksekusi
        \Log::info('ChatBot Response', [
            'exitCode' => $process->getExitCode(),
            'output'   => $process->getOutput(),
            'error'    => $process->getErrorOutput(),
            'success'  => $process->isSuccessful(),
        ]);

        // 6. Ambil output atau error
        $output = $process->isSuccessful()
            ? $process->getOutput()
            : $process->getErrorOutput();

        return response()->json([
            'status' => 'success',
            'data'   => trim($output) ?: 'Tidak ada respon dari sistem.'
        ]);
    }
}