<?php



class SimplePDF {
    private $content = '';
    private $y = 750; 
    
    public function addPage() {
        $this->y = 750;
        return $this;
    }
    
    public function addHeader($text) {
        
        $this->content .= "BT\n";
        $this->content .= "/F1 36 Tf\n";
        $this->content .= "0 0 0 rg\n";
        $this->content .= "50 " . ($this->y + 10) . " Td\n";
        $this->content .= "(BILETLY) Tj\n";
        $this->content .= "ET\n";
        
        
        $this->content .= "BT\n";
        $this->content .= "/F1 14 Tf\n";
        $this->content .= "0.2 0.2 0.2 rg\n";
        $this->content .= "50 " . ($this->y - 10) . " Td\n";
        $this->content .= $this->encodeText("MODERN OTOBUS BILETI SISTEMI") . " Tj\n";
        $this->content .= "ET\n";
        
        
        $this->content .= "0 0 0 RG\n";
        $this->content .= "1 w\n";
        $this->content .= "50 " . ($this->y - 25) . " m 562 " . ($this->y - 25) . " l S\n";
        
        $this->y -= 50;
        return $this;
    }
    
    public function addPNRBox($pnr) {
        
        $this->content .= "BT\n";
        $this->content .= "/F1 14 Tf\n";
        $this->content .= "0 0 0 rg\n";
        $this->content .= "480 " . ($this->y + 10) . " Td\n";
        $this->content .= "(PNR: $pnr) Tj\n";
        $this->content .= "ET\n";
        
        $this->y -= 30;
        return $this;
    }
    
    public function addSectionBox($title) {
        
        $this->content .= "BT\n";
        $this->content .= "/F1 16 Tf\n";
        $this->content .= "0 0 0 rg\n";
        $this->content .= "50 " . ($this->y + 5) . " Td\n";
        $this->content .= $this->encodeText($title) . " Tj\n";
        $this->content .= "ET\n";
        
        $this->y -= 25;
        return $this;
    }
    
    public function addInfoLine($label, $value, $rightColumn = false) {
        $xPos = $rightColumn ? 320 : 50;
        
        
        $this->content .= "BT\n";
        $this->content .= "/F1 14 Tf\n";
        $this->content .= "0.3 0.3 0.3 rg\n";
        $this->content .= "$xPos {$this->y} Td\n";
        $this->content .= $this->encodeText($label) . " Tj\n";
        $this->content .= "ET\n";
        
        
        $valueY = $this->y - 18;
        $this->content .= "BT\n";
        $this->content .= "/F1 14 Tf\n";
        $this->content .= "0 0 0 rg\n";
        $this->content .= "$xPos $valueY Td\n";
        $this->content .= $this->encodeText($value) . " Tj\n";
        $this->content .= "ET\n";
        
        if (!$rightColumn) {
            
        } else {
            $this->y -= 40;
        }
        
        return $this;
    }
    
    public function addDivider() {
        
        $this->content .= "0 0 0 RG\n";
        $this->content .= "1 w\n";
        $this->content .= "50 {$this->y} m 562 {$this->y} l S\n";
        $this->y -= 15;
        return $this;
    }
    
    public function addSpace($height = 20) {
        $this->y -= $height;
        return $this;
    }
    
    public function addFooterBox($lines) {
        
        foreach ($lines as $line) {
            $this->content .= "BT\n";
            $this->content .= "/F1 14 Tf\n";
            $this->content .= "0.3 0.3 0.3 rg\n";
            $this->content .= "50 {$this->y} Td\n";
            $this->content .= $this->encodeText($line) . " Tj\n";
            $this->content .= "ET\n";
            $this->y -= 18;
        }
        
        return $this;
    }
    
    private function encodeText($text) {
        
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        
        
        
        $turkishChars = [
            'Ç' => chr(199),  
            'ç' => chr(231),  
            'Ğ' => chr(208),  
            'ğ' => chr(240),  
            'İ' => chr(221),  
            'ı' => chr(253),  
            'Ö' => chr(214),  
            'ö' => chr(246),  
            'Ş' => chr(222),  
            'ş' => chr(254),  
            'Ü' => chr(220),  
            'ü' => chr(252)   
        ];
        
        
        $text = str_replace(array_keys($turkishChars), array_values($turkishChars), $text);
        
        return '(' . $text . ')';
    }
    
    private function cleanText($text) {
        
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        
        return $text;
    }
    
    public function output() {
        $contentLength = strlen($this->content);
        
        
        $pdf = "%PDF-1.4\n";
        
        
        $pdf .= "1 0 obj\n<</Type/Catalog/Pages 2 0 R>>\nendobj\n";
        
        
        $pdf .= "2 0 obj\n<</Type/Pages/Kids[3 0 R]/Count 1>>\nendobj\n";
        
        
        $pdf .= "3 0 obj\n<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>\nendobj\n";
        
        
        $pdf .= "4 0 obj\n<</Length $contentLength>>\nstream\n{$this->content}\nendstream\nendobj\n";
        
        
        $pdf .= "5 0 obj\n<</Type/Font/Subtype/Type1/BaseFont/Times-Roman/Encoding/WinAnsiEncoding>>\nendobj\n";
        
        
        $offset1 = 9;
        $offset2 = $offset1 + 46;
        $offset3 = $offset2 + 54;
        $offset4 = $offset3 + 130;
        $offset5 = $offset4 + 50 + $contentLength;
        $xrefPos = $offset5 + 90;
        
        
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        $pdf .= sprintf("%010d 00000 n \n", $offset1);
        $pdf .= sprintf("%010d 00000 n \n", $offset2);
        $pdf .= sprintf("%010d 00000 n \n", $offset3);
        $pdf .= sprintf("%010d 00000 n \n", $offset4);
        $pdf .= sprintf("%010d 00000 n \n", $offset5);
        
        
        $pdf .= "trailer\n<</Size 6/Root 1 0 R>>\n";
        $pdf .= "startxref\n$xrefPos\n%%EOF";
        
        return $pdf;
    }
}
?>
