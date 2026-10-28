<?php

namespace App\Libraries;

/**
 * Port of CI3 libraries/Jsi18n.php: replaces {{lang key}} in a JS file with the translated text.
 *
 *   alert("{{core error session_language}}");
 */
class Jsi18n
{
    /**
     * @param string|null $file path relative to public/ (FCPATH), e.g. "assets/themes/core/js/core_i18n.js"
     */
    public function translate(?string $file): ?string
    {
        if ($file === null || $file === '') {
            return null;
        }

        $path = FCPATH . ltrim($file, '/');

        if (! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false || $contents === '') {
            return null;
        }

        helper('core');

        return preg_replace_callback('/\{\{(.*?)\}\}/', static fn (array $m): string => lang_ci3($m[1]), $contents);
    }
}
