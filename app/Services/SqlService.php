<?php

namespace App\Services;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\{Auth, DB};
use App\Exceptions\Exception;
use App\Models\{User, Contacts, Career, Posts, Order, Position, Category, Filter, Metadata, FD, PH, SG, BH, Partner, Employer, Client, Invoice, Claim, ClaimStatus, CSH, ClaimType, Services, Contributions, SIU, StorageInner, SupplReq};

class SqlService {
	protected $user, $contacts, $career, $posts, $order, $position, $category, $filter, $metadata, $filtdistrib, $pricehistory, $storagegoods, $baskethistory, $partner, $employer, $client, $supplyinvoice, $supplies, $invoice, $claim, $claimStatus, $claimStatusHistory, $claimType, $services, $contributions, $storInnUsage, $storageInner, $supplReq;

	public function __construct() {
		$this->user = new User(null);
		$this->contacts = new Contacts();
		$this->career = new Career();
		$this->posts = new Posts();
		$this->order = new Order();
		$this->position = new Position();
		$this->category = new Category();
		$this->filter = new Filter();
		$this->metadata = new Metadata();
		$this->filtdistrib = new FD();
		$this->pricehistory = new PH();
		$this->storagegoods = new SG();
		$this->baskethistory = new BH();
		$this->partner = new Partner();
		$this->employer = new Employer();
		$this->client = new Client();
		$this->invoice = new Invoice();
		$this->claim = new Claim();
		$this->claimStatus = new ClaimStatus();
		$this->claimStatusHistory = new CSH();
		$this->claimType = new ClaimType();
		$this->services = new Services();
		$this->contributions = new Contributions();
		$this->storInnUsage = new SIU();
		$this->storageInner = new StorageInner();
		$this->supplReq = new SupplReq();
	}

	public function retrieveUserByLogin($login) {
		$u = $this->user;
		$c = $this->contacts;
		$res = DB::table($u->getTable())
			->select([
				$c->getTable().'.*',
				$u->getTable().'.password'
			])
			->join($c->getTable(), $c->getTable().'.'.$c->getKeyName(), '=', $u->getTable().'.'.$u->getKeyName())
			->where($c->getTable().'.email', $login)
			->orWhere($c->getTable().'.phone', $login);
		return $res;
	}

	public function defineUserType($id = null) {
		$p = $this->partner;
		$c = $this->client;
		$e = $this->employer;

		$sql = 'select (select count(*) from '.$c->getTable().' where contacts_contactid=?) as is_'.$c->getTable().', (select count(*) from '.$e->getTable().' where contacts_contactid=?) as is_'.$e->getTable().', (select count(*) from '.$p->getTable().' where contacts_contactid=?) as is_'.$p->getTable().' from dual';
		$data = DB::select($sql, [$id, $id, $id]);
		// dd($data[0]);
		return $data[0];
	}

	public function retrievePosts($userid) {
		$c = $this->career;
		$p = $this->posts;
		$u = $this->user;
		$e = $this->employer;

		// dd($userid);
		$data = DB::table($c->getTable())
			->select([
				$c->getTable().'.*',
				$p->getTable().'.title'
			])
			->join($p->getTable(), $p->getTable().'.'.$p->getKeyName(), '=', $c->getTable().'.post_postid')
			->join($e->getTable(), $e->getTable().'.'.$e->getKeyName(), '=', $c->getTable().'.employer_employerid')
			->join($u->getTable(), $u->getTable().'.'.$u->getKeyName(), '=', $e->getTable().'.contacts_contactid')
			->whereRaw('('.$c->getTable().'.fire is null or '.$c->getTable().'.fire > sysdate)')
			// ->where($c->getTable().'.employer_employerid', $userid);
			->where($u->getTable().'.'.$u->getKeyName(), $userid);
		// dd($data->get());
		return $data;
	}

	public function getCurrentOrderIfExists() {
		if (Auth::guest())
			return;

		$userid = (int)Auth::user()->getUser()->contactid;
		$o = $this->order;
		$c = $this->client;
		return DB::table($o->getTable())
			->join($c->getTable(), $c->getTable().'.'.$c->getKeyName(), '=', $o->getTable().'.'.$c->getTable().'_'.$c->getKeyName())
			->where($c->getTable().'.contacts_contactid', $userid)
			->where($o->getTable().'.submitted', 0)
			->where($o->getTable().'.employer_employerid', null);
	}

