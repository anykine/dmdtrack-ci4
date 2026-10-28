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

    /**
     * Active user by id, without password/salt (what is kept in the session).
     */
    public function getUser(int $id): ?array
    {
        return $this->db->query(
            "SELECT id, username, first_name, last_name, email, language, is_admin, status, created, updated
             FROM {$this->table}
             WHERE id = ? AND status = '1' AND deleted = '0'",
            [$id]
        )->getRowArray();
    }

    /**
     * Self-registration (CI3 create_profile): inactive account plus a validation code
     * that is emailed to the user.
     *
     * @return string|false validation code
     */
    public function createProfile(array $data): false|string
    {
        [$password, $salt] = self::hashPassword($data['password']);
        $validationCode    = bin2hex(random_bytes(20));
        $now               = date('Y-m-d H:i:s');

        $ok = $this->db->table($this->table)->insert([
            'username'        => $data['username'],
            'password'        => $password,
            'salt'            => $salt,
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'email'           => $data['email'],
            'language'        => $data['language'],
            'is_admin'        => '0',
            'status'          => '0',
            'deleted'         => '0',
            'validation_code' => $validationCode,
            'created'         => $now,
            'updated'         => $now,
        ]);

        return $ok ? $validationCode : false;
    }

    /**
     * Update the logged-in user's own profile (CI3 edit_profile). An empty password
     * keeps the current one.
     */
    public function editProfile(int $userID, array $data): bool
    {
        $row = [
            'username'   => $data['username'],
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'language'   => $data['language'],
            'updated'    => date('Y-m-d H:i:s'),
        ];

        if (($data['password'] ?? '') !== '') {
            [$row['password'], $row['salt']] = self::hashPassword($data['password']);
        }

        $this->db->table($this->table)->where('id', $userID)->where('deleted', '0')->update($row);

        return $this->db->affectedRows() > 0;
    }

    /**
     * Activate an account from the emailed link (CI3 validate_account).
     */
    public function validateAccount(string $encryptedEmail, string $validationCode): bool
    {
        if ($encryptedEmail === '' || $validationCode === '') {
            return false;
        }

        $user = $this->db->query(
            "SELECT id FROM {$this->table}
             WHERE SHA1(email) = ? AND validation_code = ? AND status = '0' AND deleted = '0'
             LIMIT 1",
            [$encryptedEmail, $validationCode]
        )->getRowArray();

        if ($user === null) {
            return false;
        }

        $this->db->table($this->table)->where('id', $user['id'])->update(['status' => '1', 'validation_code' => null]);

        return $this->db->affectedRows() > 0;
    }

    /**
     * Give an active account a new random password (CI3 reset_password).
     *
     * @return array{first_name: string, new_password: string}|null
     */
    public function resetPassword(string $email): ?array
    {
        $user = $this->db->query(
            "SELECT id, first_name FROM {$this->table}
             WHERE email = ? AND status = '1' AND deleted = '0'
             LIMIT 1",
            [$email]
        )->getRowArray();

        if ($user === null) {
            return null;
        }

        $newPassword       = self::randomPassword();
        [$password, $salt] = self::hashPassword($newPassword);

        $this->db->table($this->table)->where('id', $user['id'])->update(['password' => $password, 'salt' => $salt]);

        return $this->db->affectedRows() > 0
            ? ['first_name' => $user['first_name'], 'new_password' => $newPassword]
            : null;
    }

    public function emailExists(string $email): bool
    {
        return $this->db->table($this->table)->where('email', $email)->countAllResults() > 0;
    }

    /**
     * CI3's scheme, kept so old and new accounts log in the same way: sha512(password . salt).
     * CI3 made the salt from mt_rand; this uses random_bytes.
     *
     * @return array{0: string, 1: string} [hash, salt]
     */
    private static function hashPassword(string $password): array
    {
        $salt = hash('sha512', random_bytes(32));

        return [hash('sha512', $password . $salt), $salt];
    }

    /**
     * 8 letters/digits, like CI3 generate_random_password() but from a secure source.
     */
    private static function randomPassword(int $length = 8): string
    {
        $characters = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789';
        $password   = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }
}
