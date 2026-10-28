<?php

namespace App\Controllers;

use App\Models\ConsentModel;
use App\Models\ContactModel;
use App\Models\IndividualModel;
use App\Models\PhenotypeModel;
use App\Models\ReferrerModel;
use App\Models\ResultModel;
use App\Models\SampleModel;
use App\Models\SequencingModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Port of CI3 controllers/IndivDetail.php: everything about one individual on one page.
 *
 * Route: indivDetail/view/{individualID}
 */
class IndivDetail extends BaseController
{
    /**
     * CI3 printed "test" here.
     */
    public function index(): RedirectResponse
    {
        return redirect()->to(site_url('individual'));
    }

    public function view($individualID = null): RedirectResponse|string
    {
        $individual = ctype_digit((string) $individualID) ? model(IndividualModel::class)->find((int) $individualID) : null;
        if ($individual === null) {
            $this->session->setFlashdata('error', 'That individual does not exist.');

            return redirect()->to(site_url('individual'));
        }

        $individualID = (int) $individualID;

        $this->setTitle('Individual Detail');

        return $this->render('individual/detail', [
            'individual'  => $individual,
            'family'      => model(IndividualModel::class)->family($individualID),
            'contact'     => $individual['contactID'] ? model(ContactModel::class)->find((int) $individual['contactID']) : null,
            // CI3 looked the referrer up by individualID by mistake
            'referrer'    => $individual['referrerID'] ? model(ReferrerModel::class)->find((int) $individual['referrerID']) : null,
            'phenotype'   => model(PhenotypeModel::class)->findByIndividual($individualID),
            'consents'    => model(ConsentModel::class)->forIndividual($individualID),
            'samples'     => model(SampleModel::class)->forIndividual($individualID),
            'sequencings' => model(SequencingModel::class)->forIndividual($individualID),
            'results'     => model(ResultModel::class)->forIndividual($individualID),
        ]);
    }
}