	public function retrieveCurrentOrder() {
		// dd(Auth::user()->getOrder());
		if (Auth::guest())
			return;

		$userid = (int)Auth::user()->getUser()->contactid;
		$o = $this->order;
		$order = Order::query()->firstOrCreate(
            [$o->getKeyName() => DB::table($o->getTable())
                    ->select($o->getKeyName())
                    ->where('submitted', 0)
                    ->where('client_clientid', $userid)
                    ->where('employer_employerid', null)
                    ->first()
                    ->orderid
                ??DB::table($o->getTable())->max($o->getKeyName())+1
            ],
            [
                'createdt' => DB::raw('systimestamp'),
                'client_clientid' => $userid,
            ]
        );
        return $order;
	}

	public function retrieveCurrentBasket($orderid) {
		$bh = $this->baskethistory;
		$sg = $this->storagegoods;
		$p = $this->position;
		$ph = $this->pricehistory;
		$i = $this->invoice;

		if (Auth::guest())
			return;

		return DB::table($bh->getTable())
			->select([
				$bh->getTable().'.*',
				$p->getTable().'.*',
				$ph->getTable().'.price'
			])
			->join($sg->getTable(), $sg->getTable().'.'.$sg->getKeyName(), '=', $bh->getTable().'.storagegoods_sku')
			->join($p->getTable(), $p->getTable().'.'.$p->getKeyName(), '=', $sg->getTable().'.position_posid')
			->join($ph->getTable(), $ph->getTable().'.position_posid', '=', $p->getTable().'.'.$p->getKeyName())
			->where($bh->getTable().'.order_orderid', $orderid)
			->where($ph->getTable().'.setupdate', function ($query) use ($ph) {
                $query->select(DB::raw('max(ph1.setupdate) as aggregate'))
                    ->from($ph->getTable().' ph1')
                    ->whereRaw('ph1.position_posid = '.$ph->getTable().'.position_posid');
            });
	}

	public function addToBasket($posid, $amount) {
		$bh = $this->baskethistory;

		if (Auth::guest())
			return;

		$goods = $this->retrieveInStock($posid)->take($amount)->get();
		$sql = 'insert into '.$bh->getTable().'(order_orderid, storagegoods_sku, whenset) values(?,?,sysdate)';
		DB::transaction(function () use ($sql, $goods) {
			foreach ($goods as $item)
				// dd($item);
				DB::insert($sql, [Auth::user()->getOrder()->orderid, $item->sku]);
		});
	}

	public function rmFromBasket($sku) {
		$bh = $this->baskethistory;

		if (Auth::guest())
			return;
		$sql = 'delete from '.$bh->getTable().' where storagegoods_sku=? and order_orderid=?';
		$order = Auth::user()->getOrder()->orderid;
		DB::delete($sql, [$sku, $order]);
	}

	public function retrieveCategories() {
		$c = $this->category;
		$p = $this->position;
		$data = DB::table($c->getTable())
			->select([
				$c->getTable().'.*'
			])
			->distinct()
			->join($p->getTable(), $p->getTable().'.category_categoryid', '=', $c->getTable().'.'.$c->getKeyName());
		// dd($data->get());
		return $data;
	}

	public function retrievePositions($entry = null, $category = null) {
		$p = $this->position;
		$query = DB::table($p->getTable());
		if (!is_null($entry) && is_string($entry))
			$query->where('title', 'like', '%'.$entry.'%');
		if (!is_null($category) && is_numeric($category))
			$query->where('category_categoryid', $category);
		return $query;
	}

