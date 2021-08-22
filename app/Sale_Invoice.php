<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale_Invoice extends Model
{
    public $fillable = ['ext_id','ext_id_contact','license','name','contact','street','street2','city','zip'];

    public function customer()
    {
        return $this->belongsTo('App\Customer','ext_id_shipping','ext_id');
    }
    public function unit()
    {
        return $this->belongsTo('App\Unit','ext_id_unit','ext_id');
    }

}
