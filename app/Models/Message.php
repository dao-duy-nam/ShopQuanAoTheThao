<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'tin_nhans'; 

    protected $fillable = [
        'nguoi_gui_id',
        'nguoi_nhan_id',
        'noi_dung',
        'tep_dinh_kem',
        'da_doc_luc',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'nguoi_gui_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'nguoi_nhan_id');
    }
}
