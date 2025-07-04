<?php
// ai_helper.php

function getAiSuggestionForImage($imagePath) {
    if (!file_exists($imagePath)) {
        return "Error: Image file not found at path: " . htmlspecialchars($imagePath);
    }
    $imageData = base64_encode(file_get_contents($imagePath));
    if ($imageData === false) {
        return "Error: Could not read image data from file.";
    }

    $apiKey = GOOGLE_API_KEY;

    // --- Using the exact model name from your project's available list ---
    $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

    $prompt = "You are a professional facilities maintenance assistant. Your task is to analyze the following image from a damage report. ".
              "Based only on what you see in the image, provide the following in a clear, numbered format: ".
              "1. **Problem Identification:** A one-sentence description of the issue. ".
              "2. **Possible Causes:** A bulleted list of 2-3 likely causes. ".
              "3. **Recommended Actions:** A bulleted list of initial steps the maintenance person should take. ".
              "If the image is unclear or shows no obvious problem, state that the issue cannot be determined from the image provided.";

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt],
                    [
                        'inline_data' => [
                            'mime_type' => 'image/jpeg',
                            'data' => $imageData
                        ]
                    ]
                ]
            ]
        ]
    ];
    $jsonData = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "cURL Error: " . $error_msg;
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        return "Error: Failed to communicate with AI service. (HTTP Code: " . $httpCode . ") Response: " . htmlspecialchars($response);
    }

    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        return "AI API Error: " . htmlspecialchars($result['error']['message']);
    }
    if (empty($result['candidates'][0]['content']['parts'][0]['text'])) {
        return "AI did not provide a suggestion. The response may have been blocked by a content safety filter.";
    }
    
    return $result['candidates'][0]['content']['parts'][0]['text'];
}
?>