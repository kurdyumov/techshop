<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SqlService;
use App\Http\Requests\StMasterRequest;

class StMasterController extends Controller
{
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function defineMasterToClaim(StMasterRequest $request, $claimid) {
        $masterid = $request->input('master');
        $this->sqlService->defineMasterToClaim($claimid, $masterid);
        return redirect()->back();
    }

    public function setClaimStatus(StMasterRequest $request, $claimid) {
        $status = $request->input('status');
        // dd([$claimid, $status]);
        $this->sqlService->setClaimStatus($claimid, $status);
        return redirect()->back();
    }
}
