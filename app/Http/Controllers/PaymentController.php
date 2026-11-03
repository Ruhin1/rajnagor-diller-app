<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
public function srcustomersdues(Request $request)
{
    // সব ইউজার লিস্ট ড্রপডাউন ভরাটের জন্য
    $users = User::whereIn('role', [0, 1, 2])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

    $user = null;
    $amount = 0; 
    $payments = collect(); // একটি খালি কালেকশন তৈরি করা হলো

    // যদি query param থেকে user_id আসে, তখন ডেটা লোড করব
    if ($request->has('user_id') && $request->user_id) {
        
        // Eager Loading: srpayment এর সাথে customer এবং dealer এর তথ্যও একবারে লোড করা হচ্ছে
        $user = User::with(['srpayment' => function ($query) {
            $query->where('confrim', 0)
                  ->with(['customer', 'dealer']); // <-- মূল অপ্টিমাইজেশন এখানে
        }])->find($request->user_id);

        if ($user) {
            $payments = $user->srpayment;
            $amount = $payments->sum('amount');
        }
    }

    return view('sr.srcustomersdues', compact('users', 'user', 'amount', 'payments'));
}
 
// YourController.php

public function srdealersdues(Request $request)
{
    // সব ইউজার লিস্ট ড্রপডাউন ভরাটের জন্য
    $users = User::whereIn('role', [0, 1, 2])
            ->select('id', 'name')
            ->orderBy('name')
            ->get(); 

    $user = null;
    $amount = 0;
    $payments = collect(); // একটি খালি কালেকশন

    // যদি query param থেকে user_id আসে, তখন ডেটা লোড করব
    if ($request->has('user_id') && $request->user_id) {
        
        // Eager Loading: srpayment এর সাথে customer এবং dealer এর তথ্যও একবারে লোড করা হচ্ছে
        $user = User::with(['srpayment' => function ($query) {
            $query->where('confrim', 1) // শুধুমাত্র কনফার্মড পেমেন্ট
                  ->with(['customer', 'dealer']) // <-- ভবিষ্যতের জন্য অপ্টিমাইজেশন
                  ->latest('paid_at'); // নতুন পেমেন্টগুলো আগে দেখানোর জন্য
        }])->find($request->user_id); 

        if ($user) {
            $payments = $user->srpayment;
            $amount = $payments->sum('amount');
        }
    }

    return view('sr.srdealersdues', compact('users', 'user', 'amount', 'payments'));
}

    // একক payment confirm
    public function confirm(Payment $payment)
    {
        $payment->update(['confrim' => 1]); // confrim = 1 করে দেওয়া হলো
        return back()->with('success', 'Payment confirmed successfully!');
    }

    // ইউজারের সব payment confirm
    public function confirmAll(User $user)
    {
        $user->srpayment()->update(['confrim' => 1]);
        return back()->with('success', 'All payments confirmed successfully!');
    }
}

