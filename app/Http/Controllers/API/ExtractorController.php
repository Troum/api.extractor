<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ExtractorService;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtractorController extends Controller
{
    private ExtractorService $extractorService;
    public function __construct(ExtractorService $extractorService)
    {
        $this->extractorService = $extractorService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function extract(Request $request): JsonResponse
    {

        try {
            $uploaded = FileService::storeUploadedFile($request);
            $downloadURL = $this->extractorService->getExtractedFile($uploaded);

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $downloadURL,
                    'notice' => 'Файл будет удален в течении суток'
                ]
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]);
        }
    }
}
