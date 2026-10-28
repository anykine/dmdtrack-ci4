<?php

namespace App\Controllers;

use App\Libraries\ListState;
use App\Libraries\UsStates;
use App\Models\ContactModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Port of CI3 controllers/Contact.php (the patient's contact person).
 *
 * Routes: contact (list), contact/add, contact/edit/{id}, POST contact/delete/{id},
 * contact/GetContactSearch (select2).
 */
class Contact extends CrudController
{
    protected string $module = 'contact';

    public function index(): RedirectResponse|string
    {
        $state = $this->listState(array_keys(ContactModel::SORTS), 'contactID', array_keys(ContactModel::FILTERS));
        if (! $state instanceof ListState) {
            return $state;
        }

        $this->setTitle('Contact List');

        return $this->renderList('contact/contact_table', $state, model(ContactModel::class)->listAll($state));
    }

    public function add(): RedirectResponse|string
    {
        if ($this->request->is('post') && $this->validate($this->rules())) {
            $id = $this->runSave(fn () => model(ContactModel::class)->addContact($this->request->getPost()));

            return $this->finish($id !== false, "Contact {$id} was added.", 'The contact could not be added.');
        }

        $this->setTitle('Add Contact');

        return $this->render('contact/contact_form', $this->formData(null));
    }

    public function edit($contactID = null): RedirectResponse|string
    {
        $contact = self::isId($contactID) ? model(ContactModel::class)->find((int) $contactID) : null;
        if ($contact === null) {
            return redirect()->to($this->returnUrl);
        }

        $contactID = (int) $contactID;

        if ($this->request->is('post') && $this->validate($this->rules())) {
            $ok = $this->runSave(fn () => model(ContactModel::class)->updateContact($contactID, $this->request->getPost()));

            return $this->finish((bool) $ok, "Contact {$contactID} was saved.", "Contact {$contactID} could not be saved.");
        }

        $this->setTitle('Edit Contact');

        return $this->render('contact/contact_form', $this->formData($contact) + ['contactID' => $contactID]);
    }

    /**
     * Fails (with a message) while an individual still points at this contact.
     */
    public function delete($contactID = null): RedirectResponse
    {
        return $this->runDelete($contactID, 'Contact', static fn (int $id) => model(ContactModel::class)->delete($id));
    }

    public function GetContactSearch(): ResponseInterface
    {
        return $this->response->setJSON(model(ContactModel::class)->search($this->searchTerm()));
    }

    private function formData(?array $contact): array
    {
        return [
            'cancel_url'    => $this->returnUrl,
            'contact'       => $contact,
            'relationships' => ContactModel::RELATIONSHIPS,
            'states'        => UsStates::LIST,
        ];
    }

    /**
     * CI3 only trimmed the names; every field is optional.
     */
    private function rules(): array
    {
        return [
            'contactFirstName' => ['label' => 'First Name', 'rules' => 'permit_empty|max_length[255]'],
            'contactLastName'  => ['label' => 'Last Name', 'rules' => 'permit_empty|max_length[255]'],
            'contactEmail1'    => ['label' => 'Email', 'rules' => 'permit_empty|max_length[255]'],
        ];
    }
}
