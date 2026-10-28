<?php

namespace App\Controllers;

use App\Models\UsersModel;
use App\Validation\AccountRules;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Port of CI3 controllers/Profile.php: the logged-in user edits their own account.
 *
 * Route: profile (GET|POST), behind AuthFilter.
 */
class Profile extends BaseController
{
    public function index(): RedirectResponse|string
    {
        $userID = (int) $this->user['id'];

        if ($this->request->is('post') && $this->validate(AccountRules::rules($userID, $this->request->getPost(), $this->site->languages))) {
            $post  = array_map(static fn ($v) => is_string($v) ? trim($v) : $v, $this->request->getPost());
            $users = model(UsersModel::class);

            if ($users->editProfile($userID, $post)) {
                // Refresh the copy of the user kept in the session
                $user = $users->getUser($userID);
                if ($user !== null) {
                    $this->session->set('logged_in', $user);
                    $this->session->set('language', $user['language']);
                }

                $this->session->setFlashdata('message', lang('Users.users msg edit_profile_success'));
            } else {
                $this->session->setFlashdata('error', lang('Users.users error edit_profile_failed'));
            }

            return redirect()->to(site_url('profile'));
        }

        $this->setTitle(lang('Users.users title profile'));

        return $this->render('user/profile_form', [
            'cancel_url'        => base_url(),
            'account'           => $this->user,
            'password_required' => false,
            'save_label'        => lang('Core.core button save'),
        ]);
    }
}
