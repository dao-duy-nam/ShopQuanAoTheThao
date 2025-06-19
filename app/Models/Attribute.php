<?php
namespace App\Models;

use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory;

    protected $table = 'thuoc_tinhs';

    protected $fillable = ['ten'];

    public function values()
    {
        return $this->hasMany(AttributeValue::class, 'thuoc_tinh_id');
    }
}
