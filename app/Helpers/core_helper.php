<?php

/**
 * Port of CI3 application/helpers/core_helper.php.
 * Only what the ported modules use so far; copy the rest (display_json, json_indent,
 * array_to_csv, generate_random_password, array_sort_by_column) as modules need them.
 */

use Config\Site;

if (! function_exists('get_languages')) {
    /**
     * Available languages, e.g. ['english' => 'English', 'spanish' => 'Spanish'].
     * CI3 scanned application/language/; CI4 takes them from Config\Site::$languages.
     *
     * @return array<string, string>
     */
    function get_languages(): array
    {
        $languages = [];

        foreach (array_keys(config(Site::class)->languages) as $name) {
            $languages[$name] = ucwords(str_replace(['-', '_'], ' ', $name));
        }

        return $languages;
    }
}

if (! function_exists('lang_ci3')) {
    /**
     * Look up a CI3-style key ('core button save') in its CI4 file ('Core').
     * Used by Jsi18n for the {{...}} placeholders in theme JS files.
     */
    function lang_ci3(string $key): string
    {
        $file = ucfirst((string) strtok($key, ' '));

        return lang("{$file}.{$key}");
    }
}
