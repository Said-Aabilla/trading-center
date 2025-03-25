<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIService
{
    /**
     * Analyse la tendance en envoyant un prompt à l'API OpenAI.
     * @param string $prompt
     * @return string "bullish", "bearish" ou "unknown"
     */
    public function analyseTendance(string $prompt): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un expert en analyse des tendances financières.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.5,
                'max_tokens' => 10,
            ]);

            $result = $response->json();
            return strtolower(trim($result['choices'][0]['message']['content']));
        } catch (Exception $e) {
            return "unknown";
        }
    }
}
