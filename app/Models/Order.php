<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order Model
 *
 * @author AI.Architect
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = ['customer_name', 'customer_phone', 'total_amount', 'status'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}