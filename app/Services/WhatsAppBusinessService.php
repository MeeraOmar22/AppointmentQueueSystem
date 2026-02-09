<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppBusinessService
{
    private $token;
    private $phoneId;
    private $apiVersion = 'v23.0';
    private $apiBaseUrl = 'https://graph.facebook.com';

    public function __construct()
    {
        // Get credentials from .env or configuration
        $this->token = env('WHATSAPP_BUSINESS_TOKEN', 'EAAT8fFtKwgYBQnB4SZBOuWXZA4nQIzACnZAIldgTgJtAXJHxIQeE1NVfzMfTAZBvCsUSm0L5sIeiEdrdl74SDjvHAfno8eZBI3iA7ZAdIRzRlk85vOLMbZCDiDAPqyuO99luWAuOQksZBwL4ocWVUmnjtimiQDWOwDn7cV7uynNZCCFShY2D4eRt5i3QE1URBZBfA7to848DOpIZASWyLZAiBSwxjKMYM6abkKI1TwpL');
        $this->phoneId = env('WHATSAPP_PHONE_ID', '1034387306416120');
    }

    /**
     * Send a text message via WhatsApp Business API
     *
     * @param string $recipientPhone Phone number in international format (e.g., 601155577037 or +601155577037)
     * @param string $message Message text
     * @return array Response from WhatsApp API
     */
    public function sendMessage($recipientPhone, $message)
    {
        // Normalize phone number - remove all non-digits
        $digits = preg_replace('/[^0-9]/', '', $recipientPhone);
        
        // Add country code if needed
        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            // Format: 0123456789 -> 60123456789
            $digits = '60' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            // Format: 123456789 -> 60123456789 (assuming missing country code)
            $digits = '60' . $digits;
        }
        // If already has 60 or other country code, use as-is
        
        $phone = '+' . $digits;

        try {
            $response = Http::withToken($this->token)
                ->post(
                    "{$this->apiBaseUrl}/{$this->apiVersion}/{$this->phoneId}/messages",
                    [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $digits, // WhatsApp API expects digits without +
                        'type' => 'text',
                        'text' => [
                            'body' => $message
                        ]
                    ]
                );

            $result = $response->json();

            Log::info('WhatsApp message sent', [
                'to' => $phone,
                'status' => $response->status(),
                'response' => $result
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $result,
                'phone' => $phone
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp message failed', [
                'to' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'phone' => $phone
            ];
        }
    }

    /**
     * Send a template message via WhatsApp Business API
     *
     * @param string $recipientPhone Phone number
     * @param string $templateName Template name
     * @param array $parameters Template parameters
     * @param string $languageCode Language code (default: en)
     * @return array Response from WhatsApp API
     */
    public function sendTemplateMessage($recipientPhone, $templateName, $parameters = [], $languageCode = 'en')
    {
        // Normalize phone number
        $digits = preg_replace('/[^0-9]/', '', $recipientPhone);
        
        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = '60' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $digits = '60' . $digits;
        }

        $phone = '+' . $digits;

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $digits,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $languageCode
                    ]
                ]
            ];

            // Add parameters if provided
            if (!empty($parameters)) {
                $payload['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => array_map(function ($param) {
                            return ['type' => 'text', 'text' => $param];
                        }, $parameters)
                    ]
                ];
            }

            $response = Http::withToken($this->token)
                ->post(
                    "{$this->apiBaseUrl}/{$this->apiVersion}/{$this->phoneId}/messages",
                    $payload
                );

            $result = $response->json();

            Log::info('WhatsApp template message sent', [
                'to' => $phone,
                'template' => $templateName,
                'status' => $response->status(),
                'response' => $result
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $result,
                'phone' => $phone
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp template message failed', [
                'to' => $phone,
                'template' => $templateName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'phone' => $phone
            ];
        }
    }

    /**
     * Check if phone number is valid
     *
     * @param string $phone Phone number
     * @return bool
     */
    public function isValidPhone($phone)
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        // Must have at least 10 digits (Malaysia minimum)
        return strlen($digits) >= 10;
    }
}