	public function retrievePosition($id) {
		$p = $this->position;
        $cat =  $this->category;
        $ph = $this->pricehistory;
        $fd = $this->filtdistrib;
        $f = $this->filter;
        $m = $this->metadata;

        $pos['position'] = DB::table($p->getTable())
        	->join($ph->getTable(), $ph->getTable().'.position_posid', '=', $p->getTable().'.'.$p->getKeyName())
        	->where($p->getTable().'.'.$p->getKeyName(), $id)
        	->where($ph->getTable().'.setupdate', function ($query) use ($p, $ph, $id) {
        		$query->select(DB::raw('max(ph.setupdate)'))
        			->from($ph->getTable().' ph')
        			->whereRaw('ph.position_posid = '.$p->getTable().'.posid');
        	});

        $pos['metadata'] = DB::table($cat->getTable())
            ->select([
                $f->getTable().'.title',
                $m->getTable().'.value'
            ])
            ->join($p->getTable(), $p->getTable().'.category_categoryid', '=', $cat->getTable().'.'.$cat->getKeyName())
            ->join($ph->getTable(), $ph->getTable().'.position_posid', '=', $p->getTable().'.'.$p->getKeyName())
            ->join($fd->getTable(), $fd->getTable().'.category_categoryid', '=', $cat->getTable().'.'.$cat->getKeyName())
            ->join($f->getTable(), $f->getTable().'.'.$f->getKeyName(), '=', $fd->getTable().'.filter_filterid')
            ->join($m->getTable(), $m->getTable().'.position_posid', '=', $p->getTable().'.'.$p->getKeyName())
            ->whereRaw($m->getTable().'.filtdistrib_distribid = '.$fd->getTable().'.'.$fd->getKeyName())
            ->where($p->getTable().'.'.$p->getKeyName(), $id)
            ->where($ph->getTable().'.price', function ($query) use ($ph, $p) {
            	$query->select(DB::raw('max(ph1.price)'))
            		->from($ph->getTable().' ph1')
            		->whereRaw('ph1.position_posid = '.$p->getTable().'.'.$p->getKeyName());
            });
        return $pos;
	}

	public function retrieveInStock($posid) {
		$sgTable = $this->storagegoods->getTable();
        $oTable = $this->order->getTable();
        $bhTable = $this->baskethistory->getTable();
        $sgKey = $this->storagegoods->getKeyName();
        $oKey = $this->order->getKeyName();
        $bhKEy = $this->baskethistory->getKeyName();

        # TODO сделать leftJoin с таблицей SupplyInvoice, чтобы избежать вывод непринятых товаров (см. retrieveBasket())

        $notInBasket = DB::table($sgTable)
        	->where('position_posid', $posid)
        	->whereNotIn($sgKey, function ($query) use ($sgTable, $bhTable, $sgKey) {
        		$query->select('sg.'.$sgKey)
        			->from($sgTable.' sg')
        			->join($bhTable, $bhTable.'.storagegoods_sku', '=', $sgTable.'.'.$sgKey);
        	});
        $notOrdered = DB::table($sgTable)
        	->select($sgTable.'.*')
        	->join($bhTable, $bhTable.'.storagegoods_sku', '=', $sgTable.'.'.$sgKey)
        	->join($oTable, $oTable.'.'.$oKey, '=', $bhTable.'.order_orderid')
        	->where($sgTable.'.position_posid', $posid)
        	->where($oTable.'.submitted', 0)
        	->whereNotIn($sgTable.'.'.$sgKey, function ($query) use ($bhTable, $oTable, $oKey) {
        		$query->select('bh1.storagegoods_sku')
        			->from(DB::raw($bhTable.' bh1, '.$oTable.' o1'))
        			->whereRaw('bh1.order_orderid = o1.'.$oKey)
        			->where('o1.submitted', 1);
        	})
        	->union($notInBasket);
        if (Auth::check())
        	$notOrdered->whereNot($oTable.'.client_clientid', Auth::user()->getUser()->contactid);
        $data = DB::table(DB::raw($notOrdered->toSql()))->mergeBindings($notOrdered);
        // dd($data->take(4)->get());
        return $data;
	}

	public function submitOrder() {
		$sql = 'update orders set submitted=1 where orderid=?';
		$this->rmUnableToOrder();
		return DB::update($sql, [Auth::user()->getOrder()->orderid]);
	}

	public function rmUnableToOrder() {
		$bh = $this->baskethistory;
		$o = $this->order;

		# TODO сделать leftJoin с таблицей SupplyInvoice, чтобы избежать вывод непринятых товаров (см. retrieveBasket())

		DB::table($bh->getTable())
			->join($o->getTable(), $o->getTable().'.'.$o->getKeyName(), '=', $bh->getTable().'.order_orderid')
			->where($o->getTable().'.submitted', 0)
			->whereIn($bh.'.storagegoods_sku', function ($query) use ($bh, $o) {
				$query->select('bh1.storagegoods_sku')
					->from(DB::raw($bh->getTable().' bh1, '.$o->getTable().' o1'))
					->whereRaw('bh1.order_orderid = o1'.$o->getKeyName())
					->where('o1.submitted', 1);
			});
	}

