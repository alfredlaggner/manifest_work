<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model

{

    protected $table = "salesorders";

    public function order_lines()
    {
        return $this->hasMany('App\SaleInvoice','order_id','salesorder_id');
    }
}
