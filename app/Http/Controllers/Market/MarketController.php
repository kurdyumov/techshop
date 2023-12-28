<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SqlService;

class MarketController extends Controller
{
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function filter(Request $request) {
        $filters = [
            'entry'=>$request->input('entry'),
            'category'=>$request->input('category'),
            'search'=>$request->input('search'),
            'cancel'=>$request->input('cancel')
        ];
        // dd($filters);
        $market = $this->sqlService->retrievePositions($filters['entry'], $filters['category'])->get();
        return view('market.index', ['positions'=>$market]);
    }
}
