<?php
namespace App\Models;

use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VariantAttribute extends Model
{
    use HasFactory;

    protected $table = 'bien_the_thuoc_tinhs';

    protected $fillable = [
        'bien_the_id',
        'gia_tri_thuoc_tinh_id',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'bien_the_id');
    }

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class, 'gia_tri_thuoc_tinh_id');
    }
}
