<?php

namespace App\Controllers;

use App\Models\UsersModel;
use App\Validation\AccountRules;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Port of CI3 controllers/User.php: login, logout, register, account validation and
 * forgotten password.
 *
 * Registration is off unless Config\Site::$allowRegistration is true.
 */
class User extends BaseController
{
    public function login(): RedirectResponse|string
    {
        if ($this->user) {
            // TODO: send admins to site_url('admin') once admin is ported (CI3 behaviour)
            return redirect()->to(base_url());
        }

        if ($this->request->is('post')) {
            $rules = [
                'username' => ['label' => lang('Users.users input username_email'), 'rules' => 'required|max_length[256]'],
                'password' => ['label' => lang('Users.users input password'), 'rules' => 'required|max_length[72]'],
            ];

            if ($this->validate($rules)) {
                $users = model(UsersModel::class);

                if (! $users->loginAttemptsOk($this->request->getIPAddress(), $this->site->loginMaxTime, $this->site->loginMaxAttempts)) {
                    $this->error = sprintf(lang('Users.users error too_many_login_attempts'), $this->site->loginMaxTime);
                } else {
                    // CI3's 'trim' rule trimmed both fields before checking, so do the same
                    $login = $users->login(
                        trim((string) $this->request->getPost('username')),
                        trim((string) $this->request->getPost('password'))
                    );

                    if ($login !== null) {
                        $this->session->regenerate(true);
                        $this->session->set('logged_in', $login);

                        $redirect = $this->session->get('redirect');
                        $this->session->remove('redirect');

                        // only follow redirects back into this site
                        if (is_string($redirect) && str_starts_with($redirect, rtrim(base_url(), '/') . '/')) {
                            return redirect()->to($redirect);
                        }

                        return redirect()->to(base_url());
                    }

                    $this->error = lang('Users.users error invalid_login');
                }
            }
        }

        $this->setTitle(lang('Users.users title login'))->addCssTheme('login.css');

        return $this->render('user/login', ['allowRegistration' => $this->site->allowRegistration]);
    }

    public function logout(): RedirectResponse
    {
        $this->session->destroy();

        return redirect()->to(site_url('login'));
    }

    /**
     * Create an inactive account and email the validation link.
     */
    public function register(): RedirectResponse|string
    {
        if (! $this->site->allowRegistration) {
            $this->session->setFlashdata('error', 'Registration is closed. Ask an administrator for an account.');

            return redirect()->to(site_url('login'));
        }

        if ($this->user) {
            return redirect()->to(site_url('profile'));
        }

        if ($this->request->is('post') && $this->validate(AccountRules::rules(null, $this->request->getPost(), $this->site->languages))) {
            $post = array_map(static fn ($v) => is_string($v) ? trim($v) : $v, $this->request->getPost());
            $code = model(UsersModel::class)->createProfile($post);

            if ($code === false) {
                $this->session->setFlashdata('error', lang('Users.users error register_failed'));

                return redirect()->to(site_url('user/register'));
            }

            $url = site_url('user/validate') . '?' . http_build_query(['e' => sha1($post['email']), 'c' => $code]);

            $this->sendEmail(
                $post['email'],
                sprintf(lang('Users.users msg email_new_account_title'), $post['first_name']),
                sprintf(lang('Users.users msg email_new_account'), esc($this->settings->site_name ?? ''), esc($url, 'attr'), esc($url))
            );

            $this->session->set('language', $post['language']);
            $this->session->setFlashdata('message', sprintf(lang('Users.users msg register_success'), $post['first_name']));

            return redirect()->to(site_url('login'));
        }

        $this->setTitle(lang('Users.users title register'));

        return $this->render('user/profile_form', [
            'cancel_url'        => site_url('login'),
            'account'           => null,
            'password_required' => true,
            'save_label'        => lang('Users.users button register'),
        ]);
    }

    /**
     * The link from the registration email: GET user/validate?e=sha1(email)&c=code
     */
    public function validateAccount(): RedirectResponse
    {
        $ok = model(UsersModel::class)->validateAccount(
            (string) ($this->request->getGet('e') ?? ''),
            (string) ($this->request->getGet('c') ?? '')
        );

        $this->session->setFlashdata($ok ? 'message' : 'error', lang($ok ? 'Users.users msg validate_success' : 'Users.users error validate_failed'));

        return redirect()->to(site_url('login'));
    }

    /**
     * Forgotten password: set a new random password and email it (CI3 behaviour).
     */
    public function forgot(): RedirectResponse|string
    {
        $rules = [
            'email' => [
                'label'  => lang('Users.users input email'),
                'rules'  => 'required|max_length[256]|valid_email|is_not_unique[users.email]',
                'errors' => ['is_not_unique' => lang('Users.users error email_not_exists')],
            ],
        ];

        if ($this->request->is('post') && $this->validate($rules)) {
            $email  = trim((string) $this->request->getPost('email'));
            $result = model(UsersModel::class)->resetPassword($email);

            if ($result === null) {
                $this->session->setFlashdata('error', lang('Users.users error password_reset_failed'));
            } else {
                $loginUrl = site_url('login');

                $this->sendEmail(
                    $email,
                    sprintf(lang('Users.users msg email_password_reset_title'), $result['first_name']),
                    sprintf(lang('Users.users msg email_password_reset'), esc($this->settings->site_name ?? ''), esc($result['new_password']), esc($loginUrl, 'attr'), esc($loginUrl))
                );

                $this->session->setFlashdata('message', sprintf(lang('Users.users msg password_reset_success'), $result['first_name']));
            }

            return redirect()->to(site_url('login'));
        }

        $this->setTitle(lang('Users.users title forgot'));

        return $this->render('user/forgot_form', ['cancel_url' => site_url('login')]);
    }

    /**
     * HTML email through the local sendmail, from the site address (CI3 used the same setup).
     */
    private function sendEmail(string $to, string $subject, string $body): void
    {
        $from  = (string) ($this->settings->site_email ?? '');
        $email = service('email', null, false);

        $email->initialize([
            'protocol' => 'sendmail',
            'mailPath' => '/usr/sbin/sendmail',
            'mailType' => 'html',
        ]);
        $email->setFrom($from, (string) ($this->settings->site_name ?? ''));
        $email->setReplyTo($from, (string) ($this->settings->site_name ?? ''));
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage(lang('Core.core email start') . $body . lang('Core.core email end'));

        if (! $email->send(false)) {
            log_message('error', 'Email to ' . $to . ' failed: ' . $email->printDebugger(['headers']));
        }
    }
}
