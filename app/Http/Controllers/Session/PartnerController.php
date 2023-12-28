<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PartnerRequest;
use App\Services\SqlService;

class PartnerController extends Controller
{
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function index(PartnerRequest $request) {
        $invoices = $this->sqlService->retrieveSupplyInvoices();
        // dd($invoices);
        return view('partner.supply', ['invoices'=>$invoices]);
    }

    public function newInvoicePage(PartnerRequest $request) {
        $positions = $this->sqlService->retrievePositions(null, null);
        return view('partner.initinvoice', ['positions'=>$positions]);
    }

    public function initInvoice(PartnerRequest $request) {
        if (sizeof($request->input('input')) != sizeof($request->input('posid')))
            return;
        
        $data = [];
        for ($i = 0; $i < sizeof($request->input('input')); $i++)
            $data[$i] = [
                'sku'=>$request->input('input')[$i],
                'posid'=>$request->input('posid')[$i]
            ];
        $b = $this->sqlService->initInvoice($data);
        // dd($b);
        return redirect()->back();
    }
}
