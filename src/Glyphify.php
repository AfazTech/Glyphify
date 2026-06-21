<?php

namespace AfazTech\Glyphify;

class Glyphify
{
    private string $text;
    private array $output = [];
    private array $config;
    private array $fonts = [];
    private array $latinFontNames = [];
    private array $persianFontNames = [];
    private array $persianStyledFontNames = [];

    public function __construct(string $text, array $config = [])
    {
        $this->text = trim($text);
        $this->config = array_merge([
            'persian' => true,
            'latin' => true,
            'finglish' => true,
            'all_fonts' => false,
        ], $config);
        
        $this->loadAllFonts();
        $this->categorizeFonts();
    }

    private function loadAllFonts(): void
    {
        $fontDir = __DIR__ . '/../fonts/';
        $jsonFiles = glob($fontDir . '*.json');
        
        foreach ($jsonFiles as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['name']) && isset($data['map'])) {
                $this->fonts[$data['name']] = [
                    'map' => $data['map'],
                    'type' => $data['type'] ?? 'latin'
                ];
            }
        }
    }

    private function categorizeFonts(): void
    {
        foreach ($this->fonts as $name => $font) {
            $type = $font['type'] ?? 'latin';
            
            if ($type === 'persian') {
                $this->persianFontNames[] = $name;
            } elseif ($type === 'persian_styled') {
                $this->persianStyledFontNames[] = $name;
            } else {
                $this->latinFontNames[] = $name;
            }
        }
        
        sort($this->latinFontNames);
        sort($this->persianFontNames);
        sort($this->persianStyledFontNames);
    }

    public function getFontNames(): array
    {
        return array_keys($this->fonts);
    }

    public function getFontByName(string $name): ?array
    {
        return $this->fonts[$name] ?? null;
    }

    public function getAllFonts(): array
    {
        return $this->fonts;
    }

    public function getLatinFontNames(): array
    {
        return $this->latinFontNames;
    }

    public function getPersianFontNames(): array
    {
        return $this->persianFontNames;
    }

    public function getPersianStyledFontNames(): array
    {
        return $this->persianStyledFontNames;
    }

    private function mbStrSplit(string $string): array
    {
        return preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    private function mapText(string $text, array $map): string
    {
        $out = '';
        foreach ($this->mbStrSplit($text) as $char) {
            if (isset($map[$char])) {
                $out .= $map[$char];
            } else {
                $lower = mb_strtolower($char);
                $out .= $map[$lower] ?? $char;
            }
        }
        return $out;
    }

    private function applyPersianFont(string $text, array $fontMap): string
    {
        $result = ' ' . $text . ' ';
        uksort($fontMap, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        foreach ($fontMap as $key => $value) {
            $result = str_replace($key, $value, $result);
        }
        return trim($result);
    }

    private function hasPersian(string $text): bool
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }

    private function hasEnglish(string $text): bool
    {
        return preg_match('/[a-zA-Z0-9]/', $text);
    }

    private function persianToFinglish(string $text): string
    {
        if (!$text) return '';

        $map = [
            'ا'=>'a', 'آ'=>'aa', 'أ'=>'a', 'إ'=>'e', 'ء'=>"'",
            'ب'=>'b', 'پ'=>'p', 'ت'=>'t', 'ث'=>'s', 'ج'=>'j',
            'چ'=>'ch', 'ح'=>'h', 'خ'=>'kh', 'د'=>'d', 'ذ'=>'z',
            'ر'=>'r', 'ز'=>'z', 'ژ'=>'zh', 'س'=>'s', 'ش'=>'sh',
            'ص'=>'s', 'ض'=>'z', 'ط'=>'t', 'ظ'=>'z', 'ع'=>"'",
            'غ'=>'gh', 'ف'=>'f', 'ق'=>'gh', 'ك'=>'k', 'ک'=>'k',
            'گ'=>'g', 'ل'=>'l', 'م'=>'m', 'ن'=>'n', 'ه'=>'h',
            'ة'=>'h', 'ؤ'=>"'", 'ئ'=>"'", 'ی'=>'y', 'ي'=>'y',
            ' '=>' ', '‌'=>''
        ];

        $result = '';
        $len = mb_strlen($text);

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1);
            $next = ($i < $len - 1) ? mb_substr($text, $i + 1, 1) : '';

            if ($char === 'و') {
                $result .= ($i === 0 || $i === $len - 1 || $next === ' ' || $next === '') ? 'v' : 'o';
            } elseif ($char === 'ی' || $char === 'ي') {
                $result .= ($i === 0) ? 'y' : 'i';
            } elseif ($char === 'ه' || $char === 'ة') {
                $result .= ($i === $len - 1) ? 'eh' : 'h';
            } elseif ($char === 'ق') {
                $result .= (in_array($next, ['ا', 'و'])) ? 'q' : 'gh';
            } elseif (isset($map[$char])) {
                $result .= $map[$char];
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    public function generate(?string $fontName = null): array
    {
        $this->output = [];
        $text = $this->text;
        $hasPersian = $this->hasPersian($text);
        $hasEnglish = $this->hasEnglish($text);

        if ($fontName !== null && isset($this->fonts[$fontName])) {
            $font = $this->fonts[$fontName];
            return [[
                'name' => $fontName,
                'text' => $this->mapText($text, $font['map'])
            ]];
        }

        if ($this->config['all_fonts']) {
            foreach ($this->fonts as $name => $font) {
                $this->output[] = [
                    'name' => $name,
                    'text' => $this->mapText($text, $font['map'])
                ];
            }
            return $this->output;
        }

        $fontMaps = $this->fonts;

        if ($hasPersian && !$hasEnglish) {
            foreach ($this->persianFontNames as $name) {
                if (isset($fontMaps[$name])) {
                    $this->output[] = [
                        'name' => $name,
                        'text' => $this->applyPersianFont($text, $fontMaps[$name]['map'])
                    ];
                }
            }

            foreach ($this->persianStyledFontNames as $name) {
                if (isset($fontMaps[$name])) {
                    $this->output[] = [
                        'name' => $name,
                        'text' => $this->mapText($text, $fontMaps[$name]['map'])
                    ];
                }
            }

            $reverse = implode('', array_reverse($this->mbStrSplit($text)));
            $this->output[] = ['name' => 'Persian Reverse', 'text' => $reverse];

            return $this->output;
        }

        if ($hasEnglish && !$hasPersian) {
            foreach ($this->latinFontNames as $name) {
                if (isset($fontMaps[$name])) {
                    $outputText = ($name === 'Upside Down') 
                        ? $this->mapText(strrev($text), $fontMaps[$name]['map']) 
                        : $this->mapText($text, $fontMaps[$name]['map']);
                    $this->output[] = ['name' => $name, 'text' => $outputText];
                }
            }
            
            return $this->output;
        }

        if ($hasPersian && $hasEnglish) {
            foreach ($this->persianFontNames as $name) {
                if (isset($fontMaps[$name])) {
                    $this->output[] = [
                        'name' => $name,
                        'text' => $this->applyPersianFont($text, $fontMaps[$name]['map'])
                    ];
                }
            }

            foreach ($this->persianStyledFontNames as $name) {
                if (isset($fontMaps[$name])) {
                    $this->output[] = [
                        'name' => $name,
                        'text' => $this->mapText($text, $fontMaps[$name]['map'])
                    ];
                }
            }

            $reverse = implode('', array_reverse($this->mbStrSplit($text)));
            $this->output[] = ['name' => 'Persian Reverse', 'text' => $reverse];

            if ($this->config['finglish']) {
                $finglish = $this->persianToFinglish($text);
                $this->output[] = ['name' => 'Finglish (Transliteration)', 'text' => $finglish];

                if ($this->config['latin']) {
                    foreach ($this->latinFontNames as $name) {
                        if (isset($fontMaps[$name])) {
                            $outputText = ($name === 'Upside Down') 
                                ? $this->mapText(strrev($finglish), $fontMaps[$name]['map']) 
                                : $this->mapText($finglish, $fontMaps[$name]['map']);
                            $this->output[] = ['name' => $name . ' (on Finglish)', 'text' => $outputText];
                        }
                    }
                }
            }

            if ($this->config['latin']) {
                $englishText = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
                if (!empty(trim($englishText))) {
                    foreach ($this->latinFontNames as $name) {
                        if (isset($fontMaps[$name])) {
                            $outputText = ($name === 'Upside Down') 
                                ? $this->mapText(strrev($englishText), $fontMaps[$name]['map']) 
                                : $this->mapText($englishText, $fontMaps[$name]['map']);
                            $this->output[] = ['name' => $name . ' (English only)', 'text' => $outputText];
                        }
                    }
                }
            }
            
            return $this->output;
        }

        return $this->output;
    }

    public function getArray(): array
    {
        return $this->output;
    }

    public function display(?string $fontName = null): void
    {
        $output = $this->generate($fontName);

        echo "\n" . str_repeat('═', 70) . "\n";
        echo "              GLYPHIFY - FONT GENERATOR\n";
        echo str_repeat('═', 70) . "\n\n";

        $counter = 1;
        foreach ($output as $item) {
            printf("%3d. %-35s: %s\n", $counter, $item['name'], $item['text']);
            $counter++;
        }

        echo "\n" . str_repeat('═', 70) . "\n";
        echo "Total: " . ($counter - 1) . " unique font styles\n";
        echo str_repeat('═', 70) . "\n";
    }

    public function toJson(?string $fontName = null): string
    {
        return json_encode($this->generate($fontName), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function toCsv(?string $fontName = null): string
    {
        $rows = [['#', 'Font Name', 'Text']];
        $counter = 1;
        foreach ($this->generate($fontName) as $item) {
            $rows[] = [$counter++, $item['name'], $item['text']];
        }
        $fp = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        rewind($fp);
        return stream_get_contents($fp);
    }
}
