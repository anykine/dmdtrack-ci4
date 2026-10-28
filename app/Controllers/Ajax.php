<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Public AJAX calls (CI3 controllers/Ajax.php). CSRF is skipped for ajax/* in Config/Filters.php,
 * so nothing here may write to the database.
 */
class Ajax extends BaseController
{
    /**
     * Language picked from the navbar dropdown (core_i18n.js).
     */
    public function setSessionLanguage(): ResponseInterface
    {
        $language = (string) $this->request->getPost('language');

        if (! isset($this->site->languages[$language])) {
            return $this->response->setJSON(['success' => false]);
        }

        $this->session->set('language', $language);

        return $this->response->setJSON(['success' => true]);
    }
}
