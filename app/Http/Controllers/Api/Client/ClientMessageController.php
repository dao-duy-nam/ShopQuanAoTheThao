<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientMessageController extends Controller
{
    public function getMessagesWithAdmin(Request $request)
    {
        $clientId = $request->user()->id;
        $adminIds = User::whereIn('vai_tro_id', [1, 3])->pluck('id')->toArray();

        $messages = Message::with(['sender:id,name,vai_tro_id', 'receiver:id,name,vai_tro_id'])
            ->where(function ($query) use ($clientId, $adminIds) {
                $query->where('nguoi_gui_id', $clientId)
                    ->whereIn('nguoi_nhan_id', $adminIds);
            })->orWhere(function ($query) use ($clientId, $adminIds) {
                $query->whereIn('nguoi_gui_id', $adminIds)
                    ->where('nguoi_nhan_id', $clientId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $formatted = $messages->map(function ($m) {
            return [
                'id' => $m->id,
                'nguoi_gui' => [
                    'id' => $m->sender->id,
                    'name' => in_array($m->sender->vai_tro_id, [1, 3]) ? 'Chat Support' : $m->sender->name,
                ],
                'nguoi_nhan' => [
                    'id' => $m->receiver->id,
                    'name' => in_array($m->receiver->vai_tro_id, [1, 3]) ? 'Chat Support' : $m->receiver->name,
                ],
                'noi_dung' => $m->noi_dung,
                'tep_dinh_kem' => $m->tep_dinh_kem ? asset('storage/' . $m->tep_dinh_kem) : null,
                'created_at' => $m->created_at,
            ];
        });

        return response()->json([
            'data' => $formatted
        ]);
    }


    public function sendMessageToAdmin(Request $request)
    {
        $request->validate([
            'noi_dung' => 'nullable|string|max:1000',
            'tep_dinh_kem' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:5120',
        ]);

        $client = $request->user();
        $receiver = User::where('email', 'chat@gmail.com')->firstOrFail();

        $filePath = null;
        if ($request->hasFile('tep_dinh_kem')) {
            $filePath = $request->file('tep_dinh_kem')->store('tin_nhans', 'public');
        }

        $message = Message::create([
            'nguoi_gui_id' => $client->id,
            'nguoi_nhan_id' => $receiver->id,
            'noi_dung' => $request->noi_dung,
            'tep_dinh_kem' => $filePath,
        ]);

        return response()->json([
            'message' => 'Đã gửi tin nhắn thành công.',
            'data' => $message,
        ], 201);
    }
}
