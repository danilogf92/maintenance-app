<?php

namespace App\Modules\Institutions\Tests;

class CodeLimits
{
    public static function violations(string $directory): array
    {
        $errors = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            if (count(explode("\n", rtrim($source, "\n"))) > 300) {
                $errors[] = $file->getFilename().': más de 300 líneas';
            }
            foreach (self::functionRanges($source) as [$start, $end]) {
                if ($end - $start + 1 > 50) {
                    $errors[] = $file->getFilename().': función en línea '.$start.' supera 50 líneas';
                }
            }
        }

        return $errors;
    }

    private static function functionRanges(string $source): array
    {
        $tokens = token_get_all($source);
        $line = 1;
        $ranges = [];
        foreach ($tokens as $index => $token) {
            $text = is_array($token) ? $token[1] : $token;
            if (is_array($token) && $token[0] === T_FUNCTION) {
                $end = self::bodyEnd($tokens, $index, $line);
                if ($end !== null) {
                    $ranges[] = [$line, $end];
                }
            }
            $line += substr_count($text, "\n");
        }

        return $ranges;
    }

    private static function bodyEnd(array $tokens, int $start, int $line): ?int
    {
        $depth = 0;
        $opened = false;
        for ($index = $start; $index < count($tokens); $index++) {
            $token = $tokens[$index];
            $text = is_array($token) ? $token[1] : $token;
            if ($token === ';' && ! $opened) {
                return null;
            }
            if ($token === '{') {
                $opened = true;
                $depth++;
            } elseif ($token === '}' && $opened && --$depth === 0) {
                return $line;
            }
            $line += substr_count($text, "\n");
        }

        return null;
    }
}
