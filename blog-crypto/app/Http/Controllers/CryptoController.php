<?php

namespace App\Http\Controllers;

use App\Models\CryptoCoin;
use Illuminate\Http\Request;

class CryptoController extends Controller
{
    public function index()
    {
        // Lấy coin + giá mới nhất
        $coins = CryptoCoin::with('latestPrice')
            ->orderBy('rank')
            ->get();

        return view('crypto.index', compact('coins'));
    }
}
