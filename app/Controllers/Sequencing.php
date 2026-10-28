<?php

namespace App\Controllers;

use App\Libraries\ListState;
use App\Models\IndividualModel;
use App\Models\LookupModel;
use App\Models\SampleModel;
use App\Models\SequencingModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Port of CI3 controllers/Sequencing.php.
 *
 * Routes: sequencing (list), sequencing/add, sequencing/edit/{id}, POST sequencing/delete/{id}.
 *
 * Not ported: GetsequencingSearch / GetsequencingParentSearch (no page used them and the
 * parent search's model method didn't exist), create, update, create_sequencing_AJAX.
 */
class Sequencing extends CrudController
{
    protected string $module = 'sequencing';

    public function index(): RedirectResponse|string
    {
        $state = $this->listState(array_keys(SequencingModel::SORTS), 'sequencingID', array_keys(SequencingModel::FILTERS));
        if (! $state instanceof ListState) {
            return $state;
        }

        $this->setTitle('Sequencing List');

        return $this->renderList('sequencing/sequencing_table', $state, model(SequencingModel::class)->listAll($state));
    }

    public function add(): RedirectResponse|string
    {
        if ($this->request->is('post') && $this->validate($this->rules())) {
            $id = $this->runSave(fn () => model(SequencingModel::class)->addSequencing($this->request->getPost()));

            return $this->finish($id !== false, "Sequencing {$id} was added.", 'The sequencing could not be added.');
        }

        $this->setTitle('Add Sequencing');

        return $this->render('sequencing/sequencing_form', $this->formData(null));
    }

    public function edit($sequencingID = null): RedirectResponse|string
    {
        $sequencing = self::isId($sequencingID) ? model(SequencingModel::class)->getSequencing((int) $sequencingID) : null;
        if ($sequencing === null) {
            return redirect()->to($this->returnUrl);
        }

        $sequencingID = (int) $sequencingID;

        if ($this->request->is('post') && $this->validate($this->rules())) {
            $ok = $this->runSave(fn () => model(SequencingModel::class)->updateSequencing($sequencingID, $this->request->getPost()));

            return $this->finish((bool) $ok, "Sequencing {$sequencingID} was saved.", "Sequencing {$sequencingID} could not be saved.");
        }

        $this->setTitle('Edit Sequencing');

        return $this->render('sequencing/sequencing_form', $this->formData($sequencing) + ['sequencingID' => $sequencingID]);
    }

    /**
     * CI3's delete() used an undefined variable and never deleted anything.
     */
    public function delete($sequencingID = null): RedirectResponse
    {
        return $this->runDelete($sequencingID, 'Sequencing', static fn (int $id) => model(SequencingModel::class)->deleteSequencing($id));
    }

    private function formData(?array $sequencing): array
    {
        $lookup = model(LookupModel::class);

        $individualID = set_value('individualSearch', (string) ($sequencing['individualID'] ?? ''), false);
        $individual   = self::isId($individualID) ? model(IndividualModel::class)->find((int) $individualID) : null;
        // CI3 preselected the sequencing ID in the Sample box by mistake
        $sampleID = set_value('sampleSearch', (string) ($sequencing['sampleID'] ?? ''), false);

        return [
            'cancel_url'        => $this->returnUrl,
            'sequencing'        => $sequencing,
            'sequencingTypes'   => model(SequencingModel::class)->typeOptions(),
            'sequencingCenters' => $lookup->sequencingCenters(),
            'platforms'         => $lookup->platforms(),
            'libraryPreps'      => $lookup->libraryPreps(),
            'individualID'      => $individual === null ? '' : $individualID,
            'individualName'    => IndividualModel::name($individual),
            'sampleID'          => self::isId($sampleID) ? $sampleID : '',
            'sampleName'        => self::isId($sampleID) ? model(SampleModel::class)->label((int) $sampleID) : '',
        ];
    }

    private function rules(): array
    {
        return [
            'individualSearch' => ['label' => 'Individual', 'rules' => 'required|is_natural_no_zero'],
            'sequencingTypeID' => ['label' => 'Sequencing Type', 'rules' => 'required'],
            'sampleSearch'     => ['label' => 'Sample', 'rules' => 'permit_empty|is_natural_no_zero'],
        ];
    }
}