	public function initInvoice(array $data) {

		if (Auth::guest() || !Auth::user()->getTypes()->is_partner)
			return;
		$p = $this->partner;
		$sg = $this->storagegoods;
		$i = $this->invoice;

		$suid = DB::table($i->getTable())
			->select(DB::raw('min('.$i->getKeyName().') +1 as minid'))
			->whereNotExists(function ($query) use ($i) {
				$query->select(DB::raw(1))
					->from($i->getTable().' i')
					->whereRaw('i.'.$i->getKeyName().'='.$i->getTable().'.'.$i->getKeyName().'+1');
			})->first()->minid??0;

		if (is_null($suid))
			return;

		DB::transaction(function() use ($suid, $data, $i, $p, $sg) {
			$partnerid = DB::table($p->getTable())
			->where('contacts_contactid', Auth::user()->getUser()->contactid)
			->first()
			->partnerid;

			DB::table($i->getTable())
				->insert([
					$i->getKeyName() => $suid,
					'partner_partnerid' => $partnerid,
					'employer_employerid' => null,
					'createdt' => DB::raw('sysdate')
				]);

			foreach ($data as $item) {
				DB::table($sg->getTable())
					->insert([
						$sg->getKeyName() => $item['sku'],
						'position_posid' => $item['posid'],
						'invoice_invoiceid' => $suid
					]);
			}
		});

		return $suid;
	}

	public function retrieveSupplyInvoices($suid = null) {
		if (Auth::guest())
			return;

		$p = $this->partner;
		$uid = Auth::user()->getUser()->contactid;
		$c = $this->contacts;
		$sg = $this->storagegoods;
		$i = $this->invoice;

		$invoices = DB::table($i->getTable());
		if (Auth::user()->getTypes()->is_partner)
			$invoices->select([
					$i->getTable().'.*'
				])
				->join($p->getTable(), $p->getTable().'.'.$p->getKeyName(), '=', $i->getTable().'.partner_partnerid')
				->join($c->getTable(), $c->getTable().'.'.$c->getKeyName(), '=', $p->getTable().'.contacts_contactid')
				->where($c->getTable().'.'.$c->getKeyName(), $uid);

		// dd($invoices->get());

		// if (!is_null($suid))
		// 	$invoices->where($si->getKeyName(), $suid);

		$data = [];
		foreach ($invoices->get() as $item) {
			$data[$item->invoiceid] = DB::table($sg->getTable())
				->select([
					$sg->getTable().'.*'
				])
				->join($i->getTable(), $i->getTable().'.'.$i->getKeyName(), '=', $sg->getTable().'.invoice_invoiceid')
				->where($i->getTable().'.'.$i->getKeyName(), $item->invoiceid)
				->get();
		}
		// dd($data);
		return $data;
	}

	public function retrieveSupplyInvoice($suid) {
		$i = $this->invoice;
		return DB::table($i->getTable())
			->where($i->getKeyName(), $suid);
	}

	public function submitSGInvoice($suid) {
		if (Auth::guest() || !Auth::user()->getTypes()->is_employer)
			return;

		$userid = Auth::user()->getUser()->contactid;
		$i = $this->invoice;
		$e = $this->employer;
		$c = $this->contacts;

		$eid = DB::table($e->getTable())
			->select('employerid')
			->join($c->getTable(), $c->getTable().'.'.$c->getKeyName(), '=', $e->getTable().'.contacts_contactid')
			->where($c->getTable().'.'.$c->getKeyName(), $userid)
			->first();

		// dd($eid);

		return DB::table($i->getTable())
			->where($i->getKeyName(), $suid)
			->update([
				'employer_employerid'=>$eid->employerid
			]);
	}

	public function retrieveUserClaims($userid = null, $masterid = null) {
		$c = $this->claim;
		$cs = $this->claimStatus;
		$csh = $this->claimStatusHistory;
		$ct = $this->claimType;
		$e = $this->employer;

		$data = DB::table($c->getTable());
		if (!is_null($userid))
			$data->where('client_clientid', $this->retrieveClientIdFromUser($userid));
		if (!is_null($masterid) && Auth::user()->getTypes()->is_employer)
			$data->where($e->getTable().'_'.$e->getKeyName(), $masterid);
		
		// dd([$data->toSql(), $masterid]);

		foreach ($data->get() as $item)
			$this->retrieveClaimDetails($item->claimid);
		return $data;
	}

