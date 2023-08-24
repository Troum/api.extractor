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

        return realpath(storage_path("app/public/uploaded/$name"));
    }

    /**
     * @param mixed $content
     * @return Application|UrlGenerator|string
     */
    public static function storeExtractedFile(mixed $content): Application|UrlGenerator|string
    {
        $name = Str::random(8) . '.txt';
        Storage::putFileAs('public/text', $content, $name);

        dispatch(new RemoveFileInTwentyFourHoursJob($name))
            ->delay(now()->addHours(24));

        return url(Storage::url('public/text' . '/' . $name ));
    }

    /**
     * @param string $name
     * @return void
     */
    public static function clearFolder(string $name): void
    {
        File::delete(storage_path("/app/public/converted/$name"));
    }

}
