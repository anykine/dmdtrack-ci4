<?php

namespace App\Controllers;

use App\Libraries\ListState;
use App\Models\IndividualModel;
use App\Models\LookupModel;
use App\Models\ResultModel;
use App\Models\SampleModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Port of CI3 controllers/Result.php.
 *
 * Routes: result (list), result/add, result/edit/{id}, POST result/delete/{id}.
 */
class Result extends CrudController
{
    protected string $module = 'result';

    public function index(): RedirectResponse|string
    {
        $state = $this->listState(array_keys(ResultModel::SORTS), 'resultsID', array_keys(ResultModel::FILTERS));
        if (! $state instanceof ListState) {
            return $state;
        }

        $this->setTitle('Result List');

        return $this->renderList('result/result_table', $state, model(ResultModel::class)->listAll($state));
    }

    public function add(): RedirectResponse|string
    {
        if ($this->request->is('post') && $this->validate($this->rules())) {
            $id = $this->runSave(fn () => model(ResultModel::class)->addResult($this->request->getPost()));

            return $this->finish($id !== false, "Result {$id} was added.", 'The result could not be added.');
        }

        $this->setTitle('Add Result');

        return $this->render('result/result_form', $this->formData(null));
    }

    public function edit($resultsID = null): RedirectResponse|string
    {
        $result = self::isId($resultsID) ? model(ResultModel::class)->getResult((int) $resultsID) : null;
        if ($result === null) {
            return redirect()->to($this->returnUrl);
        }

        $resultsID = (int) $resultsID;

        if ($this->request->is('post') && $this->validate($this->rules())) {
            $ok = $this->runSave(fn () => model(ResultModel::class)->updateResult($resultsID, $this->request->getPost()));

            return $this->finish((bool) $ok, "Result {$resultsID} was saved.", "Result {$resultsID} could not be saved.");
        }

        $this->setTitle('Edit Result');

        return $this->render('result/result_form', $this->formData($result) + ['resultsID' => $resultsID]);
    }

    public function delete($resultsID = null): RedirectResponse
    {
        return $this->runDelete($resultsID, 'Result', static fn (int $id) => model(ResultModel::class)->deleteResult($id));
    }

    private function formData(?array $result): array
    {
        $lookup = model(LookupModel::class);

        $individualID = set_value('individualSearch', (string) ($result['individualID'] ?? ''), false);
        $individual   = self::isId($individualID) ? model(IndividualModel::class)->find((int) $individualID) : null;
        $sampleID     = set_value('sampleSearch', (string) ($result['linkedSampleID'] ?? $result['sampleID'] ?? ''), false);

        return [
            'cancel_url'        => $this->returnUrl,
            'result'            => $result,
            'testTypes'         => $lookup->testTypes(),
            'platforms'         => $lookup->platforms(),
            'libraryPreps'      => $lookup->libraryPreps(),
            'sequencingCenters' => $lookup->sequencingCenters(),
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
            'sampleSearch'     => ['label' => 'Sample', 'rules' => 'required|is_natural_no_zero'],
        ];
    }
}
