<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopOrder extends Model
{
    const ORDER_STATUS_PENDING = "pending";
    const ORDER_STATUS_PAID = "paid";
    const ORDER_STATUS_CLOSED = "closed";

    const orderStatusMap = [
      self::ORDER_STATUS_PENDING => "待支付",
      self::ORDER_STATUS_PAID => "已支付",
      self::ORDER_STATUS_CLOSED => "已关闭",
    ];

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            // 如果模型的 no 字段为空
            if (!$model->no) {
                // 调用 findAvailableNo 生成订单流水号
                $model->no = static::findAvailableNo();
                // 如果生成失败，则终止创建订单
                if (!$model->no) {
                    return false;
                }
            }
        });
    }

    public function item()
    {
        return $this->hasOne(ShopOrderItem::class,"shop_order_id");
    }

    public function payments()
    {
        return $this->hasMany(ShopOrderPayment::class,"shop_order_no","no");
    }

    public function user(){
        return $this->belongsTo(User::class,"user_id");
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class,'shop_id');
    }

    public static function findAvailableNo()
    {
        // 订单流水号前缀
        $prefix ="SP". date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6 位的数字
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }
        return false;
    }
}
