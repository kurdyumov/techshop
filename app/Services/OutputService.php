<?php

namespace App\Services;
use Illuminate\Support\Facades\Auth;

class OutputService {
	public static function printFIO() {
		$user = Auth::user()->getMetadata()['user'];
		// dd($user);
		return $user->lastname.' '.$user->firstname.' '.$user->patronymic;
	}

	public static function printPosTitle($posid) {
		$pos = (new SqlService())->retrievePosition($posid);

		return $pos['position']->first()->title;
	}

	public static function getSGInvoice($suid) {
		return (new SqlService())->retrieveSupplyInvoice($suid)->first();
	}
}