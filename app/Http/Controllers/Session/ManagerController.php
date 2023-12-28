<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SqlService;
use App\Http\Requests\ManagerRequest;

class ManagerController extends Controller
{
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function storagePage(ManagerRequest $request) {
        $data = [
            'entry'=>$request->input('title'),
            'pos'=>$request->input('position')
        ];
        $positions = $this->sqlService->retrievePositions($data['entry'], $data['pos']);

        $sg_invoices = $this->sqlService->retrieveSupplyInvoices();
        // dd($storagegoods_inv);
        return view('manager.storage', ['positions'=>$positions->get(), 'sg_invoices'=>$sg_invoices]);
    }

    public function submitSGInvoice(ManagerRequest $request, $suid) {
        // dd(['suid'=>$suid]);
        $submit = $this->sqlService->submitSGInvoice($suid);
        return redirect()->back();
    }
}