	public function retrieveClaimDetails($claimid) {
		$c = $this->claim;
		$cs = $this->claimStatus;
		$csh = $this->claimStatusHistory;
		$ct = $this->claimType;
		$con = $this->contributions;
		$s = $this->services;
		$siu = $this->storInnUsage;
		$si = $this->storageInner;
		$sg = $this->storagegoods;
		$sr = $this->supplReq;
		$p = $this->position;
		$ph = $this->pricehistory;

		$data['claim'] = DB::table($c->getTable())
			->select([
				$c->getTable().'.*',
				$p->getTable().'.title',
				$ct->getTable().'.title as ct_title'
			])
			->join($ct->getTable(), $ct->getTable().'.'.$ct->getKeyName(), '=', $c->getTable().'.'.$ct->getTable().'_'.$ct->getKeyName())
			->join($sg->getTable(), $sg->getTable().'.'.$sg->getKeyName(), '=', $c->getTable().'.'.$sg->getTable().'_'.$sg->getKeyName())
			->join($p->getTable(), $p->getTable().'.'.$p->getKeyName(), '=', $sg->getTable().'.'.$p->getTable().'_'.$p->getKeyName())
			->where($c->getTable().'.'.$c->getKeyName(), $claimid)
			->first();
		$data['status'] = DB::table($csh->getTable())
			->select([
				$cs->getTable().'.*',
				DB::raw($csh->getTable().'.updatedt')
			])
			->join($c->getTable(), $c->getTable().'.'.$c->getKeyName(), '=', $csh->getTable().'.'.$c->getTable().'_'.$c->getKeyName())
			->join($cs->getTable(), $cs->getTable().'.'.$cs->getKeyName(), '=', $csh->getTable().'.'.$cs->getTable().'_'.$cs->getKeyName())
			->where($c->getTable().'.'.$c->getKeyName(), $claimid)
			->get();
		$data['services'] = DB::table($con->getTable())
			->select([
				$s->getTable().'.*',
				$con->getTable().'.amount',
			])
			->join($c->getTable(), $c->getTable().'.'.$c->getKeyName(), '=', $con->getTable().'.'.$c->getTable().'_'.$c->getKeyName())
			->join($s->getTable(), $s->getTable().'.'.$s->getKeyName(), '=', $con->getTable().'.'.$s->getTable().'_'.$s->getKeyName())
			->where($c->getTable().'.'.$c->getKeyName(), $claimid)
			->get();
		$data['resources'] = DB::table($siu->getTable())
			->select([
				$siu->getTable().'.*',
				$si->getTable().'.*',
			])
			->join($c->getTable(), $c->getTable().'.'.$c->getKeyName(), '=', $siu->getTable().'.'.$c->getTable().'_'.$c->getKeyName())
			->join($si->getTable(), $si->getTable().'.'.$si->getKeyName(), '=', $siu->getTable().'.'.$si->getTable().'_'.$si->getKeyName())
			->where($c->getTable().'.'.$c->getKeyName(), $claimid)
			->get();
		$data['components'] = DB::table($sr->getTable())
			->select([
				$sr->getTable().'.amount',
				$p->getTable().'.*',
				$ph->getTable().'.price',
			])
			->join($c->getTable(), $c->getTable().'.'.$c->getKeyName(), '=', $sr->getTable().'.'.$c->getTable().'_'.$c->getKeyName())
			->join($p->getTable(), $p->getTable().'.'.$p->getKeyName(), '=', $sr->getTable().'.'.$p->getTable().'_'.$p->getKeyName())
			->join($ph->getTable(), $ph->getTable().'.'.$p->getTable().'_'.$p->getKeyName(), '=', $p->getTable().'.'.$p->getKeyName())
			->where($c->getTable().'.'.$c->getKeyName(), $claimid)
			->where($ph->getTable().'.price', function ($query) use ($ph, $p) {
            	$query->select(DB::raw('max(ph.price)'))
            		->from($ph->getTable().' ph')
            		->whereRaw('ph.position_posid = '.$p->getTable().'.'.$p->getKeyName());
            })
			->get();
		// dd($data);
		return $data;
	}

