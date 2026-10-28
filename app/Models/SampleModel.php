<?php

namespace App\Models;

use App\Libraries\ListState;

/**
 * Port of CI3 models/Sample_model.php (biospecimen collection and processing).
 * A sample belongs to an individual through individual_sample_J.
 */
class SampleModel extends ListModel
{
    protected $table      = 'sample';
    protected $primaryKey = 'sampleID';

    public const SORTS = [
        'sampleID'        => 'a.sampleID',
        'sampleTypeID'    => 'a.sampleTypeID',
        'cDMDID'          => 'c.cDMDID',
        'prevID'          => 'c.prevID',
        'cDMDTempID'      => 'c.cDMDTempID',
        'sampleReceived'  => 'a.sampleReceived',
        'extracted'       => 'a.extracted',
        'sampleQueued'    => 'a.sampleQueued',
        'sampleSequenced' => 'a.sampleSequenced',
    ];

    public const FILTERS = [
        'cDMDID'     => 'c.cDMDID',
        'cDMDID1'    => 'c.cDMDID1',
        'prevID'     => 'c.prevID',
        'cDMDTempID' => 'c.cDMDTempID',
    ];

    /** Form field => sample column. */
    private const FIELDS = [
        'sampleTypeID'           => 'sampleTypeID',
        'sampleParentSearch'     => 'sampleParentID',
        'kitSent'                => 'kitSent',
        'kitSentDate'            => 'kitSentDate',
        'sampleReceived'         => 'sampleReceived',
        'sampleReceivedDate'     => 'sampleReceivedDate',
        'extractID'              => 'extractID',
        'extracted'              => 'extracted',
        'tempSampleID'           => 'tempSampleID',
        'extractedDate'          => 'extractedDate',
        'sampleQueued'           => 'sampleQueued',
        'sampleQueuedDate'       => 'sampleQueuedDate',
        'sampleSubmitted'        => 'sampleSubmitted',
        'sampleSubmittedDate'    => 'sampleSubmittedDate',
        'sequencingCenter'       => 'sequencingCenter',
        'sampleSequenced'        => 'sampleSequenced',
        'sampleSequencedDate'    => 'sampleSequencedDate',
        'sampleSequencedID'      => 'sampleSequencedID',
        'concentrationQubit'     => 'concentrationQubit',
        'concentrationNanodrop'  => 'concentrationNanodrop',
        'sampleExhausted'        => 'sampleExhausted',
        'sampleDesc'             => 'sampleDesc',
        'sampleMassMg'           => 'sampleMassMg',
        'quality260280'          => 'quality260280',
        'quality260230'          => 'quality260230',
        'otherSampleIDs'         => 'otherSampleIDs',
        'sampleNotes'            => 'sampleNotes',
        'sampleActionID'         => 'sampleActionID',
        'sampleExtractionTypeID' => 'sampleExtractionTypeID',
    ];

    public function listAll(ListState $state): array
    {
        return $this->listPage(
            'SELECT SQL_CALC_FOUND_ROWS c.individualID, c.cDMDID, c.cDMDID1, c.prevID, c.cDMDTempID,
                    a.sampleID, a.sampleTypeID AS sSampleTypeID, st.sampleType,
                    a.kitSent, a.sampleReceived, a.extracted, a.sampleQueued, a.sampleSequenced,
                    a.otherSampleIDs, a.tempSampleID, a.sampleNotes
             FROM sample a
             JOIN individual_sample_J b ON a.sampleID = b.sampleID
             JOIN individual c ON c.individualID = b.individualID
             LEFT JOIN sampleTypes st ON a.sampleTypeID = st.sampleTypeID
             WHERE 1 = 1',
            $state
        );
    }

    /**
     * One sample with its individual (CI3 get_sample + get_individual_sample_J).
     */
    public function getSample(int $sampleID): ?array
    {
        return $this->db->query(
            'SELECT s.*, s.sampleTypeID AS sSampleTypeID,
                    isj.individualID, i.individualFirstName, i.individualLastName
             FROM sample s
             LEFT JOIN individual_sample_J isj ON isj.sampleID = s.sampleID
             LEFT JOIN individual i ON i.individualID = isj.individualID
             WHERE s.sampleID = ?',
            [$sampleID]
        )->getRowArray();
    }

