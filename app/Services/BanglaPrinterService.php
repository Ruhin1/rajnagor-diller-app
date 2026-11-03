<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Services\BanglaTextFixer;

class BanglaPrinterService
{
    private $printerIp = '192.168.0.200';
    private $printerPort = 9100;
    private $fontPath;
    
    public function __construct()
    {
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        // সবচেয়ে ভালো বাংলা ফন্ট খুঁজুন
        $fonts = [
            '/usr/share/fonts/google-noto/NotoSansBengali-Bold.ttf',
            '/usr/share/fonts/google-noto/NotoSansBengaliUI-Regular.ttf',
            '/usr/share/fonts/google-noto/NotoSansBengali-Regular.ttf',
            '/usr/share/fonts/google-noto/NotoSansBengaliUI-Bold.ttf',
            public_path('fonts/NotoSansBengali-Regular.ttf'),
            public_path('fonts/kalpurush.ttf'),
            public_path('fonts/SolaimanLipi.ttf'),
        ]; 
        
        foreach ($fonts as $font) {
            if (file_exists($font)) {
                $this->fontPath = $font;
                Log::info("Using font: " . $font);
                break;
            }
        }
    }

    public function printPosInvoice($data)
    {
        Log::info("Starting POS Print Job for INV-" . ($data['id'] ?? 'Unknown'));

        if (!$this->pingPrinter($this->printerIp)) {
            return [
                'success' => false,
                'message' => "প্রিন্টার অফলাইন! দয়া করে প্রিন্টার অন করুন অথবা আইপি ($this->printerIp) চেক করুন।"
            ];
        }

        try {
            $domain = url('/');
            $compAddress = "Chadnighat, Moulvibazar.";
            $compPhone = "Phone: 01756697683";
            
            if ($domain == 'https://app.gffoodandbeverage.com') {
                $compAddress = "Bhanugach Bazar, Kamalganj.";
                $compPhone = "Phone: 01344-558778";
            }

            Log::info("Connecting to Printer...");
            $connector = new NetworkPrintConnector($this->printerIp, $this->printerPort, 5);
            $printer = new Printer($connector);
            $printer->initialize();
            
            // --- ১. হেডার সেকশন ---
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printBanglaAsRaster($printer, "GF Food & Beverage Ltd.", 10);
            $this->printBanglaAsRaster($printer, $compAddress, 8);
            $this->printBanglaAsRaster($printer, $compPhone, 8);
            $printer->text("----------------------------------------------\n");
            
            // --- ২. ইনভয়েস মেটা ডাটা
            $this->printBanglaAsRaster($printer, "Invoice No: #INV-" . ($data['id'] ?? ''), 8);
            if (!empty($data['srname'])) {$this->printBanglaAsRaster($printer, "SR Name: " . $data['srname'], 8);}
            $this->printBanglaAsRaster($printer, "Order Date: " . ($data['date'] ?? date('d M, Y')), 8);
            if (!empty($data['ddate'])) {$this->printBanglaAsRaster($printer, "Delivery Date: " . $data['ddate'], 8);}
            $printer->text("----------------------------------------------\n");
 
            // --- ৩. ক্লায়েন্ট ইনফো ---
            $this->printBanglaAsRaster($printer, $data['client_type'], 8);
            if (!empty($data['customer_name'])) {$this->printBanglaAsRaster($printer, 'Owner: ' . $data['customer_name'], 10);}
            if (!empty($data['shop_name'])){$this->printBanglaAsRaster($printer, ('Shop: ' . $data['shop_name']), 8);}
            if (!empty($data['address'])) {$this->printBanglaAsRaster($printer, 'Address: ' . $data['address'], 8);}
            if (!empty($data['mobile'])) {$this->printBanglaAsRaster($printer, "Mobile: " . $data['mobile'], 8);}
            $printer->text("----------------------------------------------\n");

            // --- ৪. আইটেম লিস্ট ---
            foreach (($data['items'] ?? []) as $item) {
                $productName = $item->product->name ?? 'Item';
                $this->printBanglaAsRaster($printer, $productName, 8);
                
                $qty = $item->quantity ?? 0;
                $price = number_format($item->price ?? 0, 2);
                $lineTotal = number_format($item->total ?? 0, 2);
                
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text("$qty x $price = $lineTotal\n");
                $printer->setJustification(Printer::JUSTIFY_LEFT);
            }
            $printer->text("----------------------------------------------\n");

            
            // --- ৫. ক্যালকুলেশন ---
            $this->printRow($printer, "Subtotal:", number_format($data['subtotal'] ?? 0, 2));
            if (($data['discount'] ?? 0) > 0) {$this->printRow($printer, "Commission (-):", number_format($data['discount'], 2));}
            if (($data['shipping'] ?? 0) > 0) {$this->printRow($printer, "Other Cost (+):", number_format($data['shipping'], 2));}
            $printer->feed(1);
            $this->printRow($printer, "Paid Amount:", number_format($data['paid'] ?? 0, 2));
            $this->printRow($printer, "Current Amount:", number_format($data['due'] ?? 0, 2));
            

            $grandTotalDue = $data['total_due'] ?? 0;
            $currentDue = $data['due'] ?? 0; 
            if ($grandTotalDue > $currentDue) {
            $printer->text("----------------------------------------------\n");
            
            // --- ৬. Previous Due Logic ---
            if ($grandTotalDue > $currentDue) {
                $prevDue = $grandTotalDue - $currentDue;
                $prevCount = $data['prev_due_count'] ?? 0; 
                
                if ($prevDue > 0) {
                    $this->printRow($printer, "Prev. Due ($prevCount Inv):", number_format($prevDue, 2));
                    $this->printRow($printer, "Total Due:", number_format($grandTotalDue, 2));

                }
            }   
            $printer->text("----------------------------------------------\n");
            }
            // --- ৭. ফুটার এবং QR Code ---
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printBanglaAsRaster($printer, "আমাদের সাথে থাকার জন্য ধন্যবাদ!", 8);
            $printer->feed(2);
            $printer->cut();
            $printer->close();
            
            Log::info("Print Job Completed Successfully.");
            return ['success' => true];
            
        } catch (Exception $e) {
            Log::error('Print Failed: ' . $e->getMessage());
            return ['success' => false, 'message' => "প্রিন্টিং এরর: " . $e->getMessage()];
        }
    }

