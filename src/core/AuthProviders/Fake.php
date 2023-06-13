<?php

namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;

class Fake extends \MapasCulturais\AuthProvider {

    protected function _init() {
        $app = App::i();

        // add actions to auth controller
        $app->hook('GET(auth.index)', function () use($app) {

            $searchQuery = trim($app->request()->get('q', ''));
            if ($searchQuery !== '') {
                $searchQuery = '%' . $searchQuery . '%';
            }

            $users = $app->em->getConnection()->fetchAll("
                SELECT
                    u.id,
                    u.email,
                    a.name AS profile,
                    r.name AS role_name
                FROM
                    usr u
                    LEFT JOIN agent a ON a.id = u.profile_id
                    LEFT JOIN role r ON r.usr_id = u.id
                WHERE (? = '' OR u.email ILIKE ? OR a.name ILIKE ? OR r.name ILIKE ?)
                ORDER BY profile, email, role_name
                LIMIT 10", [
                $searchQuery, $searchQuery, $searchQuery, $searchQuery
            ]);

            if ($app->request()->isXhr()) {
                $this->json($users);
            } else {
                $this->render('fake-authentication', [
                    'users' => $users,
                    'form_action' => $app->createUrl('auth', 'fakeLogin'),
                    'new_user_form_action' => $app->createUrl('user')
                ]);
            }
        });

        $app->hook('GET(auth.fakeLogin)', function () use($app) {
            $app->auth->processResponse();

            if ($app->auth->isUserAuthenticated()) {        
                $url = $app->auth->getRedirectPath();
                $app->redirect($url);
                
            } else {
                $app->redirect($this->createUrl(''));
            }
        });

        $app->hook('POST(user.index)', function() use($app) {
            $new_user = $app->auth->createUser($this->postData);
            $app->redirect($app->createUrl('auth', 'fakeLogin') . '?fake_authentication_user_id=' . $new_user->id);
        });
    }

    public function _cleanUserSession() {
        unset($_SESSION['auth.fakeAuthenticationUserId']);
    }

    public function _getAuthenticatedUser() {
        $user = null;
        if (key_exists('auth.fakeAuthenticationUserId', $_SESSION)) {
            $user_id = $_SESSION['auth.fakeAuthenticationUserId'];
            $user = App::i()->repo("User")->find($user_id);
            return $user;
        } else {
            return null;
        }
    }

    /**
     * Process the Opauth authentication response and creates the user if it not exists
     * @return boolean true if the response is valid or false if the response is not valid
     */
    public function processResponse() {
        if (key_exists('fake_authentication_user_id', $_GET)) {
            $_SESSION['auth.fakeAuthenticationUserId'] = $_GET['fake_authentication_user_id'];
            $this->_setAuthenticatedUser($this->_getAuthenticatedUser());
            App::i()->applyHook('auth.successful');
        }
    }

    protected function _createUser($data) {
        $app = App::i();
        $app->disableAccessControl();
        $user = new \MapasCulturais\Entities\User;

        $user->authProvider = 'Fake';
        $user->authUid = uniqid('fake-');
        $user->email = $data['email'];

        $app->em->persist($user);
        $app->em->flush();

        $agent = new \MapasCulturais\Entities\Agent($user);
        $agent->name = $data['name'];
        //$agent->status = 0;
        $agent->save();

        $app->em->flush();

        $user->profile = $agent;
        $user->save(true);

        $app->enableAccessControl();

        return $user;
    }

    public function login($user_id) {
        $_SESSION['auth.fakeAuthenticationUserId'] = $user_id;
        $this->_setAuthenticatedUser($this->_getAuthenticatedUser());
    }

}
