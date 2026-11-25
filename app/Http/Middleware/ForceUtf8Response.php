<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUtf8Response
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $contentType = $response->headers->get('Content-Type');

        if (! $this->isTextualResponse($contentType)) {
            return $response;
        }

        $content = (string) $response->getContent();

        if ($this->containsMojibake($content)) {
            $response->setContent($this->repairMojibake($content));
        }

        $this->normalizeCharset($response, $contentType);

        return $response;
    }

    private function isTextualResponse(?string $contentType): bool
    {
        if ($contentType === null) {
            return false;
        }

        $contentType = strtolower($contentType);

        return str_starts_with($contentType, 'text/')
            || str_contains($contentType, 'application/json')
            || str_contains($contentType, 'application/javascript')
            || str_contains($contentType, 'application/xml')
            || str_contains($contentType, '+json')
            || str_contains($contentType, '+xml');
    }

    private function normalizeCharset(Response $response, string $contentType): void
    {
        $contentType = (string) preg_replace('/;\s*charset=[^;]+/i', '', $contentType);

        $response->headers->set('Content-Type', $contentType.'; charset=UTF-8');
    }

    private function containsMojibake(string $content): bool
    {
        foreach (array_keys($this->replacements()) as $sequence) {
            if (str_contains($content, $sequence)) {
                return true;
            }
        }

        return false;
    }

    private function repairMojibake(string $content): string
    {
        return strtr($content, $this->replacements());
    }

    /**
     * @return array<string, string>
     */
    private function replacements(): array
    {
        return [
            "\xC3\x83\xC2\xB3" => 'ó',
            "\xC3\x83\xC2\xA1" => 'á',
            "\xC3\x83\xC2\xA9" => 'é',
            "\xC3\x83\xC2\xAD" => 'í',
            "\xC3\x83\xC2\xBA" => 'ú',
            "\xC3\x83\xC2\xBC" => 'ü',
            "\xC3\x83\xC2\xB1" => 'ñ',
            "\xC3\x83\xC2\xA7" => 'Ç',
            "\xC3\x83\xC2\x80" => 'À',
            "\xC3\x83\xC2\x81" => 'Á',
            "\xC3\x83\xC2\x89" => 'É',
            "\xC3\x83\xC2\x8D" => 'Í',
            "\xC3\x83\xC2\x93" => 'Ó',
            "\xC3\x83\xC2\x9A" => 'Ú',
            "\xC3\x83\xC2\x8E" => 'Î',
            "\xC3\x83\xC2\x9B" => 'Û',
            "\xC3\x83\xC2\x9C" => 'Ü',
            "\xC3\x83\xC2\x91" => 'Ñ',
            "\xC3\x83\xC2\x87" => 'Ç',
            "\xC3\x83\xC2\x8F" => 'Ï',
            "\xC3\x83\xC2\x82" => 'Â',
            "\xC3\x83\xC2\xA0" => 'à',
            "\xC3\x83\xC2\xA2" => 'â',
            "\xC3\x83\xC2\xA4" => 'ä',
            "\xC3\x83\xC2\xA8" => 'è',
            "\xC3\x83\xC2\xAA" => 'ê',
            "\xC3\x83\xC2\xAE" => 'î',
            "\xC3\x83\xC2\xB4" => 'ô',
            "\xC3\x83\xC2\xBB" => 'û',
            "\xC3\x83\xC2\xB0" => 'ð',
            "\xC3\x83\xC2\xB8" => '¸',
            "\xC3\x82\xC2\xB0" => '°',
            "\xC3\xA2\xC2\x82\xC2\xAC" => '€',
            "\xC3\xA2\xC2\x80\xC2\x9C" => '“',
            "\xC3\xA2\xC2\x80\xC2\x9D" => '”',
            "\xC3\xA2\xC2\x80\xC2\x98" => '‘',
            "\xC3\xA2\xC2\x80\xC2\x93" => '–',
            "\xC3\xA2\xC2\x80\xC2\x94" => '—',
            "\xC3\xA2\xC2\x80\xC2\xA6" => '…',
        ];
    }
}
