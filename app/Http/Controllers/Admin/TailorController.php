<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tailor;
use App\Models\User;
use Illuminate\Http\Request;

class TailorController extends Controller
{
    public function index()
    {
        return inertia('admin/tailor/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/tailor/Detail', [
            'data' => Tailor::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Tailor::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = Tailor::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/tailor/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? Tailor::findOrFail($id) : new Tailor(['active' => true]);
        return inertia('admin/tailor/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'phone' => 'required|max:100',
            'address' => 'required|max:1000',
        ];

        $item = null;
        $message = '';
        $fields = ['name', 'phone', 'address', 'active'];

        $request->validate($rules);

        if (!$request->id) {
            $item = new Tailor();
            $message = 'tailor-created';
        } else {
            $item = Tailor::findOrFail($request->post('id', 0));
            $message = 'tailor-updated';
        }

        $item->fill($request->only($fields));
        $item->save();

        return redirect(route('admin.tailor.index'))->with('success', __("messages.$message", ['name' => $item->name]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Tailor::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.tailor-deleted', ['name' => $item->name])
        ]);
    }
}
