<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\View;
use Dompdf\Dompdf;
use Dompdf\Options;

final class PdfService
{
    public function render(string $view, array $data = [], string $paper = 'A4', string $orientation = 'portrait'): string
    {
        $html = View::render($view, $data);

        $options = new Options();
        $options->setIsRemoteEnabled(false);
        $options->setIsHtml5ParserEnabled(true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return $dompdf->output();
    }
}
