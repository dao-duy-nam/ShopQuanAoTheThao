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
        $currentUser = $request->user();
        if (in_array($currentUser->vai_tro_id, [1, 3])) {
            $userIds = Message::select('nguoi_gui_id', 'nguoi_nhan_id')
                ->get()
                ->flatMap(function ($message) {
                    return [$message->nguoi_gui_id, $message->nguoi_nhan_id];
                })
                ->unique()
                ->filter()
                ->values();
        } else {
            $userIds = Message::where('nguoi_gui_id', $currentUser->id)
                ->orWhere('nguoi_nhan_id', $currentUser->id)
                ->get()
                ->map(function ($message) use ($currentUser) {
                    return $message->nguoi_gui_id == $currentUser->id
                        ? $message->nguoi_nhan_id
                        : $message->nguoi_gui_id;
                })
                ->unique()
                ->filter()
                ->values();
        }

        $users = User::whereIn('id', $userIds)
            ->where('vai_tro_id', 2)
            ->get();

        return response()->json([
            'data' => $users
        ]);
    }


    public function getMessagesWithUser(Request $request, $userId)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->vai_tro_id, [1, 3])) {
            return response()->json([
                'message' => 'Bạn không có quyền xem đoạn chat này.'
            ], 403);
        }

        $messages = Message::with(['sender', 'receiver'])
            ->where(function ($query) use ($userId) {
                $query->where('nguoi_gui_id', $userId)
                    ->orWhere('nguoi_nhan_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'noi_dung' => $message->noi_dung,
                    'tep_dinh_kem' => $message->tep_dinh_kem,
                    'nguoi_gui_id' => $message->nguoi_gui_id,
                    'nguoi_gui_name' => $message->sender->name ?? null,
                    'nguoi_nhan_id' => $message->nguoi_nhan_id,
                    'nguoi_nhan_name' => $message->receiver->name ?? null,
                    'created_at' => $message->created_at,
                ];
            });

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
            'data'    => [
                'id' => $message->id,
                'noi_dung' => $message->noi_dung,
                'tep_dinh_kem' => $message->tep_dinh_kem,
                'nguoi_gui_id' => $message->nguoi_gui_id,
                'nguoi_nhan_id' => $message->nguoi_nhan_id,
                'created_at' => $message->created_at,
            ],
        ], 200);
    }
}
