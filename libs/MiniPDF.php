<?php



class MiniPDF {
    private $lines = [];
    
    public function addPage() {
        return true;
    }
    
    public function addText($x, $y, $text, $fontSize = 12, $color = null) {
        
        $text = iconv('UTF-8', 'ISO-8859-9//TRANSLIT', $text);
        
        
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        
        
        $this->lines[] = [$x, $y, $text, $fontSize, $color];
        return $this;
    }
    
    public function addTitle($x, $y, $text) {
        
        return $this->addText($x, $y, $text, 16, [0, 0, 1]);
    }
    
    public function output() {
        
        $content = "BT\n";
        
        $lastFontSize = 12;
        $lastColor = [0, 0, 0];
        
        foreach ($this->lines as $line) {
            $x = $line[0];
            $y = 792 - $line[1]; 
            $text = $line[2];
            $fontSize = $line[3] ?? 12;
            $color = $line[4];
            
            
            if ($fontSize != $lastFontSize) {
                $content .= "/F1 $fontSize Tf\n";
                $lastFontSize = $fontSize;
            }
            
            
            if ($color !== null && $color != $lastColor) {
                $content .= sprintf("%.3f %.3f %.3f rg\n", $color[0], $color[1], $color[2]);
                $lastColor = $color;
            } else if ($color === null && $lastColor != [0, 0, 0]) {
                
                $content .= "0 0 0 rg\n";
                $lastColor = [0, 0, 0];
            }
            
            
            $content .= sprintf("%.2f %.2f Td\n", $x, $y);
            $content .= "($text) Tj\n";
        }
        $content .= "ET";
        
        
        $contentLength = strlen($content);
        
        
        $pdf = "%PDF-1.4\n";
        
        
        $obj1 = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= $obj1;
        
        
        $obj2 = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf .= $obj2;
        
        
        $obj3 = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
        $pdf .= $obj3;
        
        
        $obj4 = "4 0 obj\n<< /Length $contentLength >>\nstream\n$content\nendstream\nendobj\n";
        $pdf .= $obj4;
        
        
        $obj5 = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $pdf .= $obj5;
        
        
        $offset1 = strlen("%PDF-1.4\n");
        $offset2 = $offset1 + strlen($obj1);
        $offset3 = $offset2 + strlen($obj2);
        $offset4 = $offset3 + strlen($obj3);
        $offset5 = $offset4 + strlen($obj4);
        $xrefOffset = $offset5 + strlen($obj5);
        
        
        $xref = "xref\n";
        $xref .= "0 6\n";
        $xref .= "0000000000 65535 f \n";
        $xref .= sprintf("%010d 00000 n \n", $offset1);
        $xref .= sprintf("%010d 00000 n \n", $offset2);
        $xref .= sprintf("%010d 00000 n \n", $offset3);
        $xref .= sprintf("%010d 00000 n \n", $offset4);
        $xref .= sprintf("%010d 00000 n \n", $offset5);
        
        $pdf .= $xref;
        
        
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n$xrefOffset\n%%EOF";
        
        return $pdf;
    }
}
?>