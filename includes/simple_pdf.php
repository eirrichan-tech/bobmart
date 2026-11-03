<?php
class SimplePDF {
    private $pages = [];
    private $cur = '';
    private $H = 842;                       // A4 page height

    private function clean($s) {            // PDF text can't hold odd symbols
        $s = preg_replace('/[^\x20-\x7E]/', '?', (string)$s);
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }
    public function newPage() { $this->pages[] = $this->cur; $this->cur = ''; }

    // $top = distance from the TOP of the page (PDF counts from the bottom, so we flip it)
    public function text($x, $top, $s, $size = 11, $bold = false) {
        $this->cur .= sprintf("BT /%s %d Tf %.2F %.2F Td (%s) Tj ET\n",
            $bold ? 'F2' : 'F1', $size, $x, $this->H - $top, $this->clean($s));
    }
    public function line($x1, $t1, $x2, $t2) {
        $this->cur .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $x1, $this->H - $t1, $x2, $this->H - $t2);
    }
    public function output(): string {
        $pages = $this->pages;
        $pages[] = $this->cur;
        $n = count($pages);
        $kids = [];
        for ($i = 0; $i < $n; $i++) $kids[] = (5 + 2 * $i) . " 0 R";

        $obj = [];
        $obj[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $obj[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count $n >>";
        $obj[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $obj[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        foreach ($pages as $i => $content) {
            $obj[5 + 2 * $i] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] "
                . "/Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents " . (6 + 2 * $i) . " 0 R >>";
            $obj[6 + 2 * $i] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream";
        }
        $out = "%PDF-1.4\n";
        $offsets = [];
        foreach ($obj as $id => $body) {
            $offsets[$id] = strlen($out);   // remember where each object starts
            $out .= "$id 0 obj\n$body\nendobj\n";
        }
        $xref = strlen($out);
        $count = count($obj) + 1;
        $out .= "xref\n0 $count\n0000000000 65535 f \n";
        foreach ($offsets as $o) $out .= sprintf("%010d 00000 n \n", $o);
        $out .= "trailer\n<< /Size $count /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        return $out;                        // the whole PDF as a string
    }
}