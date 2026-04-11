<?php

namespace App\Http\Controllers;

use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $devices = UserDevice::where('user_id', Auth::id())
            ->orderBy('last_seen_at', 'desc')
            ->get();

        $currentToken = hash('sha256',
            $request->userAgent() .
            $request->header('Accept-Language') .
            $request->header('Accept-Encoding')
        );

        return view('devices.index', compact('devices', 'currentToken'));
    }

    public function destroy($id)
    {
        $device = UserDevice::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $device->delete();

        return back()->with('success', 'Perangkat berhasil dihapus.');
    }
}   