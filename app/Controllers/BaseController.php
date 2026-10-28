<?php

namespace App\Controllers;

use App\Libraries\Jsi18n;
use App\Models\SettingsModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\Session;
use Config\Site;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Port of CI3 core/MY_Controller.php (+ the theme part of Public_/Private_Controller).
 *
 * Login checks moved to App\Filters\AuthFilter (applied per route group in Config/Routes.php).
 * Controllers build a page with setTitle()/addCssTheme()/... and finish with
 * `return $this->render('module/view', $data);` instead of $this->load->view($this->template, $data).
 */
abstract class BaseController extends Controller
{
    /**
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * @var list<string>
     */
    protected $helpers = ['url', 'form', 'core'];

    protected Session $session;
    protected Site $site;

    /** Rows of the `settings` table as properties (site_name, per_page_limit, ...). */
    protected stdClass $settings;

    /** Logged-in user row (without password/salt) or null. */
    protected ?array $user = null;

    /** @var array<string, string> e.g. ['english' => 'English'] */
    protected array $languages = [];

    /** CI3 language name in use, e.g. 'english'. */
    protected string $language = 'english';

    /** CSS/JS/title data handed to the theme template. */
    protected array $includes = [];

    /** Theme folder under assets/themes and app/Views/themes. Admin controllers set 'admin'. */
    protected string $theme = '';

    /** Template file inside app/Views/themes/{theme}/. */
    protected string $template = 'template';

    /** Extra error message shown above the content. */
    protected string $error = '';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session = service('session');
        $this->site    = config(Site::class);

        $this->loadSettings();

        $timezone = $this->site->timezones[$this->settings->timezones ?? ''] ?? null;
        if ($timezone !== null) {
            date_default_timezone_set($timezone);
        }

        $this->user      = $this->session->get('logged_in');
        $this->languages = get_languages();
        $this->setLanguage();

        if ($this->theme === '') {
            $this->theme = $this->site->publicTheme;
        }

        $themes = $this->site->themesFolder;

        $this
            ->addExternalCss([
                '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
                '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css',
                '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
                base_url("{$themes}/core/css/core.css"),
                '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css',
            ])
            ->addExternalJs([
                '//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js',
                '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
                '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js',
                // one file for every environment; URLs come from config.baseURL (core_i18n.js)
                base_url("{$themes}/default/js/dmdtrack.js"),
            ]);

        $coreJs = (new Jsi18n())->translate("{$themes}/core/js/core_i18n.js");
        $this->includes['js_files_i18n']['core'] = str_replace('<<base_url>>', rtrim(base_url(), '/') . '/', (string) $coreJs);

        $this
            ->addCssTheme("{$this->theme}.css")
            ->addJsTheme("{$this->theme}_i18n.js", true);
    }

    /**
     * Render a content view inside the theme template (CI3: two load->view calls).
     */
    protected function render(string $view, array $data = []): string
    {
        $page = array_merge(
            [
                'page_title'  => '',
                'page_header' => '',
                'css_files'   => [],
                'js_files'    => [],
                'js_files_i18n' => [],
            ],
            $this->includes,
            [
                'settings'  => $this->settings,
                'user'      => $this->user,
                'languages' => $this->languages,
                'language'  => $this->language,
                'error'     => $this->error,
                'site'      => $this->site,
            ]
        );

        $page['content'] = view($view, $data);

        return view("themes/{$this->theme}/{$this->template}", $page);
    }

    protected function setTitle(string $pageTitle): static
    {
        $this->includes['page_title']  = $pageTitle;
        $this->includes['page_header'] ??= $pageTitle;

        return $this;
    }

    protected function setPageHeader(string $pageHeader): static
    {
        $this->includes['page_header'] = $pageHeader;

        return $this;
    }

    protected function setTemplate(string $template = 'template'): static
    {
        $this->template = preg_replace('/\.php$/', '', $template);

        return $this;
    }

    /**
     * @param list<string>|string $files full URLs, or file names when $path is given
     */
    protected function addExternalCss(array|string $files, ?string $path = null): static
    {
        foreach ($this->fileList($files) as $css) {
            $this->includes['css_files'][sha1($css)] = $path === null ? $css : $path . $css;
        }

        return $this;
    }

    /**
     * @param list<string>|string $files
     */
    protected function addExternalJs(array|string $files, ?string $path = null): static
    {
        foreach ($this->fileList($files) as $js) {
            $this->includes['js_files'][sha1($js)] = $path === null ? $js : $path . $js;
        }

        return $this;
    }

    /**
     * CSS file from the active theme's css/ folder.
     *
     * @param list<string>|string $files
     */
    protected function addCssTheme(array|string $files): static
    {
        foreach ($this->fileList($files) as $css) {
            $this->includes['css_files'][sha1($css)] = base_url("{$this->site->themesFolder}/{$this->theme}/css/{$css}");
        }

        return $this;
    }

    /**
     * JS file from the active theme's js/ folder. With $i18n, {{lang key}} placeholders
     * are translated and the script is inlined.
     *
     * @param list<string>|string $files
     */
    protected function addJsTheme(array|string $files, bool $i18n = false): static
    {
        if ($i18n) {
            return $this->addJsi18nTheme($files);
        }

        foreach ($this->fileList($files) as $js) {
            $this->includes['js_files'][sha1($js)] = base_url("{$this->site->themesFolder}/{$this->theme}/js/{$js}");
        }

        return $this;
    }

    /**
     * @param list<string>|string $files
     */
    protected function addJsi18nTheme(array|string $files): static
    {
        $jsi18n = new Jsi18n();

        foreach ($this->fileList($files) as $js) {
            $this->includes['js_files_i18n'][sha1($js)] = $jsi18n->translate("{$this->site->themesFolder}/{$this->theme}/js/{$js}");
        }

        return $this;
    }

    /**
     * @param list<string>|string $files comma-separated string or array
     *
     * @return list<string>
     */
    private function fileList(array|string $files): array
    {
        $files = is_array($files) ? $files : explode(',', $files);

        return array_values(array_filter(array_map('trim', $files), static fn ($f) => $f !== ''));
    }

    private function loadSettings(): void
    {
        $this->settings = new stdClass();

        foreach (model(SettingsModel::class)->getSettings() as $row) {
            // Some settings (e.g. welcome_message per language) are stored serialized
            $value = @unserialize((string) $row['value'], ['allowed_classes' => false]);
            $this->settings->{$row['name']} = $value !== false ? $value : $row['value'];
        }
    }

    /**
     * Language priority (same as CI3): session choice, then the user's saved language, then the default.
     */
    private function setLanguage(): void
    {
        $candidates = [
            $this->session->get('language'),
            $this->user['language'] ?? null,
            $this->site->defaultLanguage,
        ];

        foreach ($candidates as $language) {
            if (is_string($language) && isset($this->site->languages[$language])) {
                $this->language = $language;
                break;
            }
        }

        $this->session->set('language', $this->language);

        $locale = $this->site->languages[$this->language];
        if ($this->request instanceof IncomingRequest) {
            $this->request->setLocale($locale);
        }
        service('language')->setLocale($locale);
    }
}