    private function printRow($printer, $label, $value)
    {
        $maxChars = 42;
        $labelLen = strlen($label);
        $valueLen = strlen($value);
        
        $spaces = $maxChars - ($labelLen + $valueLen);
        if ($spaces < 1) $spaces = 1;
        
        $printer->text($label . str_repeat(" ", $spaces) . $value . "\n");
    }

    private function pingPrinter($ip)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "ping -n 1 -w 1000 $ip";
        } else {
            $cmd = "ping -c 1 -W 1 $ip";
        }
        
        exec($cmd, $output, $status);
        return $status === 0;
    }

    /**
     * ✅ সম্পূর্ণ নতুন এবং নিখুঁত বাংলা রেন্ডারিং
     */
    private function printBanglaAsRaster($printer, $text, $fontSize = 20)
    {
        if (empty($text)) return;

        $width = 512;
        
        // পদ্ধতি ১: Pango CLI (100% নিখুঁত)
        $result = $this->renderWithPango($text, $fontSize, $width);
        
        if ($result === false) {
            // পদ্ধতি ২: Python + Pillow fallback
            $result = $this->renderWithPython($text, $fontSize, $width);
        }
        
        if ($result === false) {
            // পদ্ধতি ৩: Simple GD (last resort)
            $result = $this->renderSimpleGD($text, $fontSize, $width);
        }
        
        if ($result !== false) {
            list($im, $height) = $result;
            $this->sendRasterData($printer, $im, $width, $height);
            imagedestroy($im);
        }
    }

    /**
     * Pango + ImageMagick CLI (সবচেয়ে ভালো)
     */
    private function renderWithPango($text, $fontSize, $width)
    {
        // UTF-8 normalize
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        if (class_exists('Normalizer')) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_C);
        }
        
        $outputFile = tempnam(sys_get_temp_dir(), 'bnp_') . '.png';
        
        // সরাসরি Pango markup
        $pangoSize = (int)($fontSize * 1024); // Pango units
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
        
        Log::info("Pango Command: " . $cmd);
        
        $output = [];
        $return = 0;
        exec($cmd, $output, $return);
        
        @unlink($markupFile);
        
        if ($return === 0 && file_exists($outputFile) && filesize($outputFile) > 100) {
            $im = @imagecreatefrompng($outputFile);
            
            if ($im !== false) {
                $height = imagesy($im);
                $currentWidth = imagesx($im);
                
                // Width adjust
                if ($currentWidth != $width) {
                    $newIm = imagecreatetruecolor($width, $height);
                    $white = imagecolorallocate($newIm, 255, 255, 255);
                    imagefill($newIm, 0, 0, $white);
                    imagecopy($newIm, $im, 0, 0, 0, 0, min($currentWidth, $width), $height);
                    imagedestroy($im);
                    $im = $newIm;
                }
                
                @unlink($outputFile);
                return [$im, $height];
            }
        }
        
        @unlink($outputFile);
        Log::warning("Pango rendering failed: " . implode("\n", $output));
        
        return false;
    }

    /**
     * Python + Pillow দিয়ে render (খুব ভালো বাংলা সাপোর্ট)
     */
    private function renderWithPython($text, $fontSize, $width)
    {
        // Python আছে কিনা চেক
        exec('which python3 2>&1', $pythonCheck, $returnCode);
        if ($returnCode !== 0) {
            return false;
        }
        
        $outputFile = tempnam(sys_get_temp_dir(), 'pyimg_') . '.png';
        
        // Python script
        $pythonScript = <<<PYTHON
# -*- coding: utf-8 -*-
import sys
from PIL import Image, ImageDraw, ImageFont

text = """{$text}"""
font_path = "{$this->fontPath}"
font_size = {$fontSize}
width = {$width}
output = "{$outputFile}"

try:
    # Font load
    font = ImageFont.truetype(font_path, font_size)
    
    # Text size calculate
    dummy_img = Image.new('RGB', (1, 1), 'white')
    dummy_draw = ImageDraw.Draw(dummy_img)
    bbox = dummy_draw.textbbox((0, 0), text, font=font)
    text_width = bbox[2] - bbox[0]
    text_height = bbox[3] - bbox[1]
    
    # Image create
    height = text_height + 30
    img = Image.new('RGB', (width, height), 'white')
    draw = ImageDraw.Draw(img)
    
    # Draw text
    draw.text((10, 10), text, font=font, fill='black')
    
    # Save
    img.save(output, 'PNG')
    print("SUCCESS")
    
except Exception as e:
    print(f"ERROR: {e}")
    sys.exit(1)
PYTHON;

        $scriptFile = tempnam(sys_get_temp_dir(), 'pys_') . '.py';
        file_put_contents($scriptFile, $pythonScript);
        
        $cmd = "python3 " . escapeshellarg($scriptFile) . " 2>&1";
        exec($cmd, $output, $return);
        
        @unlink($scriptFile);
        
        if ($return === 0 && file_exists($outputFile) && filesize($outputFile) > 100) {
            $im = @imagecreatefrompng($outputFile);
            
            if ($im !== false) {
                $height = imagesy($im);
                @unlink($outputFile);
                return [$im, $height];
            }
        }
        
        @unlink($outputFile);
        return false;
    }

    /**
     * Simple GD rendering (basic fallback)
     */
    private function renderSimpleGD($text, $fontSize, $width)
    {
        // Basic text cleanup
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        if (class_exists('Normalizer')) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_C);
        }
        
        // BanglaTextFixer ব্যবহার করুন
        if (class_exists(BanglaTextFixer::class)) {
            $text = BanglaTextFixer::shape($text);
        }
        
        $padding = 10;
        $bbox = @imagettfbbox($fontSize, 0, $this->fontPath, $text);
        
        if ($bbox === false) {
            // Font loading failed
            $height = 50;
        } else {
            $textHeight = abs($bbox[5] - $bbox[1]);
            $height = max(50, $textHeight + 30);
        }
        
        $im = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        
        imagefilledrectangle($im, 0, 0, $width, $height, $white);
        
        if ($bbox !== false) {
            $y = abs($bbox[5]) + 10;
            @imagettftext($im, $fontSize, 0, $padding, $y, $black, $this->fontPath, $text);
        }
        
        return [$im, $height];
    }

    /**
     * প্রিন্টারে রাস্টার ডাটা পাঠান
     */
    private function sendRasterData($printer, $im, $width, $height)
    {
        $connector = $printer->getPrintConnector();
        
        // ESC/POS রাস্টার কমান্ড
        $connector->write("\x1d\x76\x30\x00");
        $connector->write(chr(($width / 8) & 0xff) . chr((($width / 8) >> 8) & 0xff));
        $connector->write(chr($height & 0xff) . chr(($height >> 8) & 0xff));
        
        // পিক্সেল ডাটা
        $data = '';
        $actualWidth = imagesx($im);
        $actualHeight = imagesy($im);
        
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x += 8) {
                $byte = 0;
                for ($b = 0; $b < 8; $b++) {
                    $px = $x + $b;
                    
                    if ($px < $actualWidth && $y < $actualHeight) {
                        $color = imagecolorat($im, $px, $y);
                        $r = ($color >> 16) & 0xFF;
                        $g = ($color >> 8) & 0xFF;
                        $blue = $color & 0xFF;
                        
                        if ($r < 128 || $g < 128 || $blue < 128) {
                            $byte |= (0x80 >> $b);
                        }
                    }
                }
                $data .= chr($byte);
            }
        }
        
        $connector->write($data);
    }
}