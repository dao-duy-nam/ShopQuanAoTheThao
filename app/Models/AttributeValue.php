<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeValue extends Model
{
    use HasFactory;

    protected $table = 'gia_tri_thuoc_tinhs';

    protected $fillable = [
        'gia_tri',
        'thuoc_tinh_id',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'thuoc_tinh_id');
    }

    public function variants()
    {
        return $this->belongsToMany(
            Variant::class,
            'bien_the_thuoc_tinhs',
            'gia_tri_thuoc_tinh_id',
            'bien_the_id'
        )->withTimestamps();
    }
}
