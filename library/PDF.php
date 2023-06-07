<?php

/// Примеры тут https://tcpdf.org/examples

class PDF
{
    static function outputByHTML($name, $html)
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
        $pdf->setFontSubsetting(true);
        $pdf->AddPage();
        $pdf->writeHTML($html);
        $pdf->Output($name, 'D');
        exit;
    }

    static function showByHTML($name, $html, $page_orientation = 'P')
    {
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false);
        //$pdf->setFontSubsetting(true);
        $pdf->SetTitle($name);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(15);

//        $pdf->SetHeaderData('/assets/img/wallpaperflare.com_wallpaper.jpg', 20, $name, "Женя едит едишион\n\n");
        $pdf->SetHeaderData('/files/mftBlack.png', 20, $name);
        $pdf->SetFooterData([0,0,0], [255,255,255]);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Output($name, 'I');
        exit;
    }

}