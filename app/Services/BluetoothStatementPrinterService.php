<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BluetoothStatementPrinterService 
{
    private $fontPath;
    
    public function __construct()
    {
        // Font path for image generation
        $fonts = [
            '/usr/share/fonts/google-noto/NotoSansBengali-Bold.ttf',
            '/usr/share/fonts/google-noto/NotoSansBengali-Regular.ttf',
            public_path('fonts/NotoSansBengali-Regular.ttf'),
        ];
        
        foreach ($fonts as $font) {
            if (file_exists($font)) {
                $this->fontPath = $font;
                break;
            }
        }
    }

    /**
     * Bluetooth Statement প্রিন্টিং এর জন্য ESC/POS commands generate করুন
     */
    public function generateBluetoothStatement($data)
    {
        try {
            $type = $data['type'] ?? 'customer'; // 'customer' or 'dealer'
            Log::info("Generating Bluetooth Statement for " . strtoupper($type) . " - ID: " . ($data['customer_id'] ?? 'Unknown'));
            
            // ESC/POS commands generate করুন
            $escpos = $this->buildStatementCommands($data);
            
            // Base64 encode (RawBT এর জন্য)
            $base64Data = base64_encode($escpos);
            
            // RawBT Intent URL
            $intentUrl = "rawbt:base64," . $base64Data;
            
            return [
                'success' => true,
                'intent_url' => $intentUrl,
                'customer_id' => $data['customer_id'] ?? null
            ];
            
        } catch (\Exception $e) {
            Log::error('Bluetooth Statement Print Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Statement ESC/POS commands তৈরি করুন (48mm paper)
     */
    private function buildStatementCommands($data)
    {
        $esc = chr(27);  // ESC
        $gs = chr(29);   // GS
        
        $output = '';
        
        // Initialize printer
        $output .= $esc . "@";
        
        $type = $data['type'] ?? 'customer';
        
        // Domain check
        $domain = url('/');
        $compAddress = "এমপির বাড়ির গেইট, 2য় তলা, চাঁদনীঘাট, মৌলভীবাজার।";
        $compPhone = "Phone: 01756697683";
        
        if ($domain == 'https://app.gffoodandbeverage.com') {
            $compAddress = "ভানুগাছ বাজার, কমলগঞ্জ, মৌলভীবাজার";
            $compPhone = "Phone: 01344-558778";
        }
        
        // --- ১. হেডার ---
        $output .= $esc . "a" . chr(0);  // Left align
        $output .= $this->setTextSize(1, 1);
        
        $output .= $this->renderTextAsImage("GF Food and beverage LTD", 12);
        $output .= $this->renderTextAsImage($compAddress, 9);
        $output .= $compPhone . "\n";
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ২. টাইটেল ---
        $output .= $esc . "a" . chr(1);  // Center
        $output .= $this->renderTextAsImage("বকেয়ার বিবরণী", 11);
        $output .= "(Statement of Account)\n";
        $output .= $esc . "a" . chr(0);  // Left align
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ৩. Customer/Dealer Info ---
        if ($type === 'dealer') {
            // Dealer information
            $output .= $this->renderTextAsImage("ডিলারের নাম: " . ($data['customer_name'] ?? ''), 9);
            $output .= $this->renderTextAsImage("প্রতিষ্ঠান: " . ($data['shop_name'] ?? 'N/A'), 9);
        } else {
            // Customer information
            $output .= $this->renderTextAsImage("কাস্টমার: " . ($data['customer_name'] ?? ''), 9);
            $output .= $this->renderTextAsImage("দোকান: " . ($data['shop_name'] ?? 'N/A'), 9);
        }
        
        if (!empty($data['mobile'])) {
            $output .= "Mobile: " . $data['mobile'] . "\n";
        }
        
        if (!empty($data['address'])) {
            $output .= $this->renderTextAsImage("ঠিকানা: " . $data['address'], 8);
        }
        
        // $output .= "তারিখ: " . ($data['statement_date'] ?? date('d-m-Y')) . "\n";
        $output .= $this->renderTextAsImage("তারিখ: " . ($data['statement_date'] ?? date('d-m-Y')), 8);
        
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ৪. Transaction Table Header ---
        $output .= $this->renderTextAsImage("তারিখ  বিবরণ     দেনা  জমা", 8);
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ৫. Transactions ---
        $balance = 0;
        
        foreach ($data['transactions'] as $transaction) {
            if ($transaction['type'] === 'sale') {
                $sale = $transaction['data'];
                $balance += $sale->total;
                
                $date = \Carbon\Carbon::parse($sale->created_at)->format('d-m-Y');
                
                // Date (8 chars)
                $output .= substr($date, 0, 8) . " ";
                
                // Description
                $output .= $this->renderTextAsImage("ইনভয়েস #" . $sale->id, 8);
                
                // Amount (Right aligned)
                $output .= $esc . "a" . chr(2);  // Right align
                $output .= "+" . number_format($sale->total, 2) . "\n";
                $output .= $esc . "a" . chr(0);  // Left align
                
            } elseif ($transaction['type'] === 'payment') {
                $payment = $transaction['data'];
                $balance -= $payment->amount;
                
                $date = \Carbon\Carbon::parse($payment->paid_at)->format('d-m-Y');
                
                // Date
                $output .= substr($date, 0, 8) . " ";
                
                // Description
                $method = $payment->method ?? 'cash';
                $output .= $this->renderTextAsImage("পরিশোধ (" . $method . ")", 8);
                
                // Amount (Right aligned)
                $output .= $esc . "a" . chr(2);  // Right align
                $output .= "-" . number_format($payment->amount, 2) . "\n";
                $output .= $esc . "a" . chr(0);  // Left align
            }
        } 
        
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ৬. Summary ---
        // $output .= $this->printRow("সর্বমোট বিক্রয়:", number_format($data['total_sales'] ?? 0, 2), 32);
        // $output .= $this->printRow("সর্বমোট জমা:", number_format($data['total_payments'] ?? 0, 2), 32);
        $output .= $this->renderTextAsImage("সর্বমোট বিক্রয়: " . number_format($data['total_sales'] ?? 0, 2) . " Tk", 9);
        $output .= $this->renderTextAsImage("সর্বমোট জমা: " . number_format($data['total_payments'] ?? 0, 2) . " Tk", 9);
        
        $output .= str_repeat("=", 32) . "\n";
        
        // Current Due (বড় করে)
        $output .= $this->setTextSize(2, 1);
        $output .= $this->renderTextAsImage("বর্তমান বকেয়া:", 11);
        $output .= $esc . "a" . chr(2);  // Right align
        $output .= number_format($data['current_due'] ?? 0, 2) . " Tk\n";
        $output .= $esc . "a" . chr(0);  // Left align
        $output .= $this->setTextSize(1, 1);
        
        $output .= str_repeat("=", 32) . "\n";
        
        // --- ৭. Footer ---
        $output .= "\n";
        $output .= $esc . "a" . chr(1);  // Center
        $output .= $this->renderTextAsImage("কম্পিউটার-জেনারেটেড বিবরণী", 8);
        $output .= $this->renderTextAsImage("স্বাক্ষরের প্রয়োজন নেই", 8);
        
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
     * Row format করুন (48mm paper = 32 chars)
     */
    private function printRow($label, $value, $maxChars = 32)
    {
        $labelLen = mb_strlen($label, 'UTF-8');
        $valueLen = strlen($value);
        
        $spaces = $maxChars - ($labelLen + $valueLen);
        if ($spaces < 1) $spaces = 1;
        
        return $label . str_repeat(" ", $spaces) . $value . "\n";
    }
    
    /**
     * বাংলা text কে raster image এ convert করুন
     */
    private function renderTextAsImage($text, $fontSize = 10)
    {
        // যদি text এ বাংলা না থাকে, সরাসরি return করুন
        if (!$this->hasBangla($text)) {
            return $text . "\n";
        }
        
        try {
            // Pango দিয়ে image তৈরি করুন
            $width = 384; // 48mm = 384 pixels at 203 DPI
            $outputFile = tempnam(sys_get_temp_dir(), 'stmt_') . '.png';
            
            // UTF-8 normalize
            if (!mb_check_encoding($text, 'UTF-8')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            }
            
            if (class_exists('Normalizer')) {
                $text = \Normalizer::normalize($text, \Normalizer::FORM_C);
            }
            
            $escapedText = htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            
            $pangoMarkup = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<markup>
<span font_desc=\"Noto Sans Bengali {$fontSize}\">{$escapedText}</span>
</markup>";
            
            $markupFile = tempnam(sys_get_temp_dir(), 'mkp_') . '.txt';
            file_put_contents($markupFile, $pangoMarkup);
            
            // ImageMagick command
            $cmd = sprintf(
                'convert -density 203 -background white -fill black pango:@%s -trim +repage -gravity West -extent %dx PNG32:%s 2>&1',
                escapeshellarg($markupFile),
                $width,
                escapeshellarg($outputFile)
            );
            
            exec($cmd, $output, $return);
            @unlink($markupFile);
            
            if ($return === 0 && file_exists($outputFile) && filesize($outputFile) > 100) {
                $rasterData = $this->imageToRaster($outputFile, $width);
                @unlink($outputFile);
                return $rasterData;
            }
            
            @unlink($outputFile);
            
        } catch (\Exception $e) {
            Log::warning("Statement Image rendering failed: " . $e->getMessage());
        }
        
        // Fallback: plain text
        return $text . "\n";
    }
    
    /**
     * PNG image কে ESC/POS raster command এ convert করুন
     */
    private function imageToRaster($imagePath, $width = 384)
    {
        $im = @imagecreatefrompng($imagePath);
        if ($im === false) {
            return "";
        }
        
        $height = imagesy($im);
        $gs = chr(29);
        
        // Raster command header
        $output = $gs . "v0" . chr(0);
        $output .= chr(($width / 8) & 0xff) . chr((($width / 8) >> 8) & 0xff);
        $output .= chr($height & 0xff) . chr(($height >> 8) & 0xff);
        
        // Pixel data
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x += 8) {
                $byte = 0;
                for ($b = 0; $b < 8; $b++) {
                    $px = $x + $b;
                    if ($px < imagesx($im)) {
                        $color = imagecolorat($im, $px, $y);
                        $r = ($color >> 16) & 0xFF;
                        $g = ($color >> 8) & 0xFF;
                        $blue = $color & 0xFF;
                        
                        if ($r < 128 || $g < 128 || $blue < 128) {
                            $byte |= (0x80 >> $b);
                        }
                    }
                }
                $output .= chr($byte);
            }
        }
        
        imagedestroy($im);
        return $output;
    }
    
    /**
     * Text এ বাংলা আছে কিনা চেক করুন
     */
    private function hasBangla($text)
    {
        return preg_match('/[\x{0980}-\x{09FF}]/u', $text);
    }
}