<?php

namespace App\Services;

use App\Jobs\RemoveFileInTwentyFourHoursJob;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * @param Request $request
     * @return bool|string
     */
    public static function storeUploadedFile(Request $request): bool|string
    {
        $name = Str::random(8) . '.pdf';

        Storage::putFileAs('public/uploaded', $request->file('file'), $name);

        self::createConvertedDirectory();

        return realpath(storage_path("app/public/uploaded/$name"));
    }

    /**
     * @param mixed $content
     * @return Application|UrlGenerator|string
     */
    public static function storeExtractedFile(mixed $content): Application|UrlGenerator|string
    {
        $name = Str::random(8) . '.txt';

        Storage::put("public/text/$name", $content);

        dispatch(new RemoveFileInTwentyFourHoursJob($name))
            ->delay(now()->addHours(24));

        self::clearFolder();

        return url(Storage::url('public/text' . '/' . $name ));
    }

    /**
     * @return void
     */
    private static function clearFolder(): void
    {
        File::deleteDirectory(storage_path("/app/public/converted"));
    }

    /**
     * @return void
     */
    private static function createConvertedDirectory(): void
    {
        if (!File::exists(storage_path("/app/public/converted"))) {
            File::makeDirectory(storage_path("/app/public/converted"));
        }
    }


    /**
     * @param string $name
     * @return void
     */
    public static function deleteFile(string $name): void
    {
        File::delete(storage_path("/app/public/converted/$name"));
    }

}