	public function retrieveUserOrders($userid) {
		$o = $this->order;
		$c = $this->client;
		// dd($userid);
		$data = DB::table($o->getTable())
			->where('client_clientid', $this->retrieveClientIdFromUser($userid));
		// dd($data->get());
		return $data;
	}

	public function retrieveClaimTypes() {
		$ct = $this->claimType;
		$data = DB::table($ct->getTable());
		// dd($data);
		return $data;
	}

	public function initClaim($claimType, $sku, $description) {
		$c = $this->claim;
		$cl = $this->client;
		$claim = $this->retrieveID($c);
		$user = Auth::user()->getUser()->contactid;

		DB::table($c->getTable())
			->insert([
				$c->getKeyName() => $claim,
				'employer_employerid' =>  null,
				'client_clientid' => $this->retrieveClientIdFromUser($user),
				'claimtype_ctid' => $claimType,
				'storagegoods_sku' => DB::raw('\''.$sku.'\''),
				'description' => DB::raw('\''.$description.'\''),
			]);
		return $claim;
	}

	public function retrieveMasters($includeSt = false) {
		$ca = $this->career;
		$e = $this->employer;
		$con = $this->contacts;
		$p = $this->posts;

		$roles = $includeSt?['2','3']:['2'];

		$data = DB::table($con->getTable())
			->select([
				$e->getTable().'.'.$e->getKeyName(),
				DB::raw($con->getTable().'.lastname||\' \'||'.$con->getTable().'.firstname||\' \'||'.$con->getTable().'.patronymic as lfp'),
			])
			->join($e->getTable(), $e->getTable().'.'.$con->getTable().'_'.$con->getKeyName(), '=', $con->getTable().'.'.$con->getKeyName())
			->join($ca->getTable(), $ca->getTable().'.'.$e->getTable().'_'.$e->getKeyName(), '=', $e->getTable().'.'.$e->getKeyName())
			->whereIn($ca->getTable().'.'.$p->getTable().'_'.$p->getKeyName(), $roles)
			->where(function ($query) use ($ca) {
				$query->where($ca->getTable().'.fire', null)
					->orWhereRaw($ca->getTable().'.fire > sysdate');
			});
		return $data;
	}

	public function defineMasterToClaim(int $claim, int $master) {
		$c = $this->claim;
		$csh = $this->claimStatusHistory;
		$e = $this->employer;
		$cs = $this->claimStatus;

		DB::transaction(function() use ($c, $cs, $csh, $e, $claim, $master) {
			DB::table($c->getTable())
				->where($c->getKeyName(), $claim)
				->update([
					$e->getTable().'_'.$e->getKeyName() => $master,
				]);
			$this->setClaimStatus($claim, 1);
		});
	}

	public function retrieveServices($claimid = null) {
		$s = $this->services;
		$c = $this->contributions;
		$cl = $this->claim;
		$data = DB::table($s->getTable());
		if (!is_null($claimid))
			$data->whereNotExists(function ($query) use ($c, $cl, $s, $claimid) {
					$query->select([
						$c->getTable().'.*'
					])
					->from($c->getTable())
					->whereRaw($c->getTable().'.'.$s->getTable().'_'.$s->getKeyName().' = '.$s->getTable().'.'.$s->getKeyName())
					->where($c->getTable().'.'.$cl->getTable().'_'.$cl->getKeyName(), $claimid);
				})
				->distinct();

		return $data;
	}

	public function retrieveComponents($claimid = null) {
		$p = $this->position;
		$sr = $this->supplReq;
		$cl = $this->claim;
		$data = DB::table($p->getTable());

		if (!is_null($claimid))
			$data->whereNotExists(function ($query) use ($p, $sr, $cl, $claimid) {
					$query->select([
						$p->getTable().'.*'
					])
					->from($sr->getTable())
					->whereRaw($sr->getTable().'.'.$p->getTable().'_'.$p->getKeyName().' = '.$p->getTable().'.'.$p->getKeyName())
					->where($sr->getTable().'.'.$cl->getTable().'_'.$cl->getKeyName(), $claimid);
				})
				->distinct();

		return $data;
	}

