<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SqlService;
use App\Http\Requests\{UserRequest, ClientRequest};
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function servicePage(UserRequest $request) {
        $types = Auth::user()->getTypes();
        $user = Auth::user()->getUser();
        $roles = Auth::user()->getRoles();

        $userid = ($types->is_client)?Auth::user()->getUser()->contactid:null;
        $masterid = ($types->is_employer && !array_key_exists(3, $roles))?$this->sqlService->retrieveEmpIdFromUser($user->contactid):null;
        $claims = $this->sqlService->retrieveUserClaims($userid, $masterid);
        // dd($claims->get());
        return view('client.claims', ['claims'=>$claims]);
    }

    public function newInvoicePage(UserRequest $request) {
        $userid = Auth::user()->getUser()->contactid;
        $orders = $this->sqlService->retrieveUserOrders($userid);
        $claimTypes = $this->sqlService->retrieveClaimTypes();
        if ($request->ajax()) {
            $goods = $this->sqlService->retrieveCurrentBasket((int)$request->input('orderid'))->get();
            // dd($goods);
            return response()->json(['goods'=>$goods]);
        }
        return view('client.newclaim', ['orders'=>$orders, 'types'=>$claimTypes]);
    }

    public function claimDetails(UserRequest $request, $claim) {
        $claim = $this->sqlService->retrieveClaimDetails($claim);
        $user = Auth::user()->getUser();
        $types = Auth::user()->getTypes();
        $roles = Auth::user()->getRoles();

        if (
            $types->is_client && 
            $this->sqlService->retrieveClientIdFromUser($user->contactid) != 
            $claim['claim']->client_clientid
        ) 
            return redirect()->back();
        if ($types->is_employer)
            $masters = $this->sqlService->retrieveMasters(true);

        return view('client.claimdetails', ['claim'=>$claim, 'masters'=>$masters??null]);
    }

    public function initClaim(UserRequest $request) {
        $claimType = $request->input('type');
        $sku = $request->input('sku');
        $description = $request->input('description');
        $claimid = $this->sqlService->initClaim($claimType, $sku, $description);
        return redirect()->route('page.claim_details', [$claimid]);
    }

    public function payClaim(ClientRequest $request, $claimid) {
        $this->sqlService->submitClaimPayment($claimid);
        return view('outside.claimPayment');
    }
}
