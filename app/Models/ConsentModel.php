<?php

namespace App\Models;

use App\Libraries\ListState;

/**
 * Port of CI3 models/Consent_model.php. A consent belongs to an individual through
 * individual_consents_J.
 */
class ConsentModel extends ListModel
{
    protected $table      = 'consent';
    protected $primaryKey = 'consentID';

    public const SORTS = [
        'consentID'       => 'c.consentID',
        'consentTypeID'   => 'c.consentTypeID',
        'consentSent'     => 'c.consentSent',
        'consentReceived' => 'c.consentReceived',
    ];

    // CI3 accepted consentID/consentTypeID filters in the URL but the page had no filter boxes
    public const FILTERS = [
        'consentID'     => 'c.consentID',
        'consentTypeID' => 'c.consentTypeID',
    ];

    /** Form field => consent column. */
    private const FIELDS = [
        'consentTypeSearch'   => 'consentTypeID',
        'consentReceived'     => 'consentReceived',
        'consentReceivedDate' => 'consentReceivedDate',
        'consentSent'         => 'consentSent',
        'consentSentDate'     => 'consentSentDate',
    ];

    public function listAll(ListState $state): array
    {
        return $this->listPage(
            'SELECT SQL_CALC_FOUND_ROWS i.cDMDID, i.cDMDID1, i.prevID, i.cDMDTempID, i.individualID,
                    i.individualFirstName, i.individualLastName,
                    c.consentID, c.consentTypeID AS cConsentTypeID, ct.consentType,
                    c.consentReceived, c.consentReceivedDate, c.consentSent, c.consentSentDate
             FROM consent c
             JOIN individual_consents_J icj ON c.consentID = icj.consentID
             JOIN individual i ON icj.individualID = i.individualID
             LEFT JOIN consentTypes ct ON c.consentTypeID = ct.consentTypeID
             WHERE 1 = 1',
            $state
        );
    }

    /**
     * One consent with its type name and individual (CI3 get_consent + get_individual_consent_J).
     */
    public function getConsent(int $consentID): ?array
    {
        return $this->db->query(
            'SELECT c.consentID, c.consentTypeID AS cConsentTypeID, ct.consentType,
                    c.consentReceived, c.consentReceivedDate, c.consentSent, c.consentSentDate,
                    icj.individualID, i.individualFirstName, i.individualLastName
             FROM consent c
             LEFT JOIN consentTypes ct ON c.consentTypeID = ct.consentTypeID
             LEFT JOIN individual_consents_J icj ON icj.consentID = c.consentID
             LEFT JOIN individual i ON i.individualID = icj.individualID
             WHERE c.consentID = ?',
            [$consentID]
        )->getRowArray();
    }

    /**
     * Consents of one individual, for the Individual detail page (CI3 get_consents_for_indiv).
     *
     * @return list<array{consentID: int|string, consentType: string|null}>
     */
    public function forIndividual(int $individualID): array
    {
        return $this->db->query(
            'SELECT c.consentID, ct.consentType
             FROM individual_consents_J icj
             JOIN consent c ON icj.consentID = c.consentID
             LEFT JOIN consentTypes ct ON c.consentTypeID = ct.consentTypeID
             WHERE icj.individualID = ?',
            [$individualID]
        )->getResultArray();
    }

    /**
     * Insert a consent and link it to the individual, in one transaction.
     *
     * @return int|false new consentID
     */
    public function addConsent(array $post): false|int
    {
        $this->db->transStart();

        $this->db->table('consent')->insert(self::pick($post, self::FIELDS) + ['consentDateAdded' => date('Y-m-d H:i:s')]);
        $consentID = (int) $this->db->insertID();

        $this->db->table('individual_consents_J')->insert([
            'individualID' => $post['individualSearch'],
            'consentID'    => $consentID,
        ]);

        $this->db->transComplete();

        return $this->db->transStatus() ? $consentID : false;
    }

    /**
     * Update a consent. Changing the individual moves the link too (CI3 relied on a posted
     * hidden field for the old individual; this looks it up instead).
     */
    public function updateConsent(int $consentID, array $post): bool
    {
        $this->db->transStart();

        $this->db->table('consent')
            ->where('consentID', $consentID)
            ->update(self::pick($post, self::FIELDS) + ['consentDateUpdated' => date('Y-m-d H:i:s')]);

        $this->db->table('individual_consents_J')
            ->where('consentID', $consentID)
            ->update(['individualID' => $post['individualSearch']]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Delete the link row, then the consent.
     */
    public function deleteConsent(int $consentID): bool
    {
        $this->db->transStart();
        $this->db->table('individual_consents_J')->where('consentID', $consentID)->delete();
        $this->db->table('consent')->where('consentID', $consentID)->delete();
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Consent types for the select2 box (CI3 GetConsentSearch).
     *
     * @return list<array{id: int|string, text: string}>
     */
    public function searchTypes(string $keyword): array
    {
        return $this->db->query(
            "SELECT consentTypeID AS id, consentType AS text
             FROM consentTypes
             WHERE consentType LIKE ? ESCAPE '!'",
            ['%' . $this->db->escapeLikeString($keyword) . '%']
        )->getResultArray();
    }
}
