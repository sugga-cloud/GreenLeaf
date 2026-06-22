<?php

class PDFParser {
    private static $cmaps = [];

    public static function extractText($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new Exception("Cannot read file: $filePath");
        }

        $streams = self::extractDecompressedStreams($content);

        // Parse CMap tables
        self::$cmaps = [];
        foreach ($streams as $stream) {
            self::parseCMap($stream);
        }

        $text = '';

        // Extract text from content streams
        foreach ($streams as $stream) {
            if (preg_match_all('/BT\s*(.*?)\s*ET/s', $stream, $btBlocks)) {
                foreach ($btBlocks[1] as $block) {
                    $text .= self::extractFromBTBlock($block);
                }
            }
        }

        // Fallback
        if (strlen(trim($text)) < 20) {
            foreach ($streams as $stream) {
                $text .= self::extractReadableStrings($stream);
            }
        }

        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (strlen($text) < 10) {
            throw new Exception("Could not extract meaningful text. The PDF may be image-based.");
        }

        return $text;
    }

    private static function extractDecompressedStreams($content) {
        $streams = [];
        preg_match_all('/stream\r?\n(.*?)endstream/s', $content, $matches);
        foreach ($matches[1] as $rawStream) {
            $dec = @gzuncompress($rawStream);
            if ($dec !== false) { $streams[] = $dec; continue; }
            $dec = @gzinflate($rawStream);
            if ($dec !== false) { $streams[] = $dec; }
        }
        return $streams;
    }

    private static function parseCMap($stream) {
        // Parse bfchar: each line has <src> <dst>
        if (preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', $stream, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                // Skip codespace ranges (<0000> <FFFF>)
                if ($matches[2][$i] === 'FFFF' && hexdec($matches[1][$i]) === 0) continue;
                
                $src = hexdec($matches[1][$i]);
                $dst = hexdec($matches[2][$i]);
                
                // Only map if dst looks like a valid Unicode codepoint (printable range)
                if ($dst >= 0x20 && $dst <= 0x10FFFF) {
                    self::$cmaps[$src] = $dst;
                }
            }
        }
    }

    private static function extractFromBTBlock($block) {
        $text = '';

        // Handle hex TJ arrays: [<hex><hex>...] TJ
        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $arrays)) {
            foreach ($arrays[1] as $arr) {
                if (preg_match_all('/<([0-9A-Fa-f]+)>/', $arr, $hexStrs)) {
                    foreach ($hexStrs[1] as $hex) {
                        $text .= self::decodeCIDString($hex);
                    }
                }
                if (preg_match_all('/\(([^)]*)\)/', $arr, $regStrs)) {
                    foreach ($regStrs[1] as $s) {
                        $text .= self::decodeString($s);
                    }
                }
                $text .= ' ';
            }
        }

        // Handle Tj: (text) Tj
        if (preg_match_all('/\(([^)]*)\)\s*Tj/', $block, $strs)) {
            foreach ($strs[1] as $s) {
                $text .= self::decodeString($s) . ' ';
            }
        }

        return $text;
    }

    private static function decodeCIDString($hex) {
        $text = '';
        $len = strlen($hex);
        if ($len % 4 == 0 && $len >= 4) {
            for ($i = 0; $i < $len; $i += 4) {
                $code = hexdec(substr($hex, $i, 4));
                if (isset(self::$cmaps[$code])) {
                    $text .= mb_chr(self::$cmaps[$code], 'UTF-8') ?: '?';
                } else {
                    $text .= mb_chr($code, 'UTF-8') ?: '?';
                }
            }
        } else {
            $bin = @hex2bin($hex);
            if ($bin) $text = $bin;
        }
        return $text;
    }

    private static function extractReadableStrings($stream) {
        $text = '';
        if (preg_match_all('/\(([^\(\)]{3,})\)/', $stream, $matches)) {
            foreach ($matches[1] as $s) {
                $decoded = self::decodeString($s);
                if (!preg_match('/^(obj|endobj|stream|endstream|xref|trailer|startxref|%%EOF|PDF|Type|Font|Page|Catalog|Kids|Count|MediaBox|Resources|BaseFont|Subtype|Length|Filter|FlateDecode|Width|Height|BitsPerComponent|ColorSpace|DecodeParms|CIDInit|ProcSet|begincmap|endcodespacerange|beginbfchar|endbfchar|beginbfrange|endbfrange|CMapName|CMapType|CIDSystemInfo|Registry|Ordering|Supplement|codespacerange|bfchar|bfrange|Identity)$/i', $decoded)) {
                    $text .= $decoded . ' ';
                }
            }
        }
        return $text;
    }

    private static function decodeString($str) {
        $str = str_replace('\\n', "\n", $str);
        $str = str_replace('\\r', "\r", $str);
        $str = str_replace('\\t', "\t", $str);
        $str = str_replace('\\\\', '\\', $str);
        $str = str_replace('\\(', '(', $str);
        $str = str_replace('\\)', ')', $str);
        $str = str_replace('\\/', '/', $str);
        return $str;
    }
}
