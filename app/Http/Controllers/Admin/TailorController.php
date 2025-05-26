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
        return inertia('admin/employee/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/employee/Detail', [
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
        return inertia('admin/employee/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? Tailor::findOrFail($id) : new Tailor(['active' => true]);
        return inertia('admin/employee/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => 'required|max:100',
            'address' => 'required|max:1000',
            'active' => 'boolean',
        ]);

        $item = !$request->id ? new Tailor() : Tailor::findOrFail($request->post('id', 0));
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.employee.index'))->with('success', "Karyawan {$item->name} telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Tailor::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Karyawan {$item->name} telah dihapus.",
        ]);
    }
}
