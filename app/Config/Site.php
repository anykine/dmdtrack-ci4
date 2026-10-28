<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Site settings carried over from CI3 application/config/core.php and timezones.php.
 * Settings editable by admins still live in the `settings` table (see SettingsModel).
 */
class Site extends BaseConfig
{
    public string $siteVersion   = "0.0.1";
    public string $themesFolder  = "assets/themes"; // under public/
    public string $publicTheme   = "default";
    public string $adminTheme    = "admin";
    public string $captchaFolder = "assets/captcha";

    /**
     * Self-registration (user/register). CI3 had it always on: anyone who could reach the
     * site could create an account and, after clicking the emailed link, see all records.
     * Off by default; admins add users instead.
     */
    public bool $allowRegistration = false;

    // Login throttling
    public int $loginMaxTime     = 10; // seconds
    public int $loginMaxAttempts = 3;

    /**
     * CI3 language name (stored in sessions and users.language) => CI4 locale.
     * Add new locales to Config\App::$supportedLocales too.
     *
     * @var array<string, string>
     */
    public array $languages = [
        "english" => "en",
        "spanish" => "es",
    ];

    public string $defaultLanguage = "english";

    /**
     * Timezone codes stored in the `timezones` setting => PHP timezone.
     *
     * @var array<string, string>
     */
    public array $timezones = [
        'UM12' => 'Pacific/Kwajalein',
        'UM11' => 'Pacific/Midway',
        'UM10' => 'Pacific/Honolulu',
        'UM95' => 'Pacific/Marquesas',
        'UM9' => 'America/Anchorage',
        'UM8' => 'America/Los_Angeles',
        'UM75' => 'America/Phoenix',
        'UM7' => 'America/Denver',
        'UM6' => 'America/Chicago',
        'UM5' => 'America/New_York',
        'UM45' => 'America/Caracas',
        'UM4' => 'America/Halifax',
        'UM35' => 'America/St_Johns',
        'UM3' => 'America/Godthab',
        'UM2' => 'Atlantic/South_Georgia',
        'UM1' => 'Atlantic/Azores',
        'UTC' => 'Europe/London',
        'UP1' => 'Europe/Prague',
        'UP2' => 'Europe/Athens',
        'UP3' => 'Asia/Baghdad',
        'UP35' => 'Asia/Tehran',
        'UP4' => 'Asia/Baku',
        'UP45' => 'Asia/Kabul',
        'UP5' => 'Asia/Karachi',
        'UP55' => 'Asia/Kolkata',
        'UP575' => 'Asia/Katmandu',
        'UP6' => 'Asia/Dhaka',
        'UP65' => 'Asia/Rangoon',
        'UP7' => 'Asia/Bangkok',
        'UP8' => 'Asia/Hong_Kong',
        'UP875' => 'Australia/West',
        'UP9' => 'Asia/Tokyo',
        'UP95' => 'Australia/Darwin',
        'UP10' => 'Australia/Sydney',
        'UP105' => 'Australia/Lord_Howe',
        'UP11' => 'Asia/Magadan',
        'UP115' => 'Pacific/Norfolk',
        'UP12' => 'Pacific/Fiji',
        'UP1275' => 'Pacific/Chatham',
        'UP13' => 'Pacific/Tongatapu',
        'UP14' => 'Pacific/Kiritimati',
    ];
}
