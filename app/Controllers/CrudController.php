<?php

namespace App\Controllers;

use App\Libraries\ListState;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Shared code for the data modules (list with sort/filter/pages, add, edit, delete).
 *
 * In CI3 every module controller repeated this: the DEFAULT_* constants, the filter
 * string building, the "return to the list" URL in the session and the pagination setup.
 */
abstract class CrudController extends BaseController
{
    /** URL segment and session key, e.g. 'consent'. */
    protected string $module = '';

    /** The plain list URL. */
    protected string $listUrl;

    /** The list page (sort/filter/page) the user came from; add/edit/delete return here. */
    protected string $returnUrl;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->listUrl   = site_url($this->module);
        $this->returnUrl = $this->session->get($this->module) ?: $this->listUrl;
    }

    /**
     * Read the list state from the URL. A POST (the filter form) is turned into a GET URL
     * and redirected, so filtered lists can be bookmarked (CI3 did the same).
     *
     * @param list<string>                $sortable
     * @param list<string>                $filterable
     * @param array<string, list<string>> $extra parameter => allowed values
     */
    protected function listState(array $sortable, string $defaultSort, array $filterable = [], array $extra = []): ListState|RedirectResponse
    {
        $state = ListState::fromInput(
            $this->request->getGet() ?? [],
            $this->listUrl,
            (int) ($this->settings->per_page_limit ?? 25),
            $sortable,
            $defaultSort,
            $filterable,
            $extra,
        );

        if ($this->request->is('post')) {
            if ($this->request->getPost('clear')) {
                return redirect()->to($this->listUrl);
            }

            $filters = ListState::filtersFrom($this->request->getPost(), $filterable);

            return redirect()->to($state->withFilters($filters)->url());
        }

        $this->session->set($this->module, $state->currentUrl());

        return $state;
    }

    /**
     * Render a list view with the data every list view uses.
     *
     * @param array{results: list<array<string, mixed>>, total: int} $list
     */
    protected function renderList(string $view, ListState $state, array $list, array $data = []): string
    {
        return $this->render($view, $data + [
            'state'      => $state,
            'rows'       => $list['results'],
            'total'      => $list['total'],
            'pagination' => service('pager')->makeLinks($state->page, $state->limit, $list['total'], 'default_full'),
        ]);
    }

    /**
     * Flash a success or failure message and go back to the list.
     */
    protected function finish(bool $ok, string $okMessage, string $failMessage): RedirectResponse
    {
        $this->session->setFlashdata($ok ? 'message' : 'error', $ok ? $okMessage : $failMessage);

        return redirect()->to($this->returnUrl);
    }

    /**
     * Run a delete. Linked records (foreign keys) make the database refuse; say so
     * instead of showing an error page.
     *
     * @param callable(int): bool $delete
     */
    protected function runDelete(mixed $id, string $label, callable $delete): RedirectResponse
    {
        if (! self::isId($id)) {
            return redirect()->to($this->returnUrl);
        }

        try {
            $ok = (bool) $delete((int) $id);
        } catch (DatabaseException $e) {
            log_message('error', "Delete {$label} {$id} failed: " . $e->getMessage());

            return $this->finish(false, '', "{$label} {$id} could not be deleted. It is probably still linked to other records; remove those first.");
        } catch (Throwable $e) {
            log_message('error', "Delete {$label} {$id} failed: " . $e->getMessage());
            $ok = false;
        }

        return $this->finish($ok, "{$label} {$id} was deleted.", "{$label} {$id} could not be deleted.");
    }

    /**
     * Run an insert/update and catch database errors so the user sees a message.
     *
     * @param callable(): (bool|int) $save
     */
    protected function runSave(callable $save): bool|int
    {
        try {
            return $save();
        } catch (DatabaseException $e) {
            log_message('error', "Save in {$this->module} failed: " . $e->getMessage());

            return false;
        }
    }

    protected static function isId(mixed $id): bool
    {
        return ctype_digit((string) $id) && (int) $id > 0;
    }

    /**
     * select2 search endpoint body: GET ?term=...
     */
    protected function searchTerm(): string
    {
        return trim((string) ($this->request->getGet('term') ?? ''));
    }
}
