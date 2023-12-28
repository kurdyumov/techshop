<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MasterRequest;
use App\Services\SqlService;

class MasterController extends Controller
{
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function defineMasterToClaim(MasterRequest $request, $claimid) {
        $masterid = $request->input('master');
        $this->sqlService->defineMasterToClaim($claimid, $masterid);
        return redirect()->back();
    }

    public function supplyClaim(MasterRequest $request, $claimid, $option) {
        if (!is_null($claimid) && !is_null($option))
            $this->sqlService->supplyClaim($claimid, $option, $request->input('data'), $request->input('amount'));
        return redirect()->back();
    }

    public function expungeFromClaim(MasterRequest $request, $claimid, $option, $data) {
        // dd([$claimid, $option, $data]);
        $this->sqlService->expungeFromClaim($claimid, $option, $data);
        return redirect()->back();
    }
}
