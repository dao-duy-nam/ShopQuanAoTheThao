<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Mail\ContactReplyMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;


class ContactController extends Controller
{
    public function search(Request $request)
    {
        $query = Contact::query();

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status); // chua_xu_ly, dang_xu_ly, da_tra_loi
        }


        if ($request->has('q') && $request->q !== null) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('subject', 'like', "%$q%")
                    ->orWhere('message', 'like', "%$q%")
                    ->orWhere('type', 'like', "%$q%");
            });
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate(10)
        );
    }

    public function index(Request $request)
    {
        $status = $request->query('status'); // VD: ?status=da_tra_loi
        $query = Contact::query();

        if ($status) {
            $query->where('status', $status);
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(10));
    }
    public function show($id)
    {
        $contact = Contact::findOrFail($id);
        return response()->json($contact);
    }
    public function reply(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $validated = $request->validate([
            'reply_content' => 'required|string',
        ]);
        Mail::to($contact->email)->send(new ContactReplyMail($contact, $validated['reply_content']));
        $contact->update([
            'reply_content' => $validated['reply_content'],
            'status' => 'da_tra_loi',
            'replied_at' => now(),
        ]);

        return response()->json(['message' => 'Đã phản hồi liên hệ.']);
    }
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:chua_xu_ly,dang_xu_ly,da_tra_loi',
        ]);

        $contact = Contact::findOrFail($id);
        $contact->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Cập nhật trạng thái thành công.']);
    }
     public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return response()->json(['message' => 'Đã xoá liên hệ.']);
    } 
    //
}
