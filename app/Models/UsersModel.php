<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Port of the login part of CI3 Users_model. The rest moves over with User/Profile/admin Users.
 */
class UsersModel extends Model
{
    protected $table      = 'users';
    protected $returnType = 'array';

    /**
     * Check a username/email and password. Uses the CI3 scheme sha512(password . salt)
     * unchanged, so existing accounts keep working.
     *
     * @return array<string, mixed>|null user row without password/salt
     */
    public function login(string $username, string $password): ?array
    {
        $user = $this->db->query(
            "SELECT id, username, password, salt, first_name, last_name, email, language,
                    is_admin, status, created, updated
             FROM {$this->table}
             WHERE (username = ? OR email = ?)
               AND status = '1'
               AND deleted = '0'
             LIMIT 1",
            [$username, $username]
        )->getRowArray();

        if ($user === null || ! hash_equals($user['password'], hash('sha512', $password . $user['salt']))) {
            return null;
        }

        unset($user['password'], $user['salt']);

        return $user;
    }

    /**
     * Record a login attempt from $ip; false when there have been too many recently (CI3 login_attempts()).
     */
    public function loginAttemptsOk(string $ip, int $maxTime, int $maxAttempts): bool
    {
        $now = date('Y-m-d H:i:s');

        $this->db->query('DELETE FROM login_attempts WHERE attempt < ?', [date('Y-m-d H:i:s', strtotime("-{$maxTime} seconds"))]);
        $this->db->query('INSERT INTO login_attempts (ip, attempt) VALUES (?, ?)', [$ip, $now]);

        $row = $this->db->query('SELECT COUNT(*) AS attempts FROM login_attempts WHERE ip = ?', [$ip])->getRowArray();

        return (int) ($row['attempts'] ?? 0) <= $maxAttempts;
    }
}
