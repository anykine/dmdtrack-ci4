<?php

namespace App\Controllers;

use App\Models\PhenotypeModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Port of CI3 controllers/Phenotype.php. Login is enforced by AuthFilter (see Config/Routes.php).
 *
 * Routes:
 *   GET|POST phenotype           list (POST = apply filters)
 *   GET|POST phenotype/add       add form
 *   GET|POST phenotype/edit/{id} edit form
 *   POST     phenotype/delete/{id}
 */
class Phenotype extends BaseController
{
    /** Session key holding the last list URL (sort/filter/page), used to return after add/edit. */
    private const RETURN_KEY = 'phenotype';

    private PhenotypeModel $phenotypes;
    private string $listUrl;
    private string $returnUrl;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->phenotypes = model(PhenotypeModel::class);
        $this->listUrl    = site_url('phenotype');
        $this->returnUrl  = $this->session->get(self::RETURN_KEY) ?: $this->listUrl;
    }

    /**
     * List with sorting, filters and pagination.
     */
    public function index(): RedirectResponse|string
    {
        $limit = (int) ($this->request->getGet('limit') ?: ($this->settings->per_page_limit ?? 25));
        $limit = min(max($limit, 1), 500);
        $page  = max(1, (int) ($this->request->getGet('page') ?: 1));
        $sort  = (string) ($this->request->getGet('sort') ?: 'phenotypeID');
        $sort  = in_array($sort, PhenotypeModel::SORTABLE, true) ? $sort : 'phenotypeID';
        $dir   = $this->request->getGet('dir') === 'desc' ? 'desc' : 'asc';

        $filters = $this->filtersFrom($this->request->getGet());

        // Filter form posts here; turn it into a GET URL so the list can be bookmarked
        if ($this->request->is('post')) {
            if ($this->request->getPost('clear')) {
                return redirect()->to($this->listUrl);
            }

            $query = ['sort' => $sort, 'dir' => $dir, 'limit' => $limit] + $this->filtersFrom($this->request->getPost());

            return redirect()->to($this->listUrl . '?' . http_build_query($query));
        }

        // sort/dir/limit/filters, without page; the view adds page or a new sort
        $query = ['sort' => $sort, 'dir' => $dir, 'limit' => $limit] + $filters;

        $this->session->set(self::RETURN_KEY, $this->listUrl . '?' . http_build_query($query + ['page' => $page]));

        $list = $this->phenotypes->listAll($limit, ($page - 1) * $limit, $filters, $sort, $dir);

        $this->setTitle('Phenotype List');

        return $this->render('phenotype/phenotype_table', [
            'this_url'   => $this->listUrl,
            'phenotypes' => $list['results'],
            'total'      => $list['total'],
            'filters'    => $filters,
            'query'      => $query,
            'pagination' => service('pager')->makeLinks($page, $limit, $list['total'], 'default_full'),
            'limit'      => $limit,
            'sort'       => $sort,
            'dir'        => $dir,
        ]);
    }

    public function add(): RedirectResponse|string
    {
        if ($this->request->is('post') && $this->validate($this->rules())) {
            $saved = $this->phenotypes->savePhenotype($this->request->getPost());

            $saved !== false
                ? $this->session->setFlashdata('message', "Phenotype {$saved} was added.")
                : $this->session->setFlashdata('error', 'The phenotype could not be added.');

            return redirect()->to($this->returnUrl);
        }

        $this->setTitle('Add Phenotype');

        return $this->render('phenotype/phenotype_form', [
            'cancel_url' => $this->returnUrl,
            'yesNoArray' => [0 => 'No', 1 => 'Yes'],
            'phenotype'  => null,
        ]);
    }

    public function edit($phenotypeID = null): RedirectResponse|string
    {
        if (! ctype_digit((string) $phenotypeID)) {
            return redirect()->to($this->returnUrl);
        }

        $phenotypeID = (int) $phenotypeID;
        $individual  = $this->phenotypes->getIndividual($phenotypeID);

        if ($individual === null) {
            return redirect()->to($this->returnUrl);
        }

        if ($this->request->is('post') && $this->validate($this->rules())) {
            $saved = $this->phenotypes->savePhenotype($this->request->getPost(), $phenotypeID);

            $saved !== false
                ? $this->session->setFlashdata('message', "Phenotype {$phenotypeID} was saved.")
                : $this->session->setFlashdata('error', "Phenotype {$phenotypeID} could not be saved.");

            return redirect()->to($this->returnUrl);
        }

        $this->setTitle('Edit Phenotype');

        return $this->render('phenotype/phenotype_form', [
            'cancel_url'         => $this->returnUrl,
            'phenotype'          => $this->phenotypes->find($phenotypeID),
            'phenotypeID'        => $phenotypeID,
            'yesNoArray'         => [0 => 'No', 1 => 'Yes'],
            'dataIndividualID'   => $individual['individualID'],
            'dataIndividualName' => $individual['individualFirstName'] . $individual['individualLastName'],
        ]);
    }

    /**
     * CI3 had a delete() that never worked (it deleted an undefined id); this one does.
     */
    public function delete($phenotypeID = null): RedirectResponse
    {
        if (ctype_digit((string) $phenotypeID) && $this->phenotypes->delete((int) $phenotypeID)) {
            $this->session->setFlashdata('message', "Phenotype {$phenotypeID} was deleted.");
        } else {
            $this->session->setFlashdata('error', 'The phenotype could not be deleted.');
        }

        return redirect()->to($this->returnUrl);
    }

    /**
     * @return array<string, array{label: string, rules: string}>
     */
    private function rules(): array
    {
        return [
            // CI3 only required this on edit; on add a missing individual failed at the database
            'individualSearch' => ['label' => 'Individual', 'rules' => 'required|is_natural_no_zero'],
        ];
    }

    /**
     * Non-empty filter values from GET or POST data.
     *
     * @return array<string, string>
     */
    private function filtersFrom(?array $input): array
    {
        $filters = [];

        foreach (PhenotypeModel::FILTERABLE as $field) {
            $value = trim((string) ($input[$field] ?? ''));
            if ($value !== '') {
                $filters[$field] = $value;
            }
        }

        return $filters;
    }
}
