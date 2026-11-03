<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BluetoothPrintController extends Controller
{
    /**
     * Bluetooth প্রিন্টিং এর জন্য ESC/POS commands generate করুন
     */
    public function generateBluetoothPrint(Request $request)
    {
        try {
            $invoiceId = $request->invoice_id;
            
            // Invoice data fetch করুন (আপনার existing logic)
            $invoice = $this->getInvoiceData($invoiceId);
            
            // ESC/POS commands generate করুন
            $escpos = $this->buildESCPOSCommands($invoice);
            
            // Base64 encode (RawBT এর জন্য)
            $base64Data = base64_encode($escpos);
            
            // RawBT Intent URL
            $intentUrl = "rawbt:base64," . $base64Data;
            
            return response()->json([
                'success' => true,
                'intent_url' => $intentUrl,
                'invoice_id' => $invoiceId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Bluetooth Print Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Invoice data fetch করুন (আপনার existing logic অনুযায়ী modify করুন)
     */
    private function getInvoiceData($invoiceId)
    {
        // আপনার existing Invoice model ব্যবহার করুন
        // $invoice = \App\Models\Invoice::with(['items.product', 'customer'])
        //     ->findOrFail($invoiceId);
        
        return [
            'id' => '$invoice->id',
            'date' => '$invoice->created_at->format()',
            'shop_name' => 'fgdddddddddddfdgfg',
            'customer_name' => 'fgddddddddddf',
            'address' => 'dsfdsfdsfdsfdsfdsfdsf',
            'mobile' => 'dsfsdfsdfsdf',
            'client_type' => 'customer',
            'items' => 'cbcvbvcb',
            'subtotal' => 'fdgdfgfdg',
            'discount' => 'fdgfdgdfgfg',
            'shipping' => 'fdgdfgdfgdfg',
            'total' => 'fdgdfgfdgdfg',
            'paid' => 'fdgdfgdfgdfg',
            'due' => 'fdgdfgdfggdf',
            'total_due' => 'fdgfdgfdg',
            'prev_due_count' => 'dfgfdgdfg',
        ];
    }
    
    /**
     * ESC/POS commands তৈরি করুন (Text + Raster Image)
     */
    private function buildESCPOSCommands($data)
    {
        $esc = chr(27);  // ESC
        $gs = chr(29);   // GS
        
        $output = '';
        
        // Initialize printer
        $output .= $esc . "@";
        
        // --- হেডার ---
        $output .= $esc . "a" . chr(1);  // Center align
        $output .= $this->setTextSize(2, 2);
        $output .= "GF Food & Beverage Ltd.\n";
        
        $output .= $this->setTextSize(1, 1);
        $output .= "Chadnighat, Moulvibazar.\n";
        $output .= "Phone: 01756697683\n";
        $output .= str_repeat("-", 32) . "\n";
        
        // --- Invoice Info ---
        $output .= $esc . "a" . chr(0);  // Left align
        $output .= "Invoice No: #INV-" . $data['id'] . "\n";
        $output .= "Order Date: " . $data['date'] . "\n";
        $output .= str_repeat("-", 32) . "\n";
        
        // --- Client Info ---
        $output .= $this->setTextSize(1, 1);
        $output .= "Bill To:\n";
        $output .= $this->setTextSize(2, 1);
        
        // বাংলা text এর জন্য image rendering করুন
        $output .= $this->renderBanglaTextAsImage($data['shop_name'], 24);
        
        $output .= $this->setTextSize(1, 1);
        if (!empty($data['customer_name'])) {
            $output .= "Owner: " . $data['customer_name'] . "\n";
        }
        if (!empty($data['address'])) {
            $output .= $this->renderBanglaTextAsImage($data['address'], 18);
        }
        if (!empty($data['mobile'])) {
            $output .= "Mobile: " . $data['mobile'] . "\n";
        }
        $output .= str_repeat("-", 32) . "\n";
        
        // --- Items ---
        foreach ($data['items'] as $item) {
            $productName = $item->product->name ?? 'Item';
            
            // বাংলা পণ্যের নাম image হিসেবে
            $output .= $this->renderBanglaTextAsImage($productName, 20);
            
            // হিসাব (ইংরেজিতে)
            $qty = $item->quantity ?? 0;
            $price = number_format($item->price ?? 0, 2);
            $total = number_format($item->total ?? 0, 2);
            
            $output .= $esc . "a" . chr(2);  // Right align
            $output .= "{$qty} x {$price} = {$total}\n";
            $output .= $esc . "a" . chr(0);  // Left align
        }
        
        $output .= str_repeat("-", 32) . "\n";
        
        // --- Totals ---
        $output .= $this->printRow("Subtotal:", number_format($data['subtotal'], 2));
        
        if ($data['discount'] > 0) {
            $output .= $this->printRow("Discount (-):", number_format($data['discount'], 2));
        }
        
        if ($data['shipping'] > 0) {
            $output .= $this->printRow("Other Cost (+):", number_format($data['shipping'], 2));
        }
        
        $output .= "\n";
        $output .= $this->setTextSize(2, 2);
        $output .= $esc . "a" . chr(1);  // Center
        $output .= "Total: " . number_format($data['total'], 2) . "\n";
        $output .= $this->setTextSize(1, 1);
        $output .= $esc . "a" . chr(0);  // Left
        
        $output .= $this->printRow("Paid:", number_format($data['paid'], 2));
        $output .= $this->printRow("Due:", number_format($data['due'], 2));
        
        $output .= str_repeat("-", 32) . "\n";
        
        // --- Previous Due ---
        $grandTotalDue = $data['total_due'] ?? 0;
        $currentDue = $data['due'] ?? 0;
        
        if ($grandTotalDue > $currentDue) {
            $prevDue = $grandTotalDue - $currentDue;
            $prevCount = $data['prev_due_count'] ?? 0;
            
            if ($prevDue > 0) {
                $output .= $this->printRow("Prev. Due ({$prevCount} Inv):", number_format($prevDue, 2));
                
                $output .= "\n";
                $output .= $this->setTextSize(2, 2);
                $output .= $esc . "a" . chr(1);
                $output .= "Total Due: " . number_format($grandTotalDue, 2) . "\n";
                $output .= $this->setTextSize(1, 1);
            }
        }
        
        // --- QR Code ---
        $output .= "\n\n";
        $output .= $esc . "a" . chr(1);  // Center
        $output .= $this->generateQRCode($data['id']);
        $output .= "Scan for Details\n\n";
        
        // --- Footer ---
        // বাংলা ধন্যবাদ message image হিসেবে
        $output .= $this->renderBanglaTextAsImage("আমাদের সাথে থাকার জন্য ধন্যবাদ!", 20);
        
        // Paper cut
        $output .= "\n\n\n";
        $output .= $gs . "V" . chr(66) . chr(0);  // Partial cut
        
        return $output;
    }
    
    /**
     * Text size set করুন
     */
    private function setTextSize($width, $height)
    {
        $esc = chr(27);
        $size = (($width - 1) << 4) | ($height - 1);
        return $esc . "!" . chr($size);
    }
    
    /**
     * Row format (label এবং value দুই পাশে)
     */
    private function printRow($label, $value)
    {
        $maxChars = 32;  // 58mm paper
        $labelLen = strlen($label);
        $valueLen = strlen($value);
        
        $spaces = $maxChars - ($labelLen + $valueLen);
        if ($spaces < 1) $spaces = 1;
        
        return $label . str_repeat(" ", $spaces) . $value . "\n";
    }
    
    /**
     * QR Code generate করুন
     */
    private function generateQRCode($data)
    {
        $gs = chr(29);
        
        $qr = '';
        
        // QR Code Model
        $qr .= $gs . "(k" . chr(4) . chr(0) . chr(49) . chr(65) . chr(50) . chr(0);
        
        // QR Code Size
        $qr .= $gs . "(k" . chr(3) . chr(0) . chr(49) . chr(67) . chr(6);
        
        // QR Code Error Correction
        $qr .= $gs . "(k" . chr(3) . chr(0) . chr(49) . chr(69) . chr(48);
        
        // Store data
        $dataLength = strlen($data) + 3;
        $pL = $dataLength % 256;
        $pH = intdiv($dataLength, 256);
        $qr .= $gs . "(k" . chr($pL) . chr($pH) . chr(49) . chr(80) . chr(48) . $data;
        
        // Print QR
        $qr .= $gs . "(k" . chr(3) . chr(0) . chr(49) . chr(81) . chr(48);
        
        return $qr;
    }
    
    /**
     * বাংলা text কে raster image হিসেবে render করুন
     * (সাময়িক সমাধান - শুধু placeholder দেখাবে)
     */
    private function renderBanglaTextAsImage($text, $fontSize)
    {
        // যেহেতু Bluetooth printing এ complex image rendering কঠিন,
        // আপাতত আমরা text টাই পাঠাচ্ছি
        // পরে যদি image support দরকার হয়, server থেকে pre-rendered image পাঠাতে হবে
        
        // Simple text fallback
        return $text . "\n";
    }
}