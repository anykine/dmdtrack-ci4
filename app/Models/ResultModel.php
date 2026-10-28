<?php

namespace App\Models;

use App\Libraries\ListState;

/**
 * Port of CI3 models/Result_model.php. Results live in `results2`, linked to a sample
 * (sample_results_J) and to an individual (individual_result_J).
 */
class ResultModel extends ListModel
{
    protected $table      = 'results2';
    protected $primaryKey = 'resultsID';

    public const SORTS = [
        'resultsID'    => 'samp.resultsID',
        'testTypeID'   => 'samp.testTypeID',
        'cDMDID'       => 'ident.cDMDID',
        'cDMDID1'      => 'ident.cDMDID1',
        'prevID'       => 'ident.prevID',
        'cDMDTempID'   => 'ident.cDMDTempID',
        'DMDmutation'  => 'samp.DMDmutation',
        'testFindings' => 'samp.testFindings',
    ];

    public const FILTERS = [
        'cDMDID'     => 'ident.cDMDID',
        'cDMDID1'    => 'ident.cDMDID1',
        'prevID'     => 'ident.prevID',
        'cDMDTempID' => 'ident.cDMDTempID',
    ];

    /**
     * Form field => results2 column. CI3's form named the platform and library dropdowns
     * "platform" and "library" while the model read platformID/libraryPrepID, so those two
     * were never saved; the form now uses the column names.
     */
    private const FIELDS = [
        'sampleSearch'          => 'sampleID',
        'testTypeID'            => 'testTypeID',
        'platformID'            => 'platformID',
        'libraryPrepID'         => 'libraryPrepID',
        'DMDmutation'           => 'DMDmutation',
        'testFindings'          => 'testFindings',
        'testFindingsDMDexonic' => 'testFindingsDMDexonic',
        'testFindingsHGVS'      => 'testFindingsHGVS',
        'rawDataPath'           => 'rawDataPath',
        'bamFilePath'           => 'bamFilePath',
        'sequencingCenter'      => 'sequencingCenter',
        'sequencingID'          => 'sequencingID',
        'flowcell'              => 'flowcell',
        'lane'                  => 'lane',
        'barcode'               => 'barcode',
        'readDepth'             => 'readDepth',
        'PEorSE'                => 'PEorSE',
        'readLength'            => 'readLength',
    ];

    public function listAll(ListState $state): array
    {
        return $this->listPage(
            'SELECT SQL_CALC_FOUND_ROWS ident.individualID, ident.cDMDID, ident.cDMDID1, ident.cDMDTempID, ident.prevID,
                    samp.resultsID, samp.sampleID, samp.testTypeID, tt.testType, samp.DMDmutation, samp.testFindings
             FROM (
                 SELECT i.cDMDID, i.cDMDID1, i.cDMDTempID, i.prevID, isj.individualID, isj.sampleID
                 FROM individual i
                 JOIN individual_sample_J isj ON i.individualID = isj.individualID
             ) ident
             INNER JOIN (
                 SELECT srj.sampleID, r.resultsID, r.testTypeID, r.DMDmutation, r.testFindings
                 FROM results2 r
                 JOIN sample_results_J srj ON r.resultsID = srj.resultsID
             ) samp ON ident.sampleID = samp.sampleID
             LEFT JOIN testType tt ON tt.testTypeID = samp.testTypeID
             WHERE 1 = 1',
            $state
        );
    }

    /**
     * One result with its sample and individual (CI3 get_result + get_sample_results_J +
     * get_individualID_for_result).
     */
    public function getResult(int $resultsID): ?array
    {
        return $this->db->query(
            'SELECT r.*, srj.sampleID AS linkedSampleID, irj.individualID,
                    i.individualFirstName, i.individualLastName
             FROM results2 r
             LEFT JOIN sample_results_J srj ON srj.resultsID = r.resultsID
             LEFT JOIN individual_result_J irj ON irj.resultID = r.resultsID
             LEFT JOIN individual i ON i.individualID = irj.individualID
             WHERE r.resultsID = ?
             LIMIT 1',
            [$resultsID]
        )->getRowArray();
    }

    /**
     * Results for an individual's samples, for the Individual detail page
     * (CI3 get_results_sample_J, which failed for individuals with no samples).
     *
     * @return list<array<string, mixed>>
     */
    public function forIndividual(int $individualID): array
    {
        return $this->db->query(
            'SELECT srj.sampleID, r.resultsID, tt.testType, r.DMDmutation, r.testFindings, r.rawDataPath,
                    r.bamFilePath, r.sequencingCenter, r.sequencingID, r.platformID, r.libraryPrepID,
                    r.flowcell, r.lane, r.barcode, r.readDepth, r.PEorSE, r.readLength
             FROM individual_sample_J isj
             JOIN sample_results_J srj ON srj.sampleID = isj.sampleID
             JOIN results2 r ON srj.resultsID = r.resultsID
             LEFT JOIN testType tt ON r.testTypeID = tt.testTypeID
             WHERE isj.individualID = ?
             ORDER BY r.resultsID',
            [$individualID]
        )->getResultArray();
    }

    /**
     * Insert the result and both link rows in one transaction.
     *
     * @return int|false new resultsID
     */
    public function addResult(array $post): false|int
    {
        $this->db->transStart();

        $this->db->table('results2')->insert(self::pick($post, self::FIELDS) + ['resultsDateAdded' => date('Y-m-d H:i:s')]);
        $resultsID = (int) $this->db->insertID();

        $this->db->table('sample_results_J')->insert([
            'sampleID'  => $post['sampleSearch'],
            'resultsID' => $resultsID,
        ]);

        $this->db->table('individual_result_J')->insert([
            'individualID' => $post['individualSearch'],
            'resultID'     => $resultsID,
            'sampleID'     => $post['sampleSearch'],
        ]);

        $this->db->transComplete();

        return $this->db->transStatus() ? $resultsID : false;
    }

    /**
     * Update the result and its links. CI3 never updated individual_result_J, so changing
     * the individual on a result didn't stick.
     */
    public function updateResult(int $resultsID, array $post): bool
    {
        $this->db->transStart();

        $this->db->table('results2')
            ->where('resultsID', $resultsID)
            ->update(self::pick($post, self::FIELDS) + ['resultsDateUpdated' => date('Y-m-d H:i:s')]);

        $this->db->table('sample_results_J')
            ->where('resultsID', $resultsID)
            ->update(['sampleID' => $post['sampleSearch']]);

        $this->db->table('individual_result_J')
            ->where('resultID', $resultsID)
            ->update(['individualID' => $post['individualSearch'], 'sampleID' => $post['sampleSearch']]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * CI3 deleted from the old `results` table, so results were never removed.
     */
    public function deleteResult(int $resultsID): bool
    {
        $this->db->transStart();
        $this->db->table('individual_result_J')->where('resultID', $resultsID)->delete();
        $this->db->table('sample_results_J')->where('resultsID', $resultsID)->delete();
        $this->db->table('results2')->where('resultsID', $resultsID)->delete();
        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