    /**
     * Samples of one individual with type and action names, for the Individual detail page
     * (CI3 get_samples_for_indiv + get_samples, which failed for individuals with no samples).
     *
     * @return list<array<string, mixed>>
     */
    public function forIndividual(int $individualID): array
    {
        return $this->db->query(
            'SELECT s.sampleID, st.sampleType, s.sampleReceived, s.extracted, s.concentrationNanodrop,
                    s.concentrationQubit, s.quality260280, s.quality260230, s.sampleQueued,
                    s.sampleSubmitted, s.sampleSequenced, sa.sampleAction
             FROM individual_sample_J isj
             JOIN sample s ON s.sampleID = isj.sampleID
             LEFT JOIN sampleTypes st ON s.sampleTypeID = st.sampleTypeID
             LEFT JOIN sampleAction sa ON s.sampleActionID = sa.sampleActionID
             WHERE isj.individualID = ?
             ORDER BY s.sampleID',
            [$individualID]
        )->getResultArray();
    }

    /**
     * @return int|false new sampleID
     */
    public function addSample(array $post): false|int
    {
        $this->db->transStart();

        $this->db->table('sample')->insert($this->row($post) + ['sampleDateAdded' => date('Y-m-d H:i:s')]);
        $sampleID = (int) $this->db->insertID();

        $this->db->table('individual_sample_J')->insert([
            'individualID' => $post['individualSearch'],
            'sampleID'     => $sampleID,
        ]);

        $this->db->transComplete();

        return $this->db->transStatus() ? $sampleID : false;
    }

    /**
     * CI3 update_sample() returned nothing on success, so every edit showed the "failed" message.
     */
    public function updateSample(int $sampleID, array $post): bool
    {
        $this->db->transStart();

        $this->db->table('individual_sample_J')
            ->where('sampleID', $sampleID)
            ->update(['individualID' => $post['individualSearch']]);

        $this->db->table('sample')
            ->where('sampleID', $sampleID)
            ->update($this->row($post) + ['sampleDateUpdated' => date('Y-m-d H:i:s')]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Delete the link row, then the sample. Fails while results still point at the sample.
     */
    public function deleteSample(int $sampleID): bool
    {
        $this->db->transStart();
        $this->db->table('individual_sample_J')->where('sampleID', $sampleID)->delete();
        $this->db->table('sample')->where('sampleID', $sampleID)->delete();
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /** @return array<int|string, string> */
    public function typeOptions(): array
    {
        return $this->options('sampleTypes', 'sampleTypeID', 'sampleType');
    }

    /** @return array<int|string, string> */
    public function actionOptions(): array
    {
        return $this->options('sampleAction', 'sampleActionID', 'sampleAction');
    }

    /** @return array<int|string, string> */
    public function extractionTypeOptions(): array
    {
        return $this->options('sampleExtractionTypes', 'sampleExtractionTypeID', 'sampleExtractionType');
    }

    /**
     * select2 search over samples (CI3 GetSampleSearch). Used by the Sample parent box,
     * Result and Sequencing.
     *
     * @return list<array{id: int|string, text: string}>
     */
    public function search(string $keyword): array
    {
        return $this->db->query(
            "SELECT s.sampleID AS id,
                    CONCAT_WS(':', i.cDMDID, i.cDMDID1, i.prevID, i.cDMDTempID, i.individualFirstName,
                              i.individualLastName, i.individualID, st.sampleType, s.sampleID) AS text
             FROM sample s
             JOIN sampleTypes st ON s.sampleTypeID = st.sampleTypeID
             JOIN individual_sample_J isj ON isj.sampleID = s.sampleID
             JOIN individual i ON isj.individualID = i.individualID
             WHERE CONCAT_WS('', i.cDMDID, i.cDMDID1, i.prevID, i.cDMDTempID, i.individualFirstName,
                             i.individualLastName, s.sampleID, s.sampleTypeID) LIKE ? ESCAPE '!'",
            ['%' . $this->db->escapeLikeString($keyword) . '%']
        )->getResultArray();
    }

    /**
     * Label for one sample, to preselect it in a select2 box (same format as search()).
     */
    public function label(?int $sampleID): string
    {
        if (! $sampleID) {
            return '';
        }

        $row = $this->db->query(
            "SELECT CONCAT_WS(':', i.cDMDID, i.cDMDID1, i.prevID, i.cDMDTempID, i.individualFirstName,
                              i.individualLastName, i.individualID, st.sampleType, s.sampleID) AS text
             FROM sample s
             LEFT JOIN sampleTypes st ON s.sampleTypeID = st.sampleTypeID
             LEFT JOIN individual_sample_J isj ON isj.sampleID = s.sampleID
             LEFT JOIN individual i ON isj.individualID = i.individualID
             WHERE s.sampleID = ?",
            [$sampleID]
        )->getRowArray();

        return (string) ($row['text'] ?? $sampleID);
    }

    private function row(array $post): array
    {
        $row = self::pick($post, self::FIELDS);

        // No parent chosen: store NULL rather than ""
        if (($row['sampleParentID'] ?? '') === '') {
            $row['sampleParentID'] = null;
        }

        return $row;
    }
}
