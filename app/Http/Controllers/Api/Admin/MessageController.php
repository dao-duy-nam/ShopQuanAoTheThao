<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    
    public function getUserList(Request $request)
    {
        $adminId = $request->user()->id;

        
        $userIds = Message::where('nguoi_gui_id', $adminId)
            ->orWhere('nguoi_nhan_id', $adminId)
            ->get()
            ->map(function ($message) use ($adminId) {
                return $message->nguoi_gui_id == $adminId
                    ? $message->nguoi_nhan_id
                    : $message->nguoi_gui_id;
            })
            ->unique()
            ->values();

       
        $users = User::whereIn('id', $userIds)->get();

        return response()->json([
            'data' => $users
        ]);
    }

   
    public function getMessagesWithUser(Request $request, $userId)
    {
        $adminId = $request->user()->id;

        $messages = Message::where(function ($q) use ($adminId, $userId) {
            $q->where('nguoi_gui_id', $adminId)
                ->where('nguoi_nhan_id', $userId);
        })->orWhere(function ($q) use ($adminId, $userId) {
            $q->where('nguoi_gui_id', $userId)
                ->where('nguoi_nhan_id', $adminId);
        })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'data' => $messages,
        ]);
    }

    
    public function sendMessageToUser(Request $request)
    {
        $data = $request->validate([
            'nguoi_nhan_id' => 'required|exists:users,id',
            'noi_dung'      => 'nullable|string',
            'tep_dinh_kem'  => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:2048',
        ]);

        $data['nguoi_gui_id'] = $request->user()->id;

        if ($request->hasFile('tep_dinh_kem')) {
            $path = $request->file('tep_dinh_kem')->store('tin_nhan', 'public');
            $data['tep_dinh_kem'] = $path;
        }

        $message = Message::create($data);

        return response()->json([
            'message' => 'Gửi tin nhắn thành công',
            'data'    => $message,
        ], 201);
    }
}
