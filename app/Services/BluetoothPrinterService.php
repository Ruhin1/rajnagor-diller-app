<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BluetoothPrinterService
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
     * Bluetooth প্রিন্টিং এর জন্য ESC/POS commands generate করুন
     */
    public function generateBluetoothPrint($data)
    {
        try {
            Log::info("Generating Bluetooth Print for INV-" . ($data['id'] ?? 'Unknown'));
            
            // ESC/POS commands generate করুন
            $escpos = $this->buildESCPOSCommands($data);
            
            // Base64 encode (RawBT এর জন্য)
            $base64Data = base64_encode($escpos);
            
            // RawBT Intent URL
            $intentUrl = "rawbt:base64," . $base64Data;
            
            return [
                'success' => true,
                'intent_url' => $intentUrl,
                'invoice_id' => $data['id']
            ];
            
        } catch (\Exception $e) {
            Log::error('Bluetooth Print Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * ESC/POS commands তৈরি করুন (48mm paper width)
     */
    private function buildESCPOSCommands($data)
    {
        $esc = chr(27);  // ESC
        $gs = chr(29);   // GS
        
        $output = '';
        
        // Initialize printer
        $output .= $esc . "@";
        
        // Domain check (আপনার existing logic)

        $compAddress = "ইউপি রোড রাজনগর";
        $compPhone = "Phone: +880 1709090957";
        
        
        
        // --- ১. হেডার ---
        $output .= $esc . "a" . chr(0);  // Left align
        $output .= $this->setTextSize(1, 1);
        
        // কোম্পানির নাম (বাংলা - image হিসেবে)
        $output .= $this->renderTextAsImage("বদরুল এন্টারপ্রাইজ ", 14);
        $output .= $this->renderTextAsImage($compAddress, 10);
        $output .= $compPhone . "\n";
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ২. Invoice Info ---
        $output .= "Invoice: #INV-" . ($data['id'] ?? '') . "\n";
        
        if (!empty($data['srname'])) {
            $output .= $this->renderTextAsImage("SR: " . $data['srname'], 10);
        }
        
        $output .= "Date: " . ($data['date'] ?? date('d-m-Y')) . "\n";
        
        if (!empty($data['ddate'])) {
            $output .= "Delivery: " . $data['ddate'] . "\n";
        }
        
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ৩. Client Info ---
        $output .= $this->renderTextAsImage($data['client_type'] ?? 'Invoice', 10);
        
        if (!empty($data['customer_name'])) {
            $output .= $this->renderTextAsImage("Owner: " . $data['customer_name'], 11);
        }
        
        if (!empty($data['shop_name'])) {
            $output .= $this->renderTextAsImage("Shop: " . $data['shop_name'], 10);
        }
        
        if (!empty($data['address'])) {
            $output .= $this->renderTextAsImage("Addr: " . $data['address'], 9);
        }
        
        if (!empty($data['mobile'])) {
            $output .= "Mobile: " . $data['mobile'] . "\n";
        }
        
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ৪. Items ---
        foreach ($data['items'] as $item) {
            $productName = $item->product->name ?? 'Item';
            
            // Product name (বাংলা image)
            $output .= $this->renderTextAsImage($productName, 10);
            
            // Quantity and price
            $qty = $item->quantity ?? 0;
            $price = number_format($item->price ?? 0, 2);
            $total = number_format($item->total ?? 0, 2);
            
            $output .= $esc . "a" . chr(2);  // Right align
            $output .= "{$qty} x {$price} = {$total}\n";
            $output .= $esc . "a" . chr(0);  // Left align
        }
        
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ৫. Totals ---
        $output .= $this->printRow("Subtotal:", number_format($data['subtotal'], 2), 32);
        
        if (($data['discount'] ?? 0) > 0) {
            $output .= $this->printRow("Discount(-):", number_format($data['discount'], 2), 32);
        }
        
        if (($data['shipping'] ?? 0) > 0) {
            $output .= $this->printRow("Other(+):", number_format($data['shipping'], 2), 32);
        }
        
        $output .= "\n";
        $output .= $this->setTextSize(2, 1);
        $output .= $this->printRow("TOTAL:", number_format($data['total'], 2), 24);
        $output .= $this->setTextSize(1, 1);
        
        $output .= $this->printRow("Paid:", number_format($data['paid'], 2), 32);
        $output .= $this->printRow("Current:", number_format($data['due'], 2), 32);
        
        // --- ৬. Previous Due ---
        $grandTotalDue = $data['total_due'] ?? 0;
        $currentDue = $data['due'] ?? 0;
        
        if ($grandTotalDue > $currentDue) {
            $prevDue = $grandTotalDue - $currentDue;
            $prevCount = $data['prev_due_count'] ?? 0;
            
            if ($prevDue > 0) {
                $output .= str_repeat("-", 32) . "\n";
                $output .= $this->printRow("Prev({$prevCount}):", number_format($prevDue, 2), 32);
                
                $output .= $this->setTextSize(2, 1);
                $output .= $this->printRow("Total Due:", number_format($grandTotalDue, 2), 24);
                $output .= $this->setTextSize(1, 1);
            }
        }
        
        $output .= str_repeat("-", 32) . "\n";
        
        // --- ৭. Footer ---
        $output .= "\n";
        $output .= $esc . "a" . chr(1);  // Center
        $output .= $this->renderTextAsImage("আমাদের সাথে থাকার জন্য ধন্যবাদ!", 10);
        
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
        $labelLen = strlen($label);
        $valueLen = strlen($value);
        
        $spaces = $maxChars - ($labelLen + $valueLen);
        if ($spaces < 1) $spaces = 1;
        
        return $label . str_repeat(" ", $spaces) . $value . "\n";
    }
    
    /**
     * বাংলা text কে raster image এ convert করুন
     * Pango/ImageMagick ব্যবহার করে
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
            $outputFile = tempnam(sys_get_temp_dir(), 'bt_') . '.png';
            
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
                // PNG কে ESC/POS raster এ convert করুন
                $rasterData = $this->imageToRaster($outputFile, $width);
                @unlink($outputFile);
                
                return $rasterData;
            }
            
            @unlink($outputFile);
            
        } catch (\Exception $e) {
            Log::warning("Image rendering failed: " . $e->getMessage());
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