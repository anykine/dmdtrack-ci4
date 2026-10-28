<?php

namespace App\Models;

use App\Libraries\ListState;
use CodeIgniter\Model;

/**
 * Base for the data-module models.
 *
 * CI3 models each had a list_all_*() that pasted sort/dir/filter values from the URL
 * straight into SQL. listPage() only uses columns listed in the model's SORTS/FILTERS
 * maps and binds the filter text.
 */
abstract class ListModel extends Model
{
    protected $returnType = 'array';

    /** Columns the list can sort by: URL name => SQL expression. */
    public const SORTS = [];

    /** Columns the list can filter on (LIKE %text%): URL name => SQL expression. */
    public const FILTERS = [];

    /**
     * Run a list query. $sql must start with "SELECT SQL_CALC_FOUND_ROWS" and end with
     * a WHERE clause (use "WHERE 1 = 1" when there is no condition).
     *
     * @return array{results: list<array<string, mixed>>, total: int}
     */
    protected function listPage(string $sql, ListState $state, array $binds = []): array
    {
        foreach ($state->filters as $field => $value) {
            if (isset(static::FILTERS[$field])) {
                $sql .= ' AND ' . static::FILTERS[$field] . " LIKE ? ESCAPE '!'";
                $binds[] = '%' . $this->db->escapeLikeString($value) . '%';
            }
        }

        $sort = static::SORTS[$state->sort] ?? array_values(static::SORTS)[0];
        $dir  = $state->dir === 'desc' ? 'DESC' : 'ASC';
        $sql .= " ORDER BY {$sort} {$dir}";
        $sql .= sprintf(' LIMIT %d, %d', $state->offset(), $state->limit);

        $results = $this->db->query($sql, $binds)->getResultArray();
        $total   = (int) $this->db->query('SELECT FOUND_ROWS() AS total')->getRow()->total;

        return ['results' => $results, 'total' => $total];
    }

    /**
     * Copy form fields into a row: [form field => column] or a plain list when the names match.
     *
     * @param array<int|string, string> $map
     *
     * @return array<string, mixed>
     */
    protected static function pick(array $post, array $map): array
    {
        $row = [];

        foreach ($map as $field => $column) {
            $field        = is_int($field) ? $column : $field;
            $value        = $post[$field] ?? null;
            // CI3's 'trim' validation rule trimmed the posted values; keep that
            $row[$column] = is_string($value) ? trim($value) : $value;
        }

        return $row;
    }

    /**
     * id => label list for a dropdown, from a lookup table.
     *
     * @return array<int|string, string>
     */
    protected function options(string $table, string $idColumn, string $labelColumn, array $first = []): array
    {
        $options = $first;

        foreach ($this->db->table($table)->select("{$idColumn}, {$labelColumn}")->orderBy($idColumn)->get()->getResultArray() as $row) {
            $options[$row[$idColumn]] = (string) $row[$labelColumn];
        }

        return $options;
    }
}
