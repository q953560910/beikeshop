<?php
/**
 * OrderProduct.php
 *
 * @copyright  2022 beikeshop.com - All Rights Reserved
 * @link       https://beikeshop.com
 * @author     Edward Yang <yangjin@guangda.work>
 * @created    2022-07-04 21:18:54
 * @modified   2022-07-04 21:18:54
 */

namespace Beike\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Base
{
    protected $fillable = [
        'product_id', 'order_number', 'product_sku', 'name', 'image', 'quantity', 'price',
    ];

    protected $appends = ['price_format'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function productSku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'product_sku', 'id');
    }

    public function getPriceFormatAttribute()
    {
        return currency_format($this->price);
    }
}
