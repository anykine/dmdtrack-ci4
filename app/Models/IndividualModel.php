<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PARTIAL port of CI3 models/Individual_model.php: only the select2 lookups.
 */
class IndividualModel extends Model
{
    protected $table      = 'individual';
    protected $primaryKey = 'individualID';
    protected $returnType = 'array';

    /**
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
}
