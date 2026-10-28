<?php

namespace App\Models;

use App\Libraries\ListState;

/**
 * Port of CI3 models/Referrer_model.php (referring physicians and institutions).
 */
class ReferrerModel extends ListModel
{
    protected $table      = 'referrer';
    protected $primaryKey = 'referrerID';

    public const SORTS = [
        'referrerLastName'    => 'referrerLastName',
        'referrerID'          => 'referrerID',
        'referrerInstitution' => 'referrerInstitution',
        'referrerFirstName'   => 'referrerFirstName',
    ];

    public const FILTERS = [
        'referrerInstitution' => 'referrerInstitution',
        'referrerFirstName'   => 'referrerFirstName',
        'referrerLastName'    => 'referrerLastName',
    ];

    public const FIELDS = [
        'referrerFirstName', 'referrerLastName', 'referrerInstitution', 'referrerEmail',
        'referrerAddress1', 'referrerAddress2', 'referrerCity', 'referrerState', 'referrerZip', 'referrerCountry',
    ];

    protected $allowedFields = [...self::FIELDS, 'referrerDateAdded', 'referrerDateUpdated'];

    public function listAll(ListState $state): array
    {
        return $this->listPage(
            'SELECT SQL_CALC_FOUND_ROWS referrerID, referrerInstitution, referrerFirstName, referrerLastName
             FROM referrer
             WHERE 1 = 1',
            $state
        );
    }

    /**
     * @return int|false new referrerID
     */
    public function addReferrer(array $post): false|int
    {
        $id = $this->insert(self::pick($post, self::FIELDS) + ['referrerDateAdded' => date('Y-m-d H:i:s')]);

        return $id === false ? false : (int) $id;
    }

    /**
     * CI3 update_referrer() returned nothing, so every edit showed the "failed" message.
     */
    public function updateReferrer(int $referrerID, array $post): bool
    {
        return $this->update($referrerID, self::pick($post, self::FIELDS) + ['referrerDateUpdated' => date('Y-m-d H:i:s')]);
    }

    /**
     * select2 search (CI3 GetReferrerSearch).
     *
     * @return list<array{id: int|string, text: string}>
     */
    public function search(string $keyword): array
    {
        return $this->db->query(
            "SELECT referrerID AS id, CONCAT(referrerInstitution, referrerLastName, referrerFirstName) AS text
             FROM referrer
             WHERE CONCAT(referrerInstitution, referrerLastName, referrerFirstName) LIKE ? ESCAPE '!'",
            ['%' . $this->db->escapeLikeString($keyword) . '%']
        )->getResultArray();
    }

    /**
     * Label used to preselect a referrer in the select2 box.
     */
    public static function label(?array $referrer): string
    {
        return $referrer === null ? '' : $referrer['referrerInstitution'] . $referrer['referrerLastName'] . $referrer['referrerFirstName'];
    }
}
