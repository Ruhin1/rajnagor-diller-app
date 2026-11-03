<?php

namespace App\Http\Controllers;

use App\Models\Cost;
use Illuminate\Http\Request;
use Carbon\Carbon; // ফাইলের উপরে যুক্ত করুন
 
class CostController extends Controller
{

    public function index(Request $request)
{ 
    // সারসংক্ষেপ কার্ডের জন্য ডেটা
    $todayExpense = Cost::whereDate('created_at', Carbon::today())->sum('amount');
    $weekExpense = Cost::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount');
    $monthExpense = Cost::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->sum('amount');

    // টেবিলের জন্য ডেটা
    $query = Cost::query();

    // তারিখ ফিল্টার
    if ($request->filled('from') && $request->filled('to')) {
        $query->whereBetween('created_at', [$request->from, $request->to]);
    }

    $costs = $query->orderBy('id', 'desc')->get();

    // যদি AJAX রিকোয়েস্ট হয়, তাহলে শুধু JSON ডেটা পাঠানো হবে
    if ($request->ajax()) {
        return response()->json($costs);
    }
    
    // সাধারণ রিকোয়েস্টের জন্য ভিউ এবং সারসংক্ষেপের ডেটা পাঠানো হবে
    return view('cost', compact('costs', 'todayExpense', 'weekExpense', 'monthExpense'));
}

    //✅ খরচ Store
    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
            'created_at' => 'required|date',
        ]);

        $validatedData['updated_at'] = $validatedData['created_at'];

        
        $expense = Cost::create($validatedData);
        
        return response()->json(['status' => 'success', 'expense' => $expense], 201);
    }
    // ✅ খরচ Update
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'amount' => 'required|numeric', 
            'note' => 'nullable',
        ]);

        $expense = Cost::findOrFail($id);
        $expense->update($request->all());

        return response()->json(['status' => 'success', 'expense' => $expense]);
    }

    // ✅ খরচ Delete
    public function destroy($id)
    {
        $expense = Cost::findOrFail($id);
        $expense->delete();

        return response()->json(['status' => 'success']);
    }

    
}
