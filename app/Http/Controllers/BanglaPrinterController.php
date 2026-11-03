<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // বেস কন্ট্রোলার ইমপোর্ট করা জরুরি
use App\Services\BanglaPrinterService; // আপনার সার্ভিস ক্লাস

class BanglaPrinterController extends Controller
{
    private $printerService;
    
    // Dependency Injection এর মাধ্যমে সার্ভিস লোড করা হচ্ছে
    public function __construct(BanglaPrinterService $printerService)
    {
        $this->printerService = $printerService;
    }
    
    /**
     * ১. কানেকশন টেস্ট (ইংরেজি)
     * যদি সার্ভিসে এই মেথড না থাকে, তবে এরর হ্যান্ডেল করা হয়েছে
     */
    public function testConnection()
    {
        try {
            // চেক করা হচ্ছে মেথডটি সার্ভিসে আছে কিনা
            if (method_exists($this->printerService, 'testConnection')) {
                $result = $this->printerService->testConnection();
                return response()->json($result);
            }
            
            return response()->json([
                'success' => false, 
                'message' => 'Method testConnection() not found in Service.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * ২. বাংলা টেস্ট প্রিন্ট
     */
    public function testPrint()
    {
        try {
            // সার্ভিস ক্লাসের testPrint মেথড কল করা
            if (method_exists($this->printerService, 'testPrint')) {
                $result = $this->printerService->testPrint();
                return response()->json($result);
            }

            // যদি সার্ভিসে testPrint মেথড না থাকে, তবে ম্যানুয়ালি একটি ডামি ইনভয়েস পাঠানো হবে
            $dummyData = [
                'shop_name' => 'টেস্ট শপ',
                'address' => 'মিরপুর, ঢাকা',
                'invoice_no' => 'TEST-101',
                'date' => date('d/m/Y'),
                'items' => [
                    ['name' => 'টেস্ট পণ্য', 'qty' => 1, 'price' => 100]
                ],
                'total' => 100
            ];
            
            $result = $this->printerService->printBanglaInvoice($dummyData);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test Print Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * ৩. রিয়েল ইনভয়েস প্রিন্ট (Frontend থেকে ডেটা আসবে)
     */
    public function printInvoice(Request $request)
    {
        // ভ্যালিডেশন: আইটেম অবশ্যই অ্যারে হতে হবে
        $request->validate([
            'items' => 'nullable|array',
            'shop_name' => 'nullable|string',
            'total' => 'nullable|numeric'
        ]);

        $invoiceData = [
            'shop_name' => $request->shop_name ?? 'আমার দোকান',
            'address' => $request->address ?? 'ঢাকা, বাংলাদেশ',
            'invoice_no' => $request->invoice_no ?? 'INV-' . time(),
            'date' => date('d/m/Y'),
            'items' => $request->items ?? [], // আইটেম না থাকলে খালি অ্যারে
            'total' => $request->total ?? 0
        ];
        
        try {
            $result = $this->printerService->printBanglaInvoice($invoiceData);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Printing Failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ৪. সিস্টেম চেক (ডিবাগিং)
     */
    public function checkSystem()
    {
        // আপনার ব্যবহৃত ফন্ট ফাইলটি এখানে নিশ্চিত করুন
        // যদি SolaimanLipi ব্যবহার করেন তবে নাম পরিবর্তন করুন
        $fontName = 'kalpurush.ttf'; 
        
        $info = [
            'php_version' => phpversion(),
            'gd_extension' => extension_loaded('gd') ? 'Enabled' : 'Disabled',
            'gd_info' => function_exists('gd_info') ? gd_info() : 'N/A',
            'font_path_public' => public_path("fonts/{$fontName}"),
            'font_exists_public' => file_exists(public_path("fonts/{$fontName}")),
            'temp_directory' => sys_get_temp_dir(),
            'is_temp_writable' => is_writable(sys_get_temp_dir()),
        ];
        
        return response()->json($info);
    }
}