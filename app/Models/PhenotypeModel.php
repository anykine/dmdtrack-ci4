<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Port of CI3 models/Phenotype_model.php.
 *
 * The model no longer reads $_POST itself: the controller passes the posted array in.
 */
class PhenotypeModel extends Model
{
    protected $table      = 'phenotype';
    protected $primaryKey = 'phenotypeID';
    protected $returnType = 'array';

    /** Form fields saved as-is (individualID comes from the individualSearch select). */
    public const FORM_FIELDS = [
        'maleFemale', 'mildSevere', 'WCage', 'age', 'DOB', 'reportedDMDmutation',
        'geneticReportAvailable', 'biopsyAvailable', 'questionnaireAvailable',
        'steroidHistory', 'supplementHistory', 'ageOfDiagnosis', 'DMDorBMD', 'initialDx',
        'cognitiveDeficiency', 'cardiomyopathy', 'otherStudies', 'notes', 'deceased',
    ];

    /** Columns the list page can sort by. */
    public const SORTABLE = ['phenotypeID', 'mildSevere', 'WCage', 'DOB', 'reportedDMDMutation', 'DMDorBMD'];

    /** Columns the list page can filter on (LIKE %value%). */
    public const FILTERABLE = ['mildSevere', 'reportedDMDMutation', 'DMDorBMD'];

    protected $allowedFields = [
        'individualID', ...self::FORM_FIELDS, 'phenotypeDateAdded', 'phenotypeDateUpdated',
    ];

    /**
     * One page of phenotypes joined to their individual.
     *
     * @param array<string, string> $filters column => text
     *
     * @return array{results: list<array<string, mixed>>, total: int}
     */
    public function listAll(int $limit, int $offset, array $filters, string $sort, string $dir): array
    {
        // sort/dir/limit/offset come from the URL: only allow known values
        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'phenotypeID';
        $dir  = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';

        $sql = 'SELECT SQL_CALC_FOUND_ROWS i.individualID, i.CDMDID, i.CDMDID1, i.prevID, i.CDMDTempID,
                    i.individualLastName, i.individualFirstName, p.phenotypeID,
                    p.maleFemale, p.mildSevere, p.WCage, p.DOB, p.reportedDMDMutation,
                    p.geneticReportAvailable, p.biopsyAvailable, p.questionnaireAvailable,
                    p.steroidHistory, p.supplementHistory, p.ageOfDiagnosis, p.DMDorBMD,
                    p.initialDx, p.notes, p.deceased
                FROM phenotype p
                JOIN individual i ON p.individualID = i.individualID
                WHERE 1 = 1';
        $binds = [];

        foreach ($filters as $column => $value) {
            if (in_array($column, self::FILTERABLE, true)) {
                $sql .= " AND p.{$column} LIKE ?";
                $binds[] = '%' . $value . '%';
            }
        }

        $sql .= " ORDER BY p.{$sort} {$dir}";

        if ($limit > 0) {
            $sql .= sprintf(' LIMIT %d, %d', max(0, $offset), $limit);
        }

        $results = $this->db->query($sql, $binds)->getResultArray();
        $total   = (int) $this->db->query('SELECT FOUND_ROWS() AS total')->getRow()->total;

        return ['results' => $results, 'total' => $total];
    }

    /**
     * The individual a phenotype belongs to (CI3 get_individualID).
     *
     * @return array{individualID: int|string, individualFirstName: string, individualLastName: string}|null
     */
    public function getIndividual(int $phenotypeID): ?array
    {
        return $this->db->query(
            'SELECT p.individualID, i.individualFirstName, i.individualLastName
             FROM phenotype p
             JOIN individual i ON p.individualID = i.individualID
             WHERE p.phenotypeID = ?',
            [$phenotypeID]
        )->getRowArray();
    }

    /**
     * Phenotype row for an individual (CI3 find_phenotypeID).
     */
    public function findByIndividual(int $individualID): ?array
    {
        return $this->where('individualID', $individualID)->first();
    }

    /**
     * Insert ($phenotypeID null) or update a phenotype from posted form data.
     *
     * @param array<string, mixed> $post
     *
     * @return int|false new/updated phenotypeID, or false on failure
     */
    public function savePhenotype(array $post, ?int $phenotypeID = null): false|int
    {
        $data = ['individualID' => $post['individualSearch'] ?? null];

        foreach (self::FORM_FIELDS as $field) {
            $data[$field] = $post[$field] ?? null;
        }

        if ($phenotypeID === null) {
            $data['phenotypeDateAdded'] = date('Y-m-d H:i:s');

            $id = $this->insert($data);

            return $id === false ? false : (int) $id;
        }

        $data['phenotypeDateUpdated'] = date('Y-m-d H:i:s');

        return $this->update($phenotypeID, $data) ? $phenotypeID : false;
    }

    /**
     * Phenotype summary for the Individual detail page (CI3 phenotype_detail_for_indiv).
     * CI3 returned the query object; this returns the rows.
     *
     * @return list<array<string, mixed>>
     */
    public function detailForIndividual(int $individualID): array
    {
        return $this->db->query(
            'SELECT phenotypeID, mildSevere, WCage, DOB, reportedDMDMutation, DMDorBMD, initialDx, notes
             FROM phenotype WHERE individualID = ?',
            [$individualID]
        )->getResultArray();
    }
}
