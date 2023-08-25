<?php

namespace App\Services;

use App\Jobs\RemoveFileInTwentyFourHoursJob;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ExtractorService
{

    private mixed $content;
    private mixed $uploaded;

    /**
     * Constructor for class
     */
    public function __construct()
    {
        $this->content = '';
    }

    /**
     * @throws PageDoesNotExist
     * @throws PdfDoesNotExist
     */
    private function extractTextContent(): Application|UrlGenerator|string
    {
        $pdfHandler = new Pdf($this->uploaded);

        for ($i = 1; $i <= $pdfHandler->getNumberOfPages(); $i++) {
            $pdfHandler->setPage($i)
                ->saveImage(storage_path("/app/public/converted/image_$i.png"));
            $converted = realpath(storage_path("/app/public/converted/image_$i.png"));
            $this->content .= (new TesseractOCR($converted))
                    ->executable(config('services.tesseract.path'))
                    ->lang('rus', 'eng')
                    ->run() . '\n';
        }

        return FileService::storeExtractedFile($this->content);
    }

    /**
     * @throws PdfDoesNotExist
     * @throws PageDoesNotExist
     */
    public function getExtractedFile(mixed $fileContent): string|UrlGenerator|Application
    {
        $this->uploaded = $fileContent;
        return $this->extractTextContent();
    }
}
