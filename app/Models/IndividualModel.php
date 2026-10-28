<?php

namespace App\Models;

use App\Libraries\ListState;

/**
 * Port of CI3 models/Individual_model.php.
 */
class IndividualModel extends ListModel
{
    protected $table      = 'individual';
    protected $primaryKey = 'individualID';

    public const SORTS = [
        'individualID'        => 'i.individualID',
        'cDMDID'              => 'i.cDMDID',
        'cDMDID1'             => 'i.cDMDID1',
        'celllineID'          => 'i.celllineID',
        'needleBxID'          => 'i.needleBxID',
        'prevID'              => 'i.prevID',
        'cDMDTempID'          => 'i.cDMDTempID',
        'individualFirstName' => 'i.individualFirstName',
        'individualLastName'  => 'i.individualLastName',
    ];

    public const FILTERS = [
        'individualID'        => 'i.individualID',
        'cDMDID'              => 'i.cDMDID',
        'cDMDID1'             => 'i.cDMDID1',
        'needleBxID'          => 'i.needleBxID',
        'prevID'              => 'i.prevID',
        'cDMDTempID'          => 'i.cDMDTempID',
        'individualFirstName' => 'i.individualFirstName',
        'individualLastName'  => 'i.individualLastName',
    ];

    /**
     * The Study menu (?cmd=...): individuals with a consent of this type.
     * CI3 had one copy of the query per study.
     */
    public const STUDIES = [
        'GenMod'     => 1,
        'MGSDD'      => 2,
        'DMDdx'      => 3,
        'aidmd'      => 4,
        'RNAseqDMD'  => 5,
        'udn'        => 6,
        'unknownDx'  => 7,
        'needleBx'   => 8,
        'ennis'      => 9,
        'imagingDMD' => 10,
    ];

    /** Form field => individual column. */
    private const FIELDS = [
        'cDMDID'               => 'cDMDID',
        'cDMDID1'              => 'cDMDID1',
        'celllineID'           => 'celllineID',
        'needleBxID'           => 'needleBxID',
        'prevID'               => 'prevID',
        'cDMDTempID'           => 'cDMDTempID',
        'individualLastName'   => 'individualLastName',
        'individualFirstName'  => 'individualFirstName',
        'individualMiddleName' => 'individualMiddleName',
        'referrerSearch'       => 'referrerID',
        'contactSearch'        => 'contactID',
    ];

    public function listAll(ListState $state): array
    {
        $columns = 'i.individualID, i.cDMDID, i.cDMDID1, i.celllineID, i.needleBxID, i.prevID, i.cDMDTempID,
                    i.individualFirstName, i.individualLastName, i.contactID, i.referrerID';

        $study = self::STUDIES[$state->extra['cmd'] ?? ''] ?? null;

        if ($study === null) {
            return $this->listPage("SELECT SQL_CALC_FOUND_ROWS {$columns} FROM individual i WHERE 1 = 1", $state);
        }

        return $this->listPage(
            "SELECT DISTINCT SQL_CALC_FOUND_ROWS {$columns}
             FROM consent c
             JOIN individual_consents_J icj ON c.consentID = icj.consentID
             JOIN individual i ON icj.individualID = i.individualID
             WHERE c.consentTypeID = ?",
            $state,
            [$study]
        );
    }

    /**
     * @return int|false new individualID
     */
    public function addIndividual(array $post): false|int
    {
        $row = $this->row($post) + ['individualDateAdded' => date('Y-m-d H:i:s')];

        if (! $this->db->table('individual')->insert($row)) {
            return false;
        }

        return (int) $this->db->insertID();
    }

    /**
     * CI3 update_individual() returned nothing, so every edit showed the "failed" message.
     */
    public function updateIndividual(int $individualID, array $post): bool
    {
        return $this->db->table('individual')
            ->where('individualID', $individualID)
            ->update($this->row($post) + ['individualDateUpdated' => date('Y-m-d H:i:s')]);
    }

    /**
     * Fails (foreign keys) while phenotype/consent/sample/... rows still point at it.
     */
    public function deleteIndividual(int $individualID): bool
    {
        return (bool) $this->db->table('individual')->where('individualID', $individualID)->delete();
    }

    /**
     * select2 search (CI3 GetIndividualSearch).
     *
     * @return list<array{id: int|string, text: string}>
     */
    public function search(string $keyword): array
    {
        return $this->db->query(
            "SELECT individualID AS id, CONCAT(individualID, ':', individualFirstName, individualLastName) AS text
             FROM individual
             WHERE CONCAT(individualFirstName, individualLastName) LIKE ? ESCAPE '!'",
            ['%' . $this->db->escapeLikeString($keyword) . '%']
        )->getResultArray();
    }

    /**
     * @return list<array{id: int|string, text: string}>
     */
    public function searchOne(int $individualID): array
    {
        return $this->db->query(
            "SELECT individualID AS id, CONCAT(individualID, ':', individualFirstName, individualLastName) AS text
             FROM individual WHERE individualID = ?",
            [$individualID]
        )->getResultArray();
    }

    /**
     * Family members of an individual (CI3 Family_model::get_familyIDs).
     *
     * @return list<array{familyIndividualID: int|string, familyRelationType: string|null}>
     */
    public function family(int $individualID): array
    {
        return $this->db->query(
            'SELECT f.familyIndividualID, frt.familyRelationType
             FROM family f
             JOIN familyRelationType frt ON f.familyRelationTypeID = frt.familyRelationTypeID
             WHERE f.individualID = ?',
            [$individualID]
        )->getResultArray();
    }

    /**
     * Full name for select2 preselects and messages.
     */
    public static function name(?array $individual): string
    {
        return $individual === null ? '' : trim($individual['individualFirstName'] . ' ' . $individual['individualLastName']);
    }

    private function row(array $post): array
    {
        $row = self::pick($post, self::FIELDS);

        // Empty select2 boxes post "" which is not a valid id
        foreach (['referrerID', 'contactID'] as $key) {
            $row[$key] = ($row[$key] ?? '') === '' ? null : $row[$key];
        }

        return $row;
    }
}
