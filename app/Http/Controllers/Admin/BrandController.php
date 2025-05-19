<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        return inertia('admin/brand/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/brand/Detail', [
            'data' => Brand::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Brand::query();

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
        $item = Brand::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/brand/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? Brand::findOrFail($id) : new Brand(['active' => true]);
        return inertia('admin/brand/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => 'required|max:100',
            'address' => 'required|max:1000',
            'active' => 'required',
        ]);

        $item = !$request->id ? new Brand() : Brand::findOrFail($request->post('id', 0));
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.brand.index'))->with('success', "Brand $item->name telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Brand::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Brand $item->name telah dihapus."
        ]);
    }
}
