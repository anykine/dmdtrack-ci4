<?php

namespace App\Models;

use App\Libraries\ListState;

/**
 * Port of CI3 models/Contact_model.php.
 */
class ContactModel extends ListModel
{
    protected $table      = 'contact';
    protected $primaryKey = 'contactID';

    public const SORTS = [
        'contactID'               => 'contactID',
        'contactLastName'         => 'contactLastName',
        'contactFirstName'        => 'contactFirstName',
        'contactPatientLastName'  => 'contactPatientLastName',
        'contactPatientFirstName' => 'contactPatientFirstName',
    ];

    public const FILTERS = [
        'contactLastName'         => 'contactLastName',
        'contactFirstName'        => 'contactFirstName',
        'contactPatientLastName'  => 'contactPatientLastName',
        'contactPatientFirstName' => 'contactPatientFirstName',
    ];

    public const FIELDS = [
        'contactLastName', 'contactFirstName', 'contactPatientLastName', 'contactPatientFirstName',
        'contactEmail1', 'contactEmail2', 'contactPhone1', 'contactAddress1', 'contactAddress2',
        'contactCity', 'contactState', 'contactZip', 'contactCountry', 'contactRelationship',
    ];

    public const RELATIONSHIPS = [
        'parent'    => 'parent',
        'self'      => 'self',
        'sibling'   => 'sibling',
        'spouse'    => 'spouse',
        'other'     => 'other',
        'unknown'   => 'unknown',
        'physician' => 'physician',
    ];

    protected $allowedFields = [...self::FIELDS, 'contactDateAdded', 'contactDateUpdated'];

    public function listAll(ListState $state): array
    {
        return $this->listPage(
            'SELECT SQL_CALC_FOUND_ROWS contactID, contactLastName, contactFirstName,
                    contactPatientFirstName, contactPatientLastName
             FROM contact
             WHERE 1 = 1',
            $state
        );
    }

    /**
     * @return int|false new contactID
     */
    public function addContact(array $post): false|int
    {
        $id = $this->insert(self::pick($post, self::FIELDS) + ['contactDateAdded' => date('Y-m-d H:i:s')]);

        return $id === false ? false : (int) $id;
    }

    /**
     * CI3 update_contact() returned nothing, so every edit showed the "failed" message.
     */
    public function updateContact(int $contactID, array $post): bool
    {
        return $this->update($contactID, self::pick($post, self::FIELDS) + ['contactDateUpdated' => date('Y-m-d H:i:s')]);
    }

    /**
     * select2 search (CI3 GetContactSearch).
     *
     * @return list<array{id: int|string, text: string}>
     */
    public function search(string $keyword): array
    {
        return $this->db->query(
            "SELECT contactID AS id, CONCAT(contactID, ':', contactFirstName, contactLastName, contactPatientFirstName) AS text
             FROM contact
             WHERE CONCAT(contactID, contactFirstName, contactLastName, contactPatientFirstName) LIKE ? ESCAPE '!'",
            ['%' . $this->db->escapeLikeString($keyword) . '%']
        )->getResultArray();
    }

    /**
     * Label used to preselect a contact in the select2 box.
     */
    public static function label(?array $contact): string
    {
        return $contact === null ? '' : $contact['contactFirstName'] . $contact['contactLastName'] . $contact['contactPatientFirstName'];
    }
}
