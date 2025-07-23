<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;


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
}
