<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;

class DashboardController extends Controller
{
    public function bpp(GoogleSheetsService $sheets)
    {
        $sheet = $sheets->getSheet("BPP");

        $first = collect($sheet)->firstWhere(0,"BPP PEMBANGKIT");
        $second = collect($sheet)
                    ->skipWhile(fn($r)=>$r !== $first)
                    ->skip(1)
                    ->firstWhere(0,"BPP PEMBANGKIT");

        return response()->json([
            "konsolidasi" => $sheets->extractMonths($first),
            "internal" => $sheets->extractMonths($second)
        ]);
    }
}