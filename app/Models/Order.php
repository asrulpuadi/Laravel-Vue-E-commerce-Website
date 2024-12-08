<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['total_price','status','created_by','updated_by'];

    public function isPaid()
    {
        return $this->status === OrderStatus::Paid->value;
    }

    public function payment():HasOne
    {
        return $this->hasOne(Payment::class,'order_id','id');
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function items():HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