	public function retrieveSupplies($claimid = null) {
		$siu = $this->storInnUsage;
		$si = $this->storageInner;
		$cl = $this->claim;
		$data = DB::table($si->getTable());

		if (!is_null($claimid))
			$data->whereNotExists(function ($query) use ($siu, $si, $cl, $claimid) {
					$query->select([
						$siu->getTable().'.*'
					])
					->from($siu->getTable())
					->whereRaw($siu->getTable().'.'.$si->getTable().'_'.$si->getKeyName().' = '.$si->getTable().'.'.$si->getKeyName())
					->where($siu->getTable().'.'.$cl->getTable().'_'.$cl->getKeyName(), $claimid);
				})
				->distinct();

		return $data;
	}

	public function supplyClaim(int $claimid, int $option, $data, int $amount) {
		$c = $this->contributions;
		$s = $this->services;
		$cl = $this->claim;
		$sr = $this->supplReq;
		$p = $this->position;
		$siu = $this->storInnUsage;
		$si = $this->storageInner;

		switch ($option) {
			case 0:
				DB::table($c->getTable())
					->insert([
						$s->getTable().'_'.$s->getKeyName() => $data,
						$cl->getTable().'_'.$cl->getKeyName() => $claimid,
						'price' => DB::table($s->getTable())
							->select('price')
							->where($s->getTable().'.'.$s->getKeyName(), $data)
							->first()
							->price,
						'amount' => $amount
					]);
				break;
			case 1:
				DB::table($sr->getTable())
					->insert([
						$cl->getTable().'_'.$cl->getKeyName() => $claimid,
						$p->getTable().'_'.$p->getKeyName() => $data,
						'amount' => $amount
					]);
				break;
			case 2:
				DB::table($siu->getTable())
					->insert([
						$si->getTable().'_'.$si->getKeyName() => $data,
						$cl->getTable().'_'.$cl->getKeyName() => $claimid,
						'amount' => $amount
					]);
				break;
			default:

				break;
		}
	}

	public function countClaimPrice($claimid) {
		if (is_null($claimid))
			return;

		$cl = $this->claim;
		$c = $this->contributions;
		$s = $this->services;
		$siu = $this->storInnUsage;
		$si = $this->storageInner;
		$sr = $this->supplReq;
		$p = $this->position;
		$ph = $this->pricehistory;

		$data = [];
		$data['c_total'] = DB::table($cl->getTable())
			->select([
				DB::raw('sum('.$c->getTable().'.price*'.$c->getTable().'.amount) c_total')
			])
			->join($c->getTable(), $c->getTable().'.'.$cl->getTable().'_'.$cl->getKeyName(), '=', $cl->getTable().'.'.$cl->getKeyName())
			->where($cl->getTable().'.'.$cl->getKeyName(), $claimid)
			->first()
			->c_total;

		$data['sr_total'] = DB::table($cl->getTable())
			->select([
				DB::raw('sum('.$ph->getTable().'.price*'.$sr->getTable().'.amount) sr_total'),
			])
			->join($sr->getTable(), $sr->getTable().'.'.$cl->getTable().'_'.$cl->getKeyName(), '=', $cl->getTable().'.'.$cl->getKeyName())
			->join($p->getTable(), $p->getTable().'.'.$p->getKeyName(), '=', $sr->getTable().'.'.$p->getTable().'_'.$p->getKeyName())
			->join($ph->getTable(), $ph->getTable().'.'.$p->getTable().'_'.$p->getKeyName(), '=', $p->getTable().'.'.$p->getKeyName())
			->where($cl->getTable().'.'.$cl->getKeyName(), $claimid)
			->where($ph->getTable().'.price', function ($query) use ($ph, $p) {
            	$query->select(DB::raw('max(ph.price)'))
            		->from($ph->getTable().' ph')
            		->whereRaw('ph.position_posid = '.$p->getTable().'.'.$p->getKeyName());
            })
			->first()
			->sr_total;

		$data['siu_total'] = DB::table($cl->getTable())
			->select([
				DB::raw('sum('.$siu->getTable().'.price*'.$siu->getTable().'.amount) as siu_total'),
			])
			->join($siu->getTable(), $siu->getTable().'.'.$cl->getTable().'_'.$cl->getKeyName(), '=', $cl->getTable().'.'.$cl->getKeyName())
			->where($cl->getTable().'.'.$cl->getKeyName(), $claimid)
			->first()
			->siu_total;

		return $data;
	}

