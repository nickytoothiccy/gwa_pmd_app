<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FfuJsonEmailService
{
    private $jsonFilePath = 'ffu_edits.json';

    public function saveToJson(array $data)
    {
        $data['timestamp'] = now()->toDateTimeString();
        
        if (Storage::exists($this->jsonFilePath)) {
            $existingData = json_decode(Storage::get($this->jsonFilePath), true);
            $existingData[] = $data;
            Storage::put($this->jsonFilePath, json_encode($existingData, JSON_PRETTY_PRINT));
        } else {
            Storage::put($this->jsonFilePath, json_encode([$data], JSON_PRETTY_PRINT));
        }

        Log::info('Data appended to JSON file', ['file' => $this->jsonFilePath]);
    }

    public function jsonFileHasData()
    {
        if (!Storage::exists($this->jsonFilePath)) {
            return false;
        }

        $data = json_decode(Storage::get($this->jsonFilePath), true);
        return !empty($data);
    }

    public function sendEmailAndClearJson()
    {
        if (!$this->jsonFileHasData()) {
            Log::info('No data in JSON file to send');
            return false;
        }

        $jsonContent = Storage::get($this->jsonFilePath);
        $emailAddress = config('mail.ffu_notification_email');

        Log::info('Attempting to send email', ['to' => $emailAddress]);

        try {
            Mail::raw($jsonContent, function ($message) use ($emailAddress) {
                $message->to($emailAddress)
                    ->subject('FFU Equipment Updates');
            });

            Storage::put($this->jsonFilePath, json_encode([]));
            Log::info('Email sent and JSON file cleared');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $emailAddress,
                'content' => $jsonContent
            ]);
            return false;
        }
    }
}
