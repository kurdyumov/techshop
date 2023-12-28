<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\SqlService;

class User extends Model
{
    use HasFactory;
    protected $table = 'users';
    protected $primaryKey = 'contacts_contactid';
    protected $guarded = [];
    protected $metadata;
    protected $roles = [];
    protected $currentOrder;
    protected $userType;
    protected SqlService $sqlService;

    public function __construct($user) {
        if (!is_null($user)) {
            $this->sqlService = new SqlService();
            $this->metadata = $user;
            $roles = $this->sqlService->retrievePosts($this->metadata->contactid)->get();
            for ($i = 0; $i < sizeof($roles); $i++)
                $this->roles[$roles[$i]->post_postid] = $roles[$i]->title;
            // dd($this->metadata->contactid);
            $this->userType = $this->sqlService->defineUserType($this->metadata->contactid);
        }
    }

    public function getMetadata() {
        return [
            'user'=>$this->metadata,
            'roles'=>$this->roles,
            'order'=>$this->order
        ];
    }

    public function getUser() {
        return $this->metadata;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function getOrder() {
        return $this->currentOrder;
    }

    public function getTypes() {
        return $this->userType;
    }

    public function setOrder($order) {
        $this->currentOrder = $order;
    }
}
