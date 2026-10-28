<?php

namespace App\Models;

use App\Libraries\ListState;

/**
 * Port of CI3 models/Sequencing_model.php. A sequencing run belongs to an individual
 * through individual_sequencing_J and points at a sample (sequencing.sampleID).
 */
class SequencingModel extends ListModel
{
    protected $table      = 'sequencing';
    protected $primaryKey = 'sequencingID';

    public const SORTS = [
        'sequencingID'                    => 'a.sequencingID',
        'sequencingTypeID'                => 'a.sequencingTypeID',
        'cDMDID'                          => 'c.cDMDID',
        'prevID'                          => 'c.prevID',
        'cDMDTempID'                      => 'c.cDMDTempID',
        'sequencingQueued'                => 'a.sequencingQueued',
        'sequencingSubmitted'             => 'a.sequencingSubmitted',
        'sequencingCompleted'             => 'a.sequencingCompleted',
        'sequencingCenterBatchIdentifier' => 'a.sequencingCenterBatchIdentifier',
    ];

    public const FILTERS = [
        'cDMDID'     => 'c.cDMDID',
        'cDMDID1'    => 'c.cDMDID1',
        'prevID'     => 'c.prevID',
        'cDMDTempID' => 'c.cDMDTempID',
    ];

    /** Form field => sequencing column. */
    private const FIELDS = [
        'sequencingTypeID'                => 'sequencingTypeID',
        'sampleSearch'                    => 'sampleID',
        'sequencingQCpassed'              => 'sequencingQCpassed',
        'sequencingQCnotes'               => 'sequencingQCnotes',
        'sequencingQueued'                => 'sequencingQueued',
        'sequencingQueuedDate'            => 'sequencingQueuedDate',
        'sequencingSubmitted'             => 'sequencingSubmitted',
        'sequencingSubmittedDate'         => 'sequencingSubmittedDate',
        'sequencingCompleted'             => 'sequencingCompleted',
        'sequencingCompletedDate'         => 'sequencingCompletedDate',
        'sequencingBatchIdentifier'       => 'sequencingBatchIdentifier',
        'sequencingCenterID'              => 'sequencingCenterID',
        'sequencingPlatform'              => 'sequencingPlatform',
        'sequencingLibraryID'             => 'sequencingLibraryID',
        'sequencingPEorSE'                => 'sequencingPEorSE',
        'sequencingReadLength'            => 'sequencingReadLength',
        'sequencingReadDepth'             => 'sequencingReadDepth',
        'sequencingMultiplexed'           => 'sequencingMultiplexed',
        'otherSampleIDs'                  => 'otherSampleIDs',
        'sequencingNotes'                 => 'sequencingNotes',
        'sequencingBarcode'               => 'sequencingBarcode',
        'sequencingCenterBatchIdentifier' => 'sequencingCenterBatchIdentifier',
        'sequencingSampleName'            => 'sequencingSampleName',
    ];

    public function listAll(ListState $state): array
    {
        return $this->listPage(
            'SELECT SQL_CALC_FOUND_ROWS c.individualID, c.cDMDID, c.cDMDID1, c.prevID, c.cDMDTempID,
                    a.sequencingID, st.sequencingTypeID, st.sequencingType,
                    a.sequencingQueued, a.sequencingSubmitted, a.sequencingCompleted,
                    a.sequencingBatchIdentifier, a.sequencingCenterBatchIdentifier
             FROM sequencing a
             JOIN individual_sequencing_J b ON a.sequencingID = b.sequencingID
             JOIN individual c ON c.individualID = b.individualID
             JOIN sequencingTypes st ON a.sequencingTypeID = st.sequencingTypeID
             WHERE 1 = 1',
            $state
        );
    }

    /**
     * One sequencing row with its individual.
     */
    public function getSequencing(int $sequencingID): ?array
    {
        return $this->db->query(
            'SELECT s.*, isj.individualID, i.individualFirstName, i.individualLastName
             FROM sequencing s
             LEFT JOIN individual_sequencing_J isj ON isj.sequencingID = s.sequencingID
             LEFT JOIN individual i ON i.individualID = isj.individualID
             WHERE s.sequencingID = ?',
            [$sequencingID]
        )->getRowArray();
    }

    /**
     * Sequencing runs of one individual, for the Individual detail page
     * (CI3 get_sequencings_for_indiv + get_sequencings).
     *
     * @return list<array<string, mixed>>
     */
    public function forIndividual(int $individualID): array
    {
        return $this->db->query(
            'SELECT s.sequencingID, st.sequencingType, s.sequencingQueued, s.sequencingSubmitted,
                    s.sequencingCompleted, s.sequencingBatchIdentifier, s.sequencingPlatform,
                    s.sequencingPEorSE, s.sequencingReadLength, s.sequencingReadDepth,
                    s.sequencingMultiplexed, s.sequencingBarcode
             FROM individual_sequencing_J isj
             JOIN sequencing s ON s.sequencingID = isj.sequencingID
             LEFT JOIN sequencingTypes st ON s.sequencingTypeID = st.sequencingTypeID
             WHERE isj.individualID = ?
             ORDER BY s.sequencingID',
            [$individualID]
        )->getResultArray();
    }

    /**
     * @return int|false new sequencingID
     */
    public function addSequencing(array $post): false|int
    {
        $this->db->transStart();

        $this->db->table('sequencing')->insert($this->row($post) + ['sequencingDateAdded' => date('Y-m-d H:i:s')]);
        $sequencingID = (int) $this->db->insertID();

        $this->db->table('individual_sequencing_J')->insert([
            'individualID' => $post['individualSearch'],
            'sequencingID' => $sequencingID,
        ]);

        $this->db->transComplete();

        return $this->db->transStatus() ? $sequencingID : false;
    }

    /**
     * CI3 update_sequencing() returned nothing on success, so every edit showed the "failed" message.
     */
    public function updateSequencing(int $sequencingID, array $post): bool
    {
        $this->db->transStart();

        $this->db->table('individual_sequencing_J')
            ->where('sequencingID', $sequencingID)
            ->update(['individualID' => $post['individualSearch']]);

        $this->db->table('sequencing')
            ->where('sequencingID', $sequencingID)
            ->update($this->row($post) + ['sequencingDateUpdated' => date('Y-m-d H:i:s')]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function deleteSequencing(int $sequencingID): bool
    {
        $this->db->transStart();
        $this->db->table('individual_sequencing_J')->where('sequencingID', $sequencingID)->delete();
        $this->db->table('sequencing')->where('sequencingID', $sequencingID)->delete();
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /** @return array<int|string, string> */
    public function typeOptions(): array
    {
        return $this->options('sequencingTypes', 'sequencingTypeID', 'sequencingType');
    }

    private function row(array $post): array
    {
        $row = self::pick($post, self::FIELDS);

        if (($row['sampleID'] ?? '') === '') {
            $row['sampleID'] = null;
        }

        return $row;
    }
}
