<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

use App\Models\Product as ProductMain;

class Product extends ProductMain
{
    /**
    * Get the value of the model's route key.
    *
    * @return mixed
    */
    public function getRouteKeyName()
    {
        return 'id';
    }

    
}
