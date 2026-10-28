<?php

namespace App\Controllers;

use App\Models\IndividualModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PARTIAL port of CI3 controllers/Individual.php: only the select2 lookups that the
 * Phenotype form needs. The rest of the module comes over when Individual is ported.
 */
class Individual extends BaseController
{
    /**
     * select2 search: GET individual/GetIndividualSearch?term=...
     */
    public function GetIndividualSearch(): ResponseInterface
    {
        $term = (string) ($this->request->getGet('term') ?? '');

        return $this->response->setJSON(model(IndividualModel::class)->search($term));
    }

    /**
     * select2 prepopulate: GET individual/GetOneIndividualSearch/{id}
     */
    public function GetOneIndividualSearch($individualID = null): ResponseInterface
    {
        return $this->response->setJSON(model(IndividualModel::class)->searchOne((int) $individualID));
    }
}
