<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ProductionTailorPayment;
use App\Models\ProductionTailorPayroll;
use App\Models\ProductionWorkReturn;
use App\Models\Tailor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionTailorPayrollController extends Controller
{
    public function index()
    {
        return inertia('admin/production-tailor-payroll/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        // Query utama
        $q = ProductionTailorPayroll::with(['tailor']);

        // Filter
        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('notes', 'like', '%' . $filter['search'] . '%')
                    ->orWhereHas('tailor', function ($q2) use ($filter) {
                        $q2->where('name', 'like', '%' . $filter['search'] . '%');
                    });
            });
        }

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('production_orders.status', '=', $filter['status']);
        }

        $q->orderBy($orderBy, $orderType);

        // Paginate dan ambil data
        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? ProductionTailorPayroll::findOrFail($id) : new ProductionTailorPayroll([
            'tailor_id' => null,
            'period_start' => null,
            'period_end' => Carbon::now(),
            'total_amount' => 0,
            'status',
        ]);

        return inertia('admin/production-tailor-payroll/Editor', [
            'data' => $item,
            'tailors' => Tailor::where('active', '=', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'tailor_id' => 'required|exists:tailors,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'total_amount' => 'required|numeric',
            // 'status' => 'required|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'paid';

        DB::beginTransaction();

        $item = new ProductionTailorPayroll();
        $item->fill($validated);
        $item->save();

        // ambil data work_returns yang belum dibayar berdasarkan tailor_id,
        // untuk masing-maising item, buat rekaman ProductionTailorPayment

        // 2. Ambil work_return yang belum dibayar
        $returns = ProductionWorkReturn::whereHas('work_assignment', function ($query) use ($validated) {
            $query->where('tailor_id', $validated['tailor_id']);
        })
            ->whereBetween('datetime', [$validated['period_start'] . ' 00:00:00', $validated['period_end'] . ' 23:59:59'])
            ->where('is_paid', false)
            ->get();

        foreach ($returns as $return) {
            // 3. Buat rekaman pembayaran
            ProductionTailorPayment::create([
                'payroll_id' => $item->id,
                'datetime' => Carbon::now(),
                'work_return_id' => $return->id,
                'quantity' => $return->quantity,
                'cost' => $return->work_assignment->order_item->unit_cost,
                'amount' => $return->quantity * $return->work_assignment->order_item->unit_cost,
                'method' => 'cash',
                'notes' => 'Dibuat dari penggajian #' . $item->id
            ]);

            // 4. Tandai work_return sudah dibayar
            $return->is_paid = true;
            $return->save();
        }


        DB::commit();

        return response()->json([
            'data' => $item,
            'message' => "Rekaman gaji #$item->id telah disimpan."
        ]);
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = ProductionTailorPayroll::findOrFail($id);
        $payments = ProductionTailorPayment::where('payroll_id', $item->id)->get();

        DB::beginTransaction();
        foreach ($payments as $payment) {
            $payment->delete(); // otomatis set is_paid = false di production_work_return
        }
        $item->delete();
        DB::commit();

        return response()->json([
            'message' => "Rekaman gaji #$item->id telah dihapus."
        ]);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'tailor_id' => 'required|exists:tailors,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $tailorId = $validated['tailor_id'];
        $start = $validated['period_start'];
        $end = $validated['period_end'];

        $q = ProductionWorkReturn::with([
            'work_assignment',
            'work_assignment.tailor',
            'work_assignment.order_item',
            'work_assignment.order_item.order',
            'work_assignment.order_item.order.customer',
        ]);

        $q->whereHas('work_assignment', function ($query) use ($tailorId) {
            $query->where('tailor_id', $tailorId);
        });

        $q->whereBetween('datetime', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->where('is_paid', false)
            ->orderBy('datetime', 'asc');

        $items = $q->get();
        $totalQuantity = $items->sum('quantity');
        $totalCost = $items->sum(function ($item) {
            return $item->work_assignment->order_item->unit_cost * $item->quantity;
        });

        return response()->json([
            'items' => $items,
            'total_quantity' => $totalQuantity,
            'total_cost' => $totalCost,
        ]);
    }

    
    public function detail($id)
    {
        $item = ProductionTailorPayroll::with([
            'tailor',
            'payments',
            'payments.work_return',
            'payments.work_return.work_assignment',
            'payments.work_return.work_assignment.order_item',
            'payments.work_return.work_assignment.order_item.order',
        ])->findOrFail($id);
        return inertia('admin/production-tailor-payroll/Detail', [
            'data' => $item
        ]);
    }
}
