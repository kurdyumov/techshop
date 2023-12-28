<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SqlService;
use App\Http\Requests\UserRequest;
use App\Events\ReceiveOrder;

class PositionController extends Controller
{
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function index($posid) {
        $pos = $this->sqlService->retrievePosition($posid);
        $instock = $this->sqlService->retrieveInStock($posid);
        return view('market.position', ['position'=>$pos['position']->first(), 'metadata'=>$pos['metadata']->get(), 'instock'=>$instock]);
    }

    public function toBasket(UserRequest $request, $id) {
        $data = [
            'id'=>$id,
            'amount'=>$request->input('amount')
        ];
        event(new ReceiveOrder());
        $this->sqlService->addToBasket($data['id'], $data['amount']);
        return redirect()->back();
    }

    public function rmFromBasket(UserRequest $request, $sku) {
        $this->sqlService->rmFromBasket($sku);
        return redirect()->back();
    }
}
