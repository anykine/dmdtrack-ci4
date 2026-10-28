<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Login check from CI3 Private_Controller: not logged in -> remember the page, go to /login.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        if ($session->get('logged_in')) {
            return null;
        }

        if ($request instanceof IncomingRequest && $request->isAJAX()) {
            return service('response')->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        if ($request->getMethod() === 'GET') {
            // come back here after logging in
            $session->set('redirect', (string) current_url(true));
        }

        return redirect()->to(site_url('login'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