	public function expungeFromClaim(int $claimid, int $option, $data) {
		if (is_null($claimid))
			return;

		$cl = $this->claim;
		$c = $this->contributions;
		$s = $this->services;
		$siu = $this->storInnUsage;
		$si = $this->storageInner;
		$sr = $this->supplReq;
		$p = $this->position;
		$ph = $this->pricehistory;
		$cs = $this->claimStatus;
		$csh = $this->claimStatusHistory;

		switch ($option) {
			case 0:
				DB::table($c->getTable())
					->where([
						$cl->getTable().'_'.$cl->getKeyName() => $claimid,
						$s->getTable().'_'.$s->getKeyName() => $data
					])
					->delete();
				break;
			case 1:
				DB::table($sr->getTable())
					->where([
						$cl->getTable().'_'.$cl->getKeyName() => $claimid,
						$p->getTable().'_'.$p->getKeyName() => $data
					])
					->delete();
				break;
			case 2:
				DB::table($siu->getTable())
					->where([
						$cl->getTable().'_'.$cl->getKeyName() => $claimid,
						$si->getTable().'_'.$si->getKeyName() => $data
					])
					->delete();
				break;
			case 3:
				DB::table($csh->getTable())
					->where([
						$cl->getTable().'_'.$cl->getKeyName() => $claimid,
						$cs->getTable().'_'.$cs->getKeyName() => $data
					])
					->delete();
				break;
		}
	}

	protected function retrieveID($table) {
		$data = DB::table($table->getTable())
			->select(DB::raw('min('.$table->getKeyName().') +1 as minid'))
			->whereNotExists(function ($query) use ($table) {
				$query->select(DB::raw(1))
					->from($table->getTable().' t')
					->whereRaw('t.'.$table->getKeyName().'='.$table->getTable().'.'.$table->getKeyName().'+1');
			})->first()->minid??0;
		return $data;
	}

	public function retrieveStatusList(int $claimid = null, $inverse = false) {
		$cs = $this->claimStatus;
		$csh = $this->claimStatusHistory;
		$cl = $this->claim;
		$data = DB::table($cs->getTable())
			->whereNotIn($cs->getKeyName(), [0,1]);
		// if (!is_null($claimid)) {
		// 	$data->join($csh->getTable(), $csh->getTable().'.'.$cs->getTable().'_'.$cs->getKeyName(), '=', $cs->getTable().'.'.$cs->getKeyName())
		// 		->where($csh->getTable().'.'.$cl->getTable().'_'.$cl->getKeyName(), $claimid);
		// }
		return $data;
	}

	public function submitClaimPayment(int $claimid) {
		$cl = $this->claim;
		$csh = $this->claimStatusHistory;
		$cs = $this->claimStatus;
		DB::table($csh->getTable())
			->insert([
				$cl->getTable().'_'.$cl->getKeyName() => $claimid,
				$cs->getTable().'_'.$cs->getKeyName() => 5,
				'updatedt' => DB::raw('sysdate')
			]);
	}

	public function setClaimStatus(int $claimid, int $status) {
		$cl = $this->claim;
		$cs = $this->claimStatus;
		$csh = $this->claimStatusHistory;

		$sql = 'merge into '.$csh->getTable().' using dual on (
			'.$cs->getTable().'_'.$cs->getKeyName().' = ?
			and
			'.$cl->getTable().'_'.$cl->getKeyName().' = ?
		) when not matched then
			insert('.$cs->getTable().'_'.$cs->getKeyName().', '.$cl->getTable().'_'.$cl->getKeyName().', updatedt)
			values(?,?,sysdate)';
		DB::insert($sql, [$status, $claimid, $status, $claimid]);
	}

	public function retrieveClientIdFromUser($userid) {
		$cl = $this->client;
		return DB::table($cl->getTable())
			->select([$cl->getKeyName()])
			->where('contacts_contactid', $userid)
			->first()
			->clientid;
	}

	public function retrieveEmpIdFromUser($userid) {
		$e = $this->employer;
		return DB::table($e->getTable())
			->select([$e->getKeyName()])
			->where('contacts_contactid', $userid)
			->first()
			->employerid;
	}
}

?>