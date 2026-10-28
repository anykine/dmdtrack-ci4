<?php

namespace App\Controllers;

use App\Libraries\ListState;
use App\Models\ConsentModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Port of CI3 controllers/Consent.php.
 *
 * Routes: consent (list), consent/add, consent/edit/{id}, POST consent/delete/{id},
 * consent/GetConsentSearch (select2 consent types).
 */
class Consent extends CrudController
{
    protected string $module = 'consent';

    public function index(): RedirectResponse|string
    {
        $state = $this->listState(array_keys(ConsentModel::SORTS), 'consentID', array_keys(ConsentModel::FILTERS));
        if (! $state instanceof ListState) {
            return $state;
        }

        $this->setTitle('Consent List');

        return $this->renderList('consent/consent_table', $state, model(ConsentModel::class)->listAll($state));
    }

    public function add(): RedirectResponse|string
    {
        if ($this->request->is('post') && $this->validate($this->rules())) {
            $id = $this->runSave(fn () => model(ConsentModel::class)->addConsent($this->request->getPost()));

            return $this->finish($id !== false, "Consent {$id} was added.", 'The consent could not be added.');
        }

        $this->setTitle('Add Consent');

        return $this->render('consent/consent_form', ['cancel_url' => $this->returnUrl, 'consent' => null]);
    }

    public function edit($consentID = null): RedirectResponse|string
    {
        $consent = self::isId($consentID) ? model(ConsentModel::class)->getConsent((int) $consentID) : null;
        if ($consent === null) {
            return redirect()->to($this->returnUrl);
        }

        $consentID = (int) $consentID;

        if ($this->request->is('post') && $this->validate($this->rules())) {
            $ok = $this->runSave(fn () => model(ConsentModel::class)->updateConsent($consentID, $this->request->getPost()));

            return $this->finish((bool) $ok, "Consent {$consentID} was saved.", "Consent {$consentID} could not be saved.");
        }

        $this->setTitle('Edit Consent');

        return $this->render('consent/consent_form', [
            'cancel_url' => $this->returnUrl,
            'consent'    => $consent,
            'consentID'  => $consentID,
        ]);
    }

    /**
     * CI3's delete() used an undefined variable and never deleted anything.
     */
    public function delete($consentID = null): RedirectResponse
    {
        return $this->runDelete($consentID, 'Consent', static fn (int $id) => model(ConsentModel::class)->deleteConsent($id));
    }

    public function GetConsentSearch(): ResponseInterface
    {
        return $this->response->setJSON(model(ConsentModel::class)->searchTypes($this->searchTerm()));
    }

    private function rules(): array
    {
        return [
            'individualSearch'  => ['label' => 'Individual', 'rules' => 'required|is_natural_no_zero'],
            'consentTypeSearch' => ['label' => 'Consent Type', 'rules' => 'required|is_natural_no_zero'],
        ];
    }
}
