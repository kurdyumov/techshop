<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Services\SqlService;
use App\Events\ReceiveOrder;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function index(UserRequest $request) {
        $order = $this->sqlService->getCurrentOrderIfExists();
        // dd($order->exists());
        if ($order->exists())
            event(new ReceiveOrder());
        // dd(Auth::user()->getOrder());

        $userid = Auth::user()->getUser()->contactid;
        $basket = $this->sqlService->retrieveCurrentBasket($userid);
        $total = 0;
        if (!is_null($basket))
        foreach ($basket->get() as $item)
            $total += $item->price;
        return view('session.profile', ['order'=>$order->first(), 'basket'=>$basket, 'total'=>$total, 'userType'=>Auth::user()->getTypes()]);
    }

    public function submitOrder(UserRequest $request) {
        $close = $this->sqlService->submitOrder();
        return view('outside.payment', ['closed'=>$close]);
    }
}
