<?php

namespace App\Controllers;

use App\Libraries\ListState;
use App\Models\IndividualModel;
use App\Models\LookupModel;
use App\Models\SampleModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Port of CI3 controllers/Sample.php.
 *
 * Routes: sample (list), sample/add, sample/edit/{id}, POST sample/delete/{id},
 * sample/GetSampleSearch (select2, also used by Result and Sequencing).
 *
 * Not ported (nothing linked to them): view2, OLDviewQueued, create, update,
 * create_sample_AJAX, GetSampleParentSearch (its model method didn't exist).
 */
class Sample extends CrudController
{
    protected string $module = 'sample';

    public function index(): RedirectResponse|string
    {
        $state = $this->listState(array_keys(SampleModel::SORTS), 'sampleID', array_keys(SampleModel::FILTERS));
        if (! $state instanceof ListState) {
            return $state;
        }

        $this->setTitle('Sample List');

        return $this->renderList('sample/sample_table', $state, model(SampleModel::class)->listAll($state));
    }

    public function add(): RedirectResponse|string
    {
        if ($this->request->is('post') && $this->validate($this->rules())) {
            $id = $this->runSave(fn () => model(SampleModel::class)->addSample($this->request->getPost()));

            return $this->finish($id !== false, "Sample {$id} was added.", 'The sample could not be added.');
        }

        $this->setTitle('Add Sample');

        return $this->render('sample/sample_form', $this->formData(null));
    }

    public function edit($sampleID = null): RedirectResponse|string
    {
        $sample = self::isId($sampleID) ? model(SampleModel::class)->getSample((int) $sampleID) : null;
        if ($sample === null) {
            return redirect()->to($this->returnUrl);
        }

        $sampleID = (int) $sampleID;

        if ($this->request->is('post') && $this->validate($this->rules())) {
            $ok = $this->runSave(fn () => model(SampleModel::class)->updateSample($sampleID, $this->request->getPost()));

            return $this->finish((bool) $ok, "Sample {$sampleID} was saved.", "Sample {$sampleID} could not be saved.");
        }

        $this->setTitle('Edit Sample');

        return $this->render('sample/sample_form', $this->formData($sample) + ['sampleID' => $sampleID]);
    }

    /**
     * CI3's delete() used an undefined variable and never deleted anything.
     */
    public function delete($sampleID = null): RedirectResponse
    {
        return $this->runDelete($sampleID, 'Sample', static fn (int $id) => model(SampleModel::class)->deleteSample($id));
    }

    public function GetSampleSearch(): ResponseInterface
    {
        return $this->response->setJSON(model(SampleModel::class)->search($this->searchTerm()));
    }

    private function formData(?array $sample): array
    {
        $samples = model(SampleModel::class);

        $individualID = set_value('individualSearch', (string) ($sample['individualID'] ?? ''), false);
        $individual   = self::isId($individualID) ? model(IndividualModel::class)->find((int) $individualID) : null;
        $parentID     = set_value('sampleParentSearch', (string) ($sample['sampleParentID'] ?? ''), false);

        return [
            'cancel_url'            => $this->returnUrl,
            'sample'                => $sample,
            'sampleTypes'           => $samples->typeOptions(),
            'sampleActions'         => $samples->actionOptions(),
            'extractionTypes'       => $samples->extractionTypeOptions(),
            'sequencingCenters'     => model(LookupModel::class)->sequencingCenters(),
            'individualID'          => $individual === null ? '' : $individualID,
            'individualName'        => IndividualModel::name($individual),
            'parentID'              => self::isId($parentID) ? $parentID : '',
            'parentName'            => self::isId($parentID) ? $samples->label((int) $parentID) : '',
        ];
    }

    private function rules(): array
    {
        return [
            'individualSearch'   => ['label' => 'Individual', 'rules' => 'required|is_natural_no_zero'],
            'sampleTypeID'       => ['label' => 'Sample Type', 'rules' => 'required'],
            'sampleParentSearch' => ['label' => 'Sample Parent ID', 'rules' => 'permit_empty|is_natural_no_zero'],
        ];
    }
}
