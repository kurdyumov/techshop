<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;

class IndexController extends Controller
{
    public function getConnection() {
        dump(DB::select("SELECT USER, SYS_CONTEXT('USERENV', 'DB_NAME') AS DATABASE_NAME FROM DUAL"));
    }

    public function getData() {
        $tables = DB::getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            dump(array($table, DB::table($table)->get()));
        }
    }
}
