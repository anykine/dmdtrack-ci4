<?php

namespace App\Controllers;

use App\Libraries\ListState;
use App\Models\ContactModel;
use App\Models\IndividualModel;
use App\Models\ReferrerModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Port of CI3 controllers/Individual.php.
 *
 * Routes: individual (list; ?cmd=GenMod etc. from the Study menu), individual/add,
 * individual/edit/{id}, POST individual/delete/{id}, and the select2 lookups.
 *
 * Not ported (nothing linked to them): add_all, create, OLDcreate, show_*, the
 * view_*_detailAJAX endpoints and create_individual_AJAX.
 */
class Individual extends CrudController
{
    protected string $module = 'individual';

    public function index(): RedirectResponse|string
    {
        $state = $this->listState(
            array_keys(IndividualModel::SORTS),
            'individualID',
            array_keys(IndividualModel::FILTERS),
            ['cmd' => array_keys(IndividualModel::STUDIES)],
        );
        if (! $state instanceof ListState) {
            return $state;
        }

        $this->setTitle('Individuals List');

        return $this->renderList('individual/individual_table', $state, model(IndividualModel::class)->listAll($state), [
            'study' => $state->extra['cmd'] ?? '',
        ]);
    }

    public function add(): RedirectResponse|string
    {
        if ($this->request->is('post') && $this->validate($this->rules())) {
            $id = $this->runSave(fn () => model(IndividualModel::class)->addIndividual($this->request->getPost()));

            return $this->finish($id !== false, "Individual {$id} was added.", 'The individual could not be added.');
        }

        $this->setTitle('Add Individual');

        return $this->render('individual/individual_form', $this->formData(null));
    }

    public function edit($individualID = null): RedirectResponse|string
    {
        $individual = self::isId($individualID) ? model(IndividualModel::class)->find((int) $individualID) : null;
        if ($individual === null) {
            return redirect()->to($this->returnUrl);
        }

        $individualID = (int) $individualID;

        if ($this->request->is('post') && $this->validate($this->rules())) {
            $ok = $this->runSave(fn () => model(IndividualModel::class)->updateIndividual($individualID, $this->request->getPost()));

            return $this->finish((bool) $ok, "Individual {$individualID} was saved.", "Individual {$individualID} could not be saved.");
        }

        $this->setTitle('Edit Individual');

        return $this->render('individual/individual_form', $this->formData($individual) + ['individualID' => $individualID]);
    }

    /**
     * Fails (with a message) while phenotype, consent, sample or other records still
     * point at the individual.
     */
    public function delete($individualID = null): RedirectResponse
    {
        return $this->runDelete($individualID, 'Individual', static fn (int $id) => model(IndividualModel::class)->deleteIndividual($id));
    }

    /**
     * select2 search: GET individual/GetIndividualSearch?term=...
     */
    public function GetIndividualSearch(): ResponseInterface
    {
        return $this->response->setJSON(model(IndividualModel::class)->search($this->searchTerm()));
    }

    /**
     * select2 prepopulate: GET individual/GetOneIndividualSearch/{id}
     */
    public function GetOneIndividualSearch($individualID = null): ResponseInterface
    {
        return $this->response->setJSON(model(IndividualModel::class)->searchOne((int) $individualID));
    }

    private function formData(?array $individual): array
    {
        $referrerID = set_value('referrerSearch', (string) ($individual['referrerID'] ?? ''), false);
        $contactID  = set_value('contactSearch', (string) ($individual['contactID'] ?? ''), false);

        $referrer = self::isId($referrerID) ? model(ReferrerModel::class)->find((int) $referrerID) : null;
        $contact  = self::isId($contactID) ? model(ContactModel::class)->find((int) $contactID) : null;

        return [
            'cancel_url'   => $this->returnUrl,
            'individual'   => $individual,
            'referrerID'   => $referrer === null ? '' : $referrerID,
            'referrerName' => ReferrerModel::label($referrer),
            'contactID'    => $contact === null ? '' : $contactID,
            'contactName'  => ContactModel::label($contact),
        ];
    }

    private function rules(): array
    {
        return [
            'individualFirstName' => ['label' => 'First Name', 'rules' => 'permit_empty|min_length[2]|max_length[32]'],
            'individualLastName'  => ['label' => 'Last Name', 'rules' => 'permit_empty|min_length[2]|max_length[32]'],
            'referrerSearch'      => ['label' => 'Referrer', 'rules' => 'permit_empty|is_natural_no_zero'],
            'contactSearch'       => ['label' => 'Contact', 'rules' => 'permit_empty|is_natural_no_zero'],
        ];
    }
}
