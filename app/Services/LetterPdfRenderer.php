<?php

namespace App\Services;

use App\Models\LetterRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LetterPdfRenderer
{
    /** @param array<string, string> $fields
     * @param  array{user_id: int, signer: string, signed_at: Carbon, signature_type: string}|null  $signature
     */
    public function render(LetterRequest $letterRequest, array $fields, ?array $signature = null): string
    {
        $letterRequest->loadMissing('resident.region.village');
        $village = $letterRequest->resident?->region?->village;
        $villageName = $village?->name ?: 'Desa';
        $letterType = $letterRequest->effectiveLetterType();
        $issuedAt = $signature['signed_at'] ?? now();
        $localDate = $issuedAt->timezone('Asia/Jakarta');
        $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $dateLabel = $localDate->day.' '.$months[$localDate->month].' '.$localDate->year;
        $pages = [];
        $content = '';
        $y = 0;

        $text = function (float $x, float $baseline, string $value, int $size = 10, bool $bold = false) use (&$content): void {
            $font = $bold ? 'F2' : 'F1';
            $content .= 'BT /'.$font.' '.$size.' Tf 1 0 0 1 '.round($x, 2).' '.round($baseline, 2).' Tm ('.$this->pdfText($value).") Tj ET\n";
        };
        $centered = function (float $baseline, string $value, int $size = 10, bool $bold = false) use ($text): void {
            $plain = Str::ascii($value);
            $x = max(48, (595 - (strlen($plain) * $size * 0.50)) / 2);
            $text($x, $baseline, $value, $size, $bold);
        };
        $startPage = function (bool $continuation = false) use (&$content, &$y, $text, $centered, $villageName, $village, $letterType, $letterRequest, $dateLabel, $signature): void {
            $content = '';
            if (! $continuation) {
                $centered(798, 'PEMERINTAH DESA '.$villageName, 14, true);
                $centered(780, 'KANTOR KEPALA DESA', 11, true);
                if (filled($village?->address)) {
                    $addressLines = $this->wrappedLines((string) $village->address, 82);
                    $addressY = 763;
                    foreach (array_slice($addressLines, 0, 2) as $line) {
                        $centered($addressY, $line, 8);
                        $addressY -= 11;
                    }
                }
                $content .= "0.8 w 48 744 m 547 744 l S\n0.3 w 48 741 m 547 741 l S\n";
                $centered(727, $signature !== null
                    ? 'DOKUMEN PROTOTIPE - BUKAN TANDA TANGAN ELEKTRONIK TERSERTIFIKASI'
                    : 'DRAF - BELUM DITERBITKAN', 7, true);
                $titleY = 695;
                foreach (array_slice($this->wrappedLines(Str::upper($letterType->name), 64), 0, 3) as $titleLine) {
                    $centered($titleY, $titleLine, 13, true);
                    $titleWidth = min(499, strlen(Str::ascii($titleLine)) * 6.5);
                    $titleStart = (595 - $titleWidth) / 2;
                    $content .= '0.45 w '.round($titleStart, 2).' '.round($titleY - 4, 2).' m '.round($titleStart + $titleWidth, 2).' '.round($titleY - 4, 2).' l S'."\n";
                    $titleY -= 16;
                }
                $numberY = $titleY - 3;
                $centered($numberY, 'Nomor: '.$letterRequest->request_number, 9);
                $text(344, $numberY - 27, $villageName.', '.$dateLabel, 9);
                $text(48, $numberY - 61, 'Yang bertanda tangan di bawah ini menerangkan bahwa:', 10);
                $text(48, $numberY - 84, 'Data pemohon:', 10, true);
                $y = $numberY - 104;
            } else {
                $centered(795, Str::upper($letterType->name).' (LANJUTAN)', 11, true);
                $centered(777, 'Nomor: '.$letterRequest->request_number, 9);
                $content .= "0.5 w 48 765 m 547 765 l S\n";
                $y = 738;
            }
        };
        $finishPage = function () use (&$content, &$pages, $centered, $letterRequest): void {
            $content .= "0.35 w 48 58 m 547 58 l S\n";
            $centered(43, 'Salinan digital · '.$letterRequest->request_number.' · Prototipe layanan desa', 7);
            $pages[] = $content;
        };
        $writeParagraph = function (string $paragraph, int $width = 91) use (&$y, $text, $startPage, $finishPage): void {
            foreach ($this->wrappedLines($paragraph, $width) as $line) {
                if ($y < 105) {
                    $finishPage();
                    $startPage(true);
                }
                $text(48, $y, $line, 9);
                $y -= 14;
            }
            $y -= 5;
        };

        $startPage();
        foreach ($fields as $label => $value) {
            $lines = $this->wrappedLines(trim($value) === '' ? '—' : $value, 57);
            foreach ($lines as $index => $line) {
                if ($y < 115) {
                    $finishPage();
                    $startPage(true);
                }
                if ($index === 0) {
                    $text(56, $y, $label, 9);
                    $text(195, $y, ':', 9);
                }
                $text(208, $y, $line, 9);
                $y -= 14;
            }
            $y -= 2;
        }

        $y -= 8;
        $writeParagraph('Keterangan di atas dibuat berdasarkan data permohonan yang telah diperiksa oleh petugas desa.');
        $writeParagraph('Demikian surat keterangan ini dibuat agar dapat dipergunakan sebagaimana mestinya.');

        if ($y < 175) {
            $finishPage();
            $startPage(true);
        }
        $text(378, $y - 4, 'Kepala Desa', 9);
        $text(378, $y - 64, '________________________', 9);
        $text(48, max(90, $y - 94), $signature === null
            ? 'Status: draf pemeriksaan, belum disetujui Kepala Desa.'
            : 'Persetujuan prototipe dicatat pada '.$dateLabel.'. Tanda tangan elektronik tersertifikasi tidak digunakan.', 8);
        $finishPage();

        return $this->buildPdf($pages);
    }

    /** @param list<string> $pages */
    private function buildPdf(array $pages): string
    {
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];
        $pageReferences = [];
        foreach ($pages as $pageContent) {
            $pageId = count($objects) + 1;
            $streamId = $pageId + 1;
            $pageReferences[] = $pageId.' 0 R';
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents '.$streamId.' 0 R >>';
            $objects[] = '<< /Length '.strlen($pageContent).">>\nstream\n".$pageContent."\nendstream";
        }
        $objects[1] = '<< /Type /Pages /Kids ['.implode(' ', $pageReferences).'] /Count '.count($pages).' >>';

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf('%010d 00000 n ', $offset)."\n";
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xrefOffset."\n%%EOF";
    }

    /** @return list<string> */
    private function wrappedLines(string $value, int $width): array
    {
        $lines = [];
        foreach (preg_split('/\R/u', str_replace("\0", '', $value)) ?: [''] as $paragraph) {
            $wrapped = wordwrap(Str::ascii($paragraph), $width, "\n", true);
            array_push($lines, ...explode("\n", $wrapped));
        }

        return $lines === [] ? [''] : $lines;
    }

    private function pdfText(string $value): string
    {
        $ascii = Str::ascii($value);
        $ascii = preg_replace('/[^\x20-\x7E]/', '', $ascii) ?? '';

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
    }
}
