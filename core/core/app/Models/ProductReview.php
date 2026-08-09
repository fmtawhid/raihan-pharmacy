<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductReview extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function viewStatusBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_viewed == Status::YES) {
                    return '<span class="badge badge--success">' . trans('Viewed') . '</span>';
                } else {
                    return '<span class="badge badge--warning">' . trans('Not Viewed') . '</span>';
                }
            }
        );
    }
    public function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                Status::REVIEW_APPROVE => '<span class="badge badge--success">Approved</span>',
                Status::REVIEW_ARCHIVE => '<span class="badge badge--secondary">Archived</span>',
                Status::REVIEW_DECLINE => '<span class="badge badge--danger">Declined</span>',
                default => '<span class="badge badge--dark">Unknown</span>',
            }
        );
    }

}
