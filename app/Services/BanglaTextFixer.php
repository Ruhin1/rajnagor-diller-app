<?php

namespace App\Services;

class BanglaTextFixer
{
    private const ZWJ = "\u{200D}";   // Zero Width Joiner
    private const ZWNJ = "\u{200C}";  // Zero Width Non-Joiner
    private const HASANT = "\u{09CD}"; // ্
    
    /**
     * বাংলা টেক্সট সম্পূর্ণ ফিক্স করুন
     */
    public static function shape($text)
    {
        if (empty($text)) {
            return "";
        }

        // UTF-8 Validation
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }

        // Unicode Normalization (NFC Form)
        if (class_exists('Normalizer')) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_C);
        }

        // ১. যুক্তবর্ণে ZWJ যোগ করুন
        $text = self::addZWJToConjuncts($text);

        // ২. বিশেষ যুক্তবর্ণ হ্যান্ডল করুন
        $text = self::handleSpecialConjuncts($text);

        // ৩. Pre-base vowels reorder করুন
        $text = self::reorderPrebaseVowels($text);

        // ৪. Compound vowels decompose করুন
        $text = self::decomposeCompoundVowels($text);

        // ৫. Reph (র্) হ্যান্ডল করুন
        $text = self::handleReph($text);

        // ৬. ফলা (য-ফলা, র-ফলা, ব-ফলা) হ্যান্ডল করুন
        $text = self::handleFola($text);

        return $text;
    }

    /**
     * সকল যুক্তবর্ণে ZWJ যোগ করুন
     */
    private static function addZWJToConjuncts($text)
    {
        // Pattern: consonant + hasant + consonant
        // এর মাঝে ZWJ যোগ করুন যাতে proper conjunct form render হয়
        $pattern = '/([ক-হড়ঢ়য়ৎ])্(?!' . self::ZWJ . ')([ক-হড়ঢ়য়])/u';
        $text = preg_replace($pattern, '$1্' . self::ZWJ . '$2', $text);
        
        return $text;
    }

    /**
     * বিশেষ যুক্তবর্ণ হ্যান্ডল করুন
     */
    private static function handleSpecialConjuncts($text)
    {
        // বিশেষ যুক্তবর্ণের তালিকা যেগুলোর special rendering দরকার
        $specialMappings = [
            // ক্ষ = ক + ্ + ষ
            'ক্ষ' => 'ক্' . self::ZWJ . 'ষ',
            
            // জ্ঞ = জ + ্ + ঞ
            'জ্ঞ' => 'জ্' . self::ZWJ . 'ঞ',
            
            // ঙ্ক, ঙ্গ ইত্যাদি
            'ঙ্ক' => 'ঙ্' . self::ZWJ . 'ক',
            'ঙ্গ' => 'ঙ্' . self::ZWJ . 'গ',
            'ঙ্ঘ' => 'ঙ্' . self::ZWJ . 'ঘ',
            'ঙ্ম' => 'ঙ্' . self::ZWJ . 'ম',
            
            // ঞ্চ, ঞ্জ
            'ঞ্চ' => 'ঞ্' . self::ZWJ . 'চ',
            'ঞ্জ' => 'ঞ্' . self::ZWJ . 'জ',
            
            // ণ্ট, ণ্ঠ, ণ্ড, ণ্ঢ
            'ণ্ট' => 'ণ্' . self::ZWJ . 'ট',
            'ণ্ঠ' => 'ণ্' . self::ZWJ . 'ঠ',
            'ণ্ড' => 'ণ্' . self::ZWJ . 'ড',
            'ণ্ঢ' => 'ণ্' . self::ZWJ . 'ঢ',
            'ণ্ণ' => 'ণ্' . self::ZWJ . 'ণ',
            
            // ন্ট, ন্ঠ, ন্ড, ন্দ, ন্ধ
            'ন্ট' => 'ন্' . self::ZWJ . 'ট',
            'ন্ঠ' => 'ন্' . self::ZWJ . 'ঠ',
            'ন্ড' => 'ন্' . self::ZWJ . 'ড',
            'ন্ত' => 'ন্' . self::ZWJ . 'ত',
            'ন্থ' => 'ন্' . self::ZWJ . 'থ',
            'ন্দ' => 'ন্' . self::ZWJ . 'দ',
            'ন্ধ' => 'ন্' . self::ZWJ . 'ধ',
            'ন্ন' => 'ন্' . self::ZWJ . 'ন',
            'ন্ব' => 'ন্' . self::ZWJ . 'ব',
            'ন্ম' => 'ন্' . self::ZWJ . 'ম',
        ];

        foreach ($specialMappings as $original => $replacement) {
            // শুধুমাত্র replace করুন যদি ইতিমধ্যে ZWJ না থাকে
            if (mb_strpos($original, self::ZWJ) === false && mb_strpos($text, $original) !== false) {
                $text = str_replace($original, $replacement, $text);
            }
        }

        return $text;
    }

    /**
     * Pre-base vowels (ি, ে, ৈ) reorder করুন
     */
    private static function reorderPrebaseVowels($text)
    {
        $zwj = self::ZWJ;
        
        // Consonant cluster pattern with ZWJ
        $consonant = '[ক-হড়ঢ়য়ৎ]';
        $nukta = '়?';
        $hasant = '্';
        $preVowel = '[িেৈ]';
        
        // Single consonant cluster: C্C + vowel -> vowel + C্C
        $pattern = "/({$consonant}{$nukta}{$hasant}{$zwj}?{$consonant}{$nukta})({$preVowel})/u";
        $text = preg_replace($pattern, '$2$1', $text);
        
        // Multiple consonant cluster: C্C্C + vowel -> vowel + C্C্C
        $pattern = "/({$consonant}{$nukta}(?:{$hasant}{$zwj}?{$consonant}{$nukta}){2,})({$preVowel})/u";
        $text = preg_replace($pattern, '$2$1', $text);
        
        // Reph সহ: র্C + vowel -> vowel + র্C
        $pattern = "/(র{$hasant}{$zwj}?{$consonant}{$nukta})({$preVowel})/u";
        $text = preg_replace($pattern, '$2$1', $text);
        
        return $text;
    }

    /**
     * Compound vowels (ো, ৌ) decompose করুন
     */
    private static function decomposeCompoundVowels($text)
    {
        $zwj = self::ZWJ;
        
        // ো (U+09CB) = ে (U+09C7) + া (U+09BE)
        $text = str_replace('ো', 'ে' . $zwj . 'া', $text);
        
        // ৌ (U+09CC) = ে (U+09C7) + ৗ (U+09D7)
        $text = str_replace('ৌ', 'ে' . $zwj . 'ৗ', $text);

        return $text;
    }

    /**
     * Reph (র্) সঠিকভাবে হ্যান্ডল করুন
     */
    private static function handleReph($text)
    {
        $zwj = self::ZWJ;
        
        // র্ + consonant pattern
        // র্ক, র্গ, র্ত ইত্যাদি
        $pattern = '/র্(?!' . $zwj . ')([ক-হড়ঢ়য়])/u';
        $text = preg_replace($pattern, 'র্' . $zwj . '$1', $text);
        
        return $text;
    }

    /**
     * ফলা (য-ফলা, র-ফলা, ব-ফলা, ম-ফলা) হ্যান্ডল করুন
     */
    private static function handleFola($text)
    {
        $zwj = self::ZWJ;
        
        // য-ফলা: C্য
        $pattern = '/([ক-হড়ঢ়য়])্(?!' . $zwj . ')য/u';
        $text = preg_replace($pattern, '$1্' . $zwj . 'য', $text);
        
        // র-ফলা (রফলা): C্র
        $pattern = '/([ক-হড়ঢ়য়])্(?!' . $zwj . ')র/u';
        $text = preg_replace($pattern, '$1্' . $zwj . 'র', $text);
        
        // ব-ফলা: C্ব
        $pattern = '/([ক-হড়ঢ়য়])্(?!' . $zwj . ')ব/u';
        $text = preg_replace($pattern, '$1্' . $zwj . 'ব', $text);
        
        // ম-ফলা: C্ম
        $pattern = '/([ক-হড়ঢ়য়])্(?!' . $zwj . ')ম/u';
        $text = preg_replace($pattern, '$1্' . $zwj . 'ম', $text);
        
        return $text;
    }

    /**
     * Debug: টেক্সট এর Unicode codepoints দেখুন
     */
    public static function debugUnicode($text)
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $debug = [];
        
        foreach ($chars as $char) {
            $codepoint = mb_ord($char, 'UTF-8');
            $hex = sprintf('U+%04X', $codepoint);
            $debug[] = [
                'char' => $char,
                'hex' => $hex,
                'dec' => $codepoint,
                'name' => self::getCharName($codepoint)
            ];
        }
        
        return $debug;
    }

    /**
     * Unicode character এর নাম পান
     */
    private static function getCharName($codepoint)
    {
        $names = [
            0x200D => 'ZERO WIDTH JOINER',
            0x200C => 'ZERO WIDTH NON-JOINER',
            0x09CD => 'BENGALI SIGN VIRAMA (HASANT)',
            0x09BF => 'BENGALI VOWEL SIGN I',
            0x09C7 => 'BENGALI VOWEL SIGN E',
            0x09C8 => 'BENGALI VOWEL SIGN AI',
            0x09BE => 'BENGALI VOWEL SIGN AA',
            0x09CB => 'BENGALI VOWEL SIGN O',
            0x09CC => 'BENGALI VOWEL SIGN AU',
        ];
        
        return $names[$codepoint] ?? 'BENGALI CHARACTER';
    }
} 