<?php
class SimplePDF {
    private $objects = [];
    private $pages = [];
    private $currentPage = -1;
    private $fonts = [];
    private $links = [];
    private $fontFamily = 'Helvetica';
    private $fontStyle = '';
    private $fontSize = 12;
    private $x = 10;
    private $y = 10;
    private $pageWidth = 595.28; // A4
    private $pageHeight = 841.89;
    private $marginLeft = 20;
    private $marginRight = 20;
    private $marginTop = 20;
    private $marginBottom = 20;
    private $lineHeight = 5;

    public function __construct($format = 'A4') {
        if ($format === 'letter') {
            $this->pageWidth = 612;
            $this->pageHeight = 792;
        }
        $this->addPage();
    }

    public function addPage() {
        $this->pages[] = ['content' => '', 'links' => []];
        $this->currentPage = count($this->pages) - 1;
        $this->y = $this->marginTop;
        $this->x = $this->marginLeft;
    }

    public function setFont($family, $style = '', $size = 12) {
        $this->fontFamily = $family;
        $this->fontStyle = $style;
        $this->fontSize = $size;
        $this->lineHeight = $size * 0.3528 * 1.2;
    }

    public function write($w, $text, $link = null) {
        if ($this->y > $this->pageHeight - $this->marginBottom) {
            $this->addPage();
        }

        $size = $this->fontSize * 0.75;
        $page = &$this->pages[$this->currentPage];

        $page['content'] .= sprintf("BT /F1 %s Tf %s %s Td (%s) Tj ET\n",
            $this->fontStyle === 'B' ? $size . ' 0 0 ' . ($size * 1.2) . ' 0 0 Tm' : $size . ' Tf',
            number_format($this->x, 2),
            number_format($this->pageHeight - $this->y - $size, 2),
            $this->escapeText($text)
        );

        if ($link) {
            $page['links'][] = [
                'x' => $this->x,
                'y' => $this->y,
                'w' => $this->getStringWidth($text) * 0.75,
                'h' => $size * 1.4,
                'url' => $link
            ];
        }

        $this->x += $this->getStringWidth($text) * 0.75;
    }

    public function writeLine($w, $text, $link = null) {
        $this->x = $this->marginLeft;
        $this->write($w, $text, $link);
        $this->y += $this->lineHeight;
        $this->x = $this->marginLeft;
    }

    public function multiCell($w, $text, $link = null) {
        $words = explode(' ', $text);
        $line = '';
        $maxW = $w > 0 ? $w : $this->pageWidth - $this->marginLeft - $this->marginRight;
        
        foreach ($words as $word) {
            $test = $line ? $line . ' ' . $word : $word;
            if ($this->getStringWidth($test) * 0.75 > $maxW && $line) {
                $this->writeLine($maxW, $line);
                $line = $word;
            } else {
                $line = $test;
            }
        }
        if ($line) {
            $this->writeLine($maxW, $line);
        }
    }

    public function getStringWidth($text) {
        $w = 0;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $w += $this->charWidth($text[$i]);
        }
        return $w;
    }

    private function charWidth($c) {
        if ($c === ' ') return 2.5;
        if (ctype_upper($c)) return 2.8;
        if (ctype_lower($c)) return 2.2;
        if (ctype_digit($c)) return 2.8;
        return 2.5;
    }

    private function escapeText($text) {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        return $text;
    }

    public function output($filename = 'document.pdf') {
        $objId = 1;
        $offsets = [];
        
        $catalog = $objId++;
        $pagesObj = $objId++;
        $pageObjs = [];
        $fontObj = $objId++;
        $contentObjs = [];
        $annotObjs = [];

        // Page objects
        $pageRefs = [];
        foreach ($this->pages as $i => $page) {
            $contentObjs[$i] = $objId++;
            if ($page['links']) {
                $annotObjs[$i] = [];
                foreach ($page['links'] as $lnk) {
                    $annotObjs[$i][] = $objId++;
                }
            }
            $pageObjs[$i] = $objId++;
            $pageRefs[] = $pageObjs[$i];
        }

        // Build PDF
        $pdf = '';
        
        // Header
        $pdf .= "%PDF-1.4\n";
        
        // 1: Catalog
        $offsets[$catalog] = strlen($pdf);
        $pdf .= "$catalog 0 obj\n<< /Type /Catalog /Pages $pagesObj 0 R >>\nendobj\n";
        
        // 2: Pages
        $offsets[$pagesObj] = strlen($pdf);
        $kids = implode(' 0 R ', $pageRefs) . ' 0 R';
        $pdf .= "$pagesObj 0 obj\n<< /Type /Pages /Kids [$kids] /Count " . count($this->pages) . " >>\nendobj\n";
        
        // Font
        $offsets[$fontObj] = strlen($pdf);
        $baseFont = $this->fontFamily . ($this->fontStyle === 'B' ? '-Bold' : ($this->fontStyle === 'I' ? '-Oblique' : ''));
        $pdf .= "$fontObj 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /$baseFont >>\nendobj\n";
        
        // Content streams & pages
        foreach ($this->pages as $i => $page) {
            $contentStr = $page['content'];
            $offsets[$contentObjs[$i]] = strlen($pdf);
            $pdf .= $contentObjs[$i] . " 0 obj\n<< /Length " . strlen($contentStr) . " >>\nstream\n" . $contentStr . "\nendstream\nendobj\n";
            
            // Annotations
            $annots = [];
            if ($page['links']) {
                foreach ($page['links'] as $ji => $lnk) {
                    $offsets[$annotObjs[$i][$ji]] = strlen($pdf);
                    $rect = sprintf('[%s %s %s %s]',
                        number_format($lnk['x'], 2),
                        number_format($this->pageHeight - $lnk['y'] - $lnk['h'], 2),
                        number_format($lnk['x'] + $lnk['w'], 2),
                        number_format($this->pageHeight - $lnk['y'], 2)
                    );
                    $pdf .= $annotObjs[$i][$ji] . " 0 obj\n<< /Type /Annot /Subtype /Link /Rect $rect /A << /S /URI /URI (" . $this->escapeText($lnk['url']) . ") >> >>\nendobj\n";
                    $annots[] = $annotObjs[$i][$ji] . ' 0 R';
                }
            }
            
            // Page
            $offsets[$pageObjs[$i]] = strlen($pdf);
            $annotsStr = $annots ? ' /Annots [' . implode(' ', $annots) . ']' : '';
            $pdf .= $pageObjs[$i] . " 0 obj\n<< /Type /Page /Parent $pagesObj 0 R /MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] /Contents {$contentObjs[$i]} 0 R /Resources << /Font << /F1 $fontObj 0 R >> >>$annotsStr >>\nendobj\n";
        }
        
        // Cross-reference table
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . $objId . "\n0000000000 65535 f \n";
        for ($i = 1; $i < $objId; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        
        // Trailer
        $pdf .= "trailer\n<< /Size $objId /Root $catalog 0 R >>\nstartxref\n$xrefOffset\n%%EOF\n";
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }
}
