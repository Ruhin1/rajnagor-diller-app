<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; 

class AcountController extends Controller
{
    public function create(){

        $query = User::select('id','name','email','password','role');

        if (auth()->user()->access == 1) {
            $query->where('access', 1);         // ✅ শুধু access=1
        } else {
            $query->where('access', '!=', 1);   // ✅ access=1 বাদ, বাকিরা
        }

        $users = $query->paginate(10);
        return view("acount.create", compact("users"));
    } 

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'role'     => 'required|string'
        ]);

        if($request->role == 100){
        return redirect()->back()->with('error', 'Please select a roll.');
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role'    => $request->role,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->back()->with('success', 'User registered successfully.');
    }
     // ইউজার এডিট ফর্ম
    public function edit(User $user)
    {
        return view('acount.edit', compact('user'));
    }

    // ইউজার আপডেট
    public function update(Request $request, User $user) // অথবা User $acount
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'role' => 'required|in:0,1,2,3,10',
        'password' => 'nullable|string|min:8|confirmed',
    ]);

    // যদি ভ্যালিডেশন ফেইল করে, তাহলে withErrors এর সাথে user id পাঠানো হচ্ছে
    // ->with('edit_error_id', $user->id) এই অংশটি যুক্ত করুন
    $validator = Validator::make($request->all(), [/*... validation rules ...*/]);
    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('edit_error_id', $user->id); 
    }
    
    $data = [
        'name' => $validated['name'],
        'email' => $validated['email'],
        'role' => $validated['role'],
    ];

    if (!empty($validated['password'])) {
        $data['password'] = Hash::make($validated['password']);
    }

    $user->update($data);

    return redirect()->route('acount.create')->with('success', 'একাউন্ট সফলভাবে আপডেট হয়েছে।');
}

    // // ইউজার ডিলিট
    public function destroy(User $user)
    {   
        if($user->id == 1){

        return redirect()->route('acount.create')->with('error', 'এটি এডমিনের মেইন একাউন্ট এটি ডিলেট করা যাবে না');
          
        }
        $user->delete();
        return redirect()->route('acount.create')->with('success', 'User deleted successfully.');
    }

}
