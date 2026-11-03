<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomLedgerController extends Controller
{
    public function index()
    {
        $customers = CustomCustomer::latest()->paginate(50);
        return view('custom.index', compact('customers'));
    } 

    public function create()
    {
        return view('custom.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'due_amount' => 'required|numeric|min:0', 
        ]);
        CustomCustomer::create($validated);
        return redirect()->route('custom.customers.index')->with('success', 'নতুন কাস্টমার সফলভাবে যোগ করা হয়েছে।');
    }

    public function storeTransaction(Request $request, CustomCustomer $customer)
    {
        $validated = $request->validate([
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['type'] === 'credit' && $validated['amount'] > $customer->due_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'জমার পরিমাণ মোট বকেয়ার (' . $customer->due_amount . ' ৳) চেয়ে বেশি হতে পারবে না।',
                ]);
            }

            // লেনদেন রেকর্ড তৈরি
            $customer->transactions()->create($validated);

            // কাস্টমারের মোট বকেয়া আপডেট
            if ($validated['type'] === 'debit') {
                $customer->increment('due_amount', $validated['amount']);
            } else {
                $customer->decrement('due_amount', $validated['amount']);
            }
            
            DB::commit();
            return back()->with('success', 'লেনদেন সফলভাবে সংরক্ষণ করা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'একটি সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    public function statement(CustomCustomer $customer)
    {
        $transactions = $customer->transactions;
        return view('custom.statement', compact('customer', 'transactions'));
    }
}
