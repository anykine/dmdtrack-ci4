<?php

namespace App\Controllers;

use App\Libraries\ListState;
use App\Libraries\UsStates;
use App\Models\ReferrerModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Port of CI3 controllers/Referrer.php.
 *
 * Routes: referrer (list), referrer/add, referrer/edit/{id}, POST referrer/delete/{id},
 * referrer/GetReferrerSearch (select2).
 */
class Referrer extends CrudController
{
    protected string $module = 'referrer';

    public function index(): RedirectResponse|string
    {
        $state = $this->listState(array_keys(ReferrerModel::SORTS), 'referrerID', array_keys(ReferrerModel::FILTERS));
        if (! $state instanceof ListState) {
            return $state;
        }

        $this->setTitle('Referrer List');

        return $this->renderList('referrer/referrer_table', $state, model(ReferrerModel::class)->listAll($state));
    }

    public function add(): RedirectResponse|string
    {
        if ($this->request->is('post') && $this->validate($this->rules())) {
            $id = $this->runSave(fn () => model(ReferrerModel::class)->addReferrer($this->request->getPost()));

            return $this->finish($id !== false, "Referrer {$id} was added.", 'The referrer could not be added.');
        }

        $this->setTitle('Add Referrer');

        return $this->render('referrer/referrer_form', ['cancel_url' => $this->returnUrl, 'referrer' => null, 'states' => UsStates::LIST]);
    }

    public function edit($referrerID = null): RedirectResponse|string
    {
        $referrer = self::isId($referrerID) ? model(ReferrerModel::class)->find((int) $referrerID) : null;
        if ($referrer === null) {
            return redirect()->to($this->returnUrl);
        }

        $referrerID = (int) $referrerID;

        if ($this->request->is('post') && $this->validate($this->rules())) {
            $ok = $this->runSave(fn () => model(ReferrerModel::class)->updateReferrer($referrerID, $this->request->getPost()));

            return $this->finish((bool) $ok, "Referrer {$referrerID} was saved.", "Referrer {$referrerID} could not be saved.");
        }

        $this->setTitle('Edit Referrer');

        return $this->render('referrer/referrer_form', [
            'cancel_url' => $this->returnUrl,
            'referrer'   => $referrer,
            'referrerID' => $referrerID,
            'states'     => UsStates::LIST,
        ]);
    }

    /**
     * Fails (with a message) while an individual still points at this referrer.
     */
    public function delete($referrerID = null): RedirectResponse
    {
        return $this->runDelete($referrerID, 'Referrer', static fn (int $id) => model(ReferrerModel::class)->delete($id));
    }

    public function GetReferrerSearch(): ResponseInterface
    {
        return $this->response->setJSON(model(ReferrerModel::class)->search($this->searchTerm()));
    }

    private function rules(): array
    {
        return [
            'referrerInstitution' => ['label' => 'Institution', 'rules' => 'permit_empty|max_length[255]'],
            'referrerFirstName'   => ['label' => 'First Name', 'rules' => 'required|min_length[2]|max_length[32]'],
            'referrerLastName'    => ['label' => 'Last Name', 'rules' => 'required|min_length[2]|max_length[32]'],
        ];
    }
}
