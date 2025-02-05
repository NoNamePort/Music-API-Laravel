<?php

namespace App\Services;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use Illuminate\Support\Facades\Storage;

class AudioService
{
    private $ffmpeg;
    
    public function __construct()
    {
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => 'ffmpeg',
            'ffprobe.binaries' => 'ffprobe',
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ]);
    }

    public function compressAudio(string $inputPath, string $filename): string
    {
        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0777, true);
        }

        $tempOutputPath = $tempPath . '/' . $filename;
        
        try {
            $audio = $this->ffmpeg->open(storage_path('app/public/' . $inputPath));
            
            $format = new Mp3();
            $format->setAudioKiloBitrate(128);
            
            // Сохраняем сжатый файл во временную директорию
            $audio->save($format, $tempOutputPath);
            
            // Перемещаем сжатый файл в нужную директорию
            $compressedContent = file_get_contents($tempOutputPath);
            Storage::disk('public')->put($inputPath, $compressedContent);
            
            // Удаляем временный файл
            unlink($tempOutputPath);
            
            return $inputPath;
        } catch (\Exception $e) {
            \Log::error('Ошибка сжатия аудио: ' . $e->getMessage());
            throw $e;
        }
    }
} 