<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Translate text from one language to another.
     */
    public function translate(string $text, string $fromLanguage, string $toLanguage): string
    {
        if ($fromLanguage === $toLanguage) {
            return $text;
        }

        $cacheKey = 'translation:' . md5("{$text}:{$fromLanguage}:{$toLanguage}");

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($text, $fromLanguage, $toLanguage) {
            return $this->callTranslationApi($text, $fromLanguage, $toLanguage);
        });
    }

    /**
     * Detect the language of a given text.
     */
    public function detectLanguage(string $text): string
    {
        $apiKey = config('services.translation.api_key');
        $endpoint = config('services.translation.endpoint', 'https://api.cognitive.microsofttranslator.com');

        if (!$apiKey) {
            Log::warning('Translation API key not configured; defaulting to "en".');

            return 'en';
        }

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$endpoint}/detect?api-version=3.0", [
                ['Text' => $text],
            ]);

            if ($response->successful()) {
                return $response->json()[0]['language'] ?? 'en';
            }
        } catch (\Throwable $e) {
            Log::error('Language detection failed', ['error' => $e->getMessage()]);
        }

        return 'en';
    }

    /**
     * Get the list of supported languages.
     */
    public function getSupportedLanguages(): array
    {
        return Cache::remember('translation:supported_languages', now()->addDay(), function () {
            return [
                'en' => 'English',
                'es' => 'Spanish',
                'fr' => 'French',
                'de' => 'German',
                'it' => 'Italian',
                'pt' => 'Portuguese',
                'ja' => 'Japanese',
                'ko' => 'Korean',
                'zh' => 'Chinese',
                'ar' => 'Arabic',
                'hi' => 'Hindi',
                'tl' => 'Filipino',
                'ceb' => 'Cebuano',
            ];
        });
    }

    /**
     * Call the external translation API.
     */
    protected function callTranslationApi(string $text, string $from, string $to): string
    {
        $apiKey = config('services.translation.api_key');
        $endpoint = config('services.translation.endpoint', 'https://api.cognitive.microsofttranslator.com');

        if (!$apiKey) {
            Log::warning('Translation API key not configured; returning original text.');

            return $text;
        }

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$endpoint}/translate?api-version=3.0&from={$from}&to={$to}", [
                ['Text' => $text],
            ]);

            if ($response->successful()) {
                return $response->json()[0]['translations'][0]['text'] ?? $text;
            }
        } catch (\Throwable $e) {
            Log::error('Translation API call failed', ['error' => $e->getMessage()]);
        }

        return $text;
    }
}
