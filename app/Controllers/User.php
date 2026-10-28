<?php

namespace App\Controllers;

use App\Models\UsersModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Login/logout from CI3 controllers/User.php. Register, forgot password and
 * account validation are not ported yet.
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

        return $this->render('user/login');
    }

    public function logout(): RedirectResponse
    {
        $this->session->destroy();

        return redirect()->to(site_url('login'));
    }
}
