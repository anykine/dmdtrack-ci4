<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Port of CI3 Settings_model (read part). save_settings() moves over with admin/Settings.
 */
class SettingsModel extends Model
{
    protected $table      = 'settings';
    protected $returnType = 'array';

    /**
     * @return list<array<string, mixed>>
     */
    public function getSettings(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY sort_order ASC")->getResultArray();
    }
}
