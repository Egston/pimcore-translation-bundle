<?php
/**
 * @author Piotr Rugała <piotr@isedo.pl>
 * @copyright Copyright (c) 2021 Divante Ltd. (https://divante.co)
 */

declare(strict_types=1);

namespace DivanteTranslationBundle\Provider;

use DivanteTranslationBundle\Exception\TranslationException;

class GoogleProvider extends AbstractProvider
{
    protected string $url = 'https://www.googleapis.com/';

    private function toGoogleTarget(string $locale): string
    {
        $lang = locale_get_primary_language($locale) ?: $locale;

        if ($lang === 'zh') {
            $region = strtoupper(locale_get_region($locale) ?? '');
            $script = locale_get_script($locale) ?? '';

            // Traditional if script says Hant OR region is traditionally-Hant
            if (stripos($script, 'Hant') !== false || in_array($region, ['TW', 'HK', 'MO'], true)) {
                return $region === 'HK' ? 'zh-HK' : 'zh-TW'; // optional nuance; zh-TW is usually fine
            }

            return 'zh-CN'; // default to Simplified if not clearly Traditional
        }

        // For most other languages, Google expects ISO 639-1 (sometimes BCP-47).
        return $lang;
    }

    public function translate(string $data, string $targetLanguage): string
    {
        try {
            $response = $this->getHttpClient()->request(
                'GET',
                'language/translate/v2',
                [
                    'query' => [
                        'key' => $this->apiKey,
                        'q' => $data,
                        'source' => '',
                        'target' => $this->toGoogleTarget($targetLanguage),
                    ]
                ]
            );
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if ($data['error']) {
                throw new TranslationException();
            }
        } catch (\Throwable $exception) {
            throw new TranslationException();
        }

        return $data['data']['translations'][0]['translatedText'];
    }

    public function getName(): string
    {
        return 'google_translate';
    }
}
