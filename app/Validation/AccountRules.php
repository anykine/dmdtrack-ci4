<?php

namespace App\Validation;

/**
 * Validation rules for the account form, shared by registration (User::register) and
 * the user's own profile (Profile::index). Same limits as CI3.
 */
final class AccountRules
{
    /**
     * @param int|null              $userID    the user being edited (excluded from "already taken"), null when registering
     * @param array<string, mixed>  $post      posted form, for the error messages
     * @param array<string, string> $languages Config\Site::$languages
     */
    public static function rules(?int $userID, array $post, array $languages): array
    {
        $ignore   = $userID === null ? '' : ",id,{$userID}";
        $required = $userID === null ? 'required|' : 'permit_empty|';

        return [
            'username' => [
                'label'  => lang('Users.users input username'),
                'rules'  => "required|min_length[5]|max_length[30]|is_unique[users.username{$ignore}]",
                'errors' => ['is_unique' => strip_tags(sprintf(lang('Users.users error username_exists'), (string) ($post['username'] ?? '')))],
            ],
            'first_name' => ['label' => lang('Users.users input first_name'), 'rules' => 'required|min_length[2]|max_length[32]'],
            'last_name'  => ['label' => lang('Users.users input last_name'), 'rules' => 'required|min_length[2]|max_length[32]'],
            'email'      => [
                'label'  => lang('Users.users input email'),
                'rules'  => "required|max_length[128]|valid_email|is_unique[users.email{$ignore}]",
                'errors' => ['is_unique' => strip_tags(sprintf(lang('Users.users error email_exists'), (string) ($post['email'] ?? '')))],
            ],
            'language' => ['label' => lang('Users.users input language'), 'rules' => 'required|in_list[' . implode(',', array_keys($languages)) . ']'],
            // 72 = the login form's limit
            'password'        => ['label' => lang('Users.users input password'), 'rules' => $required . 'min_length[5]|max_length[72]'],
            'password_repeat' => ['label' => lang('Users.users input password_repeat'), 'rules' => $required . 'matches[password]'],
        ];
    }
}
