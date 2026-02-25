<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Db\Adapter\Adapter;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Laminas\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Session\SessionManager;
use Laminas\View\Model\ViewModel;
use Laminas\Crypt\Password\Bcrypt;
use Application\Form\LoginForm;
use Application\Model\UsersTable;

class LoginController extends AbstractActionController
{
    private $usersTable;
    private $adapter;

    public function __construct(UsersTable $usersTable, Adapter $adapter)
    {
        $this->usersTable = $usersTable;
        $this->adapter = $adapter;
    }

    public function indexAction()
    {
        $hash = new Bcrypt(); 
        //var_dump($hash->create('F4nny_2026*'));
        // var_dump($hash->create('R3v1llA.2024!'));
        // var_dump($hash->create('317Gdlsis*'));
        // var_dump($hash->create('V3l4zc*.2022'));
        // var_dump($hash->create('Ur5ul4.2022*'));
        $form = new LoginForm();
        $request = $this->getRequest();
        $auth = new AuthenticationService();
        $error = '';
        if ($auth->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }
        if ($request->isPost()) {
            $post = $request->getPost()->toArray();
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                $authAdapter = new CredentialTreatmentAdapter($this->adapter);

                $authAdapter->setTableName('users')
                    ->setIdentityColumn('username')
                    ->setCredentialColumn('password')
                    ->getDbSelect()->where(['active' => 1]);
                $authAdapter->setIdentity($data['email']);

                $hash = new Bcrypt();
                $userInfo = $this->usersTable->fetchAccountByUsername($data['email']);
                if ($userInfo !== null) {
                    if ($hash->verify($data['password'], $userInfo['password'])) {
                        $authAdapter->setCredential($userInfo['password']);
                    } else {
                        $authAdapter->setCredential('');
                    }
                    $authResult = $auth->authenticate($authAdapter);

                    switch ($authResult->getCode()) {
                        case Result::FAILURE_IDENTITY_NOT_FOUND:
                            $error = 'El Email proporcionado no tiene permiso de ingresar';
                            break;
                        case Result::FAILURE_CREDENTIAL_INVALID:
                            $error = 'Usuario/Contraseña incorrectos';
                            break;
                        case Result::SUCCESS:
                            $sm = new SessionManager();
                            $storage = $auth->getStorage();
                            $storage->write($authAdapter->getResultRowObject(null, ['created_at', 'updated_at']));
                            return $this->redirect()->toRoute('home');
                    }
                } else {
                    $error = 'El Email proporcionado no tiene permiso de ingresar';
                }
            }
        }
        $this->layout()->setTemplate('layout/login');
        $view = new ViewModel(['form' => $form, 'error' => $error]);
        // $view->setTemplate('foo/baz-bat/do-something-crazy');
        return $view;
    }

}