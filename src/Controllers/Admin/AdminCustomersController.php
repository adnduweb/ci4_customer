<?php

namespace Adnduweb\Ci4_customer\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Libraries\AssetsBO;
use App\Libraries\Tools;
use Adnduweb\Ci4_customer\Entities\Customer;
use Adnduweb\Ci4_customer\Models\CustomerModel;
use CodeIgniter\API\ResponseTrait;

class AdminCustomersController extends AdminController
{
    use ResponseTrait;
    use \App\Traits\UuidTrait;
    use \App\Traits\ModuleTrait;

    protected $uuidUser;

    public $module         = true;
    public $name_module    = 'customers';
    public $controller     = 'customers';
    public $item           = 'customer';
    public $type           = 'Adnduweb/Ci4_customer';
    public $pathcontroller = '/customers/list';
    public $fieldList      = 'username';
    public $add            = true;
    public $folderList      = true;


    public function __construct()
    {
        parent::__construct();
        $this->tableModel  = new CustomerModel();
        $this->module           = "customers";
        $this->idModule         = $this->getIdModule();
    }
    public function renderViewList()
    {
        AssetsBO::add_js([$this->get_current_theme_view('controllers/' . $this->controller . '/js/list.js', 'default')]);
        $parent =  parent::renderViewList();
        if (is_object($parent) && $parent->getStatusCode() == 307) {
            return $parent;
        }
        return $parent;
    }

    public function ajaxProcessList()
    {
        $parent = parent::ajaxProcessList();
        return $this->respond($parent, 200, 'liste des clients');
    }

    public function ajaxProcessActionView()
    {
        if ($value = $this->request->getPost('value')) {
            $this->uuidUser = $this->request->getPost('value');
            $idUser = $this->getIdUserByUUID();
            $this->data['user'] = $this->tableModel->find($idUser);
            if (empty($this->data['user'])) {
                throw new \RuntimeException(lang('Admin.object_not_exit'), 404);
            }
            $this->data['getLastConnexions'] = $this->tableModel->getLastConnexions($idUser, 5);

            $modal = view($this->get_current_theme_view('controllers/' . $this->controller . '/__modals/view', 'default'), $this->data);
            $return = [
                'statut' => true,
                'display' => 'modal',
                'reload' => false,
                'message' => $modal,
            ];
            return $this->respond($return, 200, 'affichage de la vue');
        }
        die(1);
    }

    public function renderForm($uuid = null)
    {
        AssetsBO::add_js([$this->get_current_theme_view('controllers/' . $this->controller . '/js/outils.js', 'default')]);
        if (is_null($uuid)) {
            $this->data['form'] = new User($this->request->getPost());
        } else {

            // Je recupére l'id user
            $this->uuidUser = $uuid;
            $idUser = $this->getIdUserByUUID();

            $this->data['form'] = $this->tableModel->where(['id' => $idUser])->first();

            if (empty($this->data['form'])) {
                Tools::set_message('danger', lang('Core.not_user_exist'), lang('Core.warning_error'));
                return redirect()->back()->withInput();
            }
            //Script
            AssetsBO::add_js([$this->get_current_theme_view('controllers/' . $this->controller . '/js/addPermission.js', 'default')]);

            $permissionModel = new \App\Models\PermissionModel();

            // On récupére les permissions du groupe ou des groupes
            foreach ($this->data['form']->auth_groups_users as $groups) {
                $permissionByIdGroupGroup       = $permissionModel->getPermissionsByIdGroup($groups->group_id);
                $this->data['permissionByIdGroupGroup'][$groups->group_id] = [];
                if (!empty($permissionByIdGroupGroup)) {
                    foreach ($permissionByIdGroupGroup as $permissions) {
                        $this->data['permissionByIdGroupGroup'][$permissions->group_id][$permissions->permission_id] = $permissions->permission_id;
                    }
                }
                $permissionByIdGroupGroupUser       = $permissionModel->permissionByIdGroupGroupUser($groups->group_id);
                $this->data['permissionByIdGroupGroupUser'][$groups->group_id] = [];
                if (!empty($permissionByIdGroupGroupUser)) {
                    foreach ($permissionByIdGroupGroupUser as $permissions) {
                        $this->data['permissionByIdGroupGroupUser'][$groups->group_id][$permissions->permission_id] = $permissions->permission_id;
                    }
                }
            }
            // on recupere la liste des dernières connexions au BO
            $this->data['getLastConnexions'] = $this->tableModel->getLastConnexions($idUser, 10);
            $this->data['getSessionsActive'] = $this->auth->getSessionActive($idUser);

            // On récupérer les permissions
            $this->data['permissions']   = $permissionModel->getPermission();

            // Si je ne suis pas un super user et que je modifie mon compte
            if (!inGroups(1, user()->id) && user()->id == $this->data['form']->id) {
                foreach ($this->data['form']->auth_groups_users as $auth_groups_users) {
                    $this->data['id_group'] = $auth_groups_users->group_id;
                }
            }
        }

        // liste des groupes
        $this->data['groups'] = $this->tableModel->getGroups();

        //Liste des company
        $this->data['form']->company = $this->tableModel->getCompany();

        $parent = parent::renderForm($uuid);
        if (is_object($parent) && $parent->getStatusCode() == 307) {
            return $parent;
        }
        return view($this->get_current_theme_view('controllers/users/form', 'default'), $this->data);
    }

    public function postProcessEdit()
    {
        // Je recupére l'id user
        $this->uuidUser =  $this->request->getPost('uuid');
        $idUser = $this->getIdUserByUUID();
        // validate
        $users = new CustomerModel();
        $rules = [
            'email'    => 'required|valid_email|is_unique[users.email,id,' . $idUser . ']',
            'id_group' => 'required',
        ];
        if (!$this->validate($rules)) {
            Tools::set_message('danger', $this->validator->getErrors(), lang('Core.warning_error'));
            return redirect()->back()->withInput();
        }

        // Try to create the user
        $user = new User($this->request->getPost());

        //Vérification du mot de passe
        $password = $this->request->getPost('password');
        $pass_confirm = $this->request->getPost('pass_confirm');
        if (empty($password)) {
            unset($user->password);
            unset($user->pass_confirm);
            unset($user->password_hash);
        } else {
            if ($password != $pass_confirm) {
                Tools::set_message('danger', lang('Core.not_concordance_mote_de_passe'), lang('Core.warning_error'));
                return redirect()->back()->withInput();
            }
        }

        // Format Phone
        $phoneInternationalMobile = Tools::phoneInternational($user->full_phone_mobile);
        if ($phoneInternationalMobile['status'] == 200) {
            $user->phone_mobile = $phoneInternationalMobile['message'];
        } else {
            Tools::set_message('danger', lang('Core.' . $phoneInternationalMobile['message'] . ': mobile'), lang('Core.warning_error'));
            return redirect()->back()->withInput();
        }
        if (!empty($user->full_phone)) {
            $phoneInternationalPhone = Tools::phoneInternational($user->full_phone);
            if ($phoneInternationalPhone['status'] == 200) {
                $user->phone = $phoneInternationalPhone['message'];
            } else {
                Tools::set_message('danger', lang('Core.' . $phoneInternationalPhone['message'] . ': phone'), lang('Core.warning_error'));
                return redirect()->back()->withInput();
            }
        }
        $user->id = $idUser;
        $user->force_pass_reset = ($user->force_pass_reset == '1') ? $user->force_pass_reset : '0';
        $workCrudGroup = $this->workCrudGroup($user);
        if ($workCrudGroup != true) {
            if ($workCrudGroup['status'] == 406) {
                Tools::set_message('danger', $workCrudGroup['message'], lang('Core.warning_error'));
                return redirect()->back()->withInput();
            }
        }

        // Si le connecté n'est égal au user
        if (user()->id != $user->id) {
            $active = $this->request->getPost('active');
            if (!$active) {
                $user->active = '0';
            }
        }

        // On sauvegarde
        if (!$users->save($user)) {
            Tools::set_message('danger', $users->errors(), lang('Core.warning_error'));
            return redirect()->back()->withInput();
        }
        // Si le connecté est égal au user
        if (user()->id == $user->id) {
            $this->saveSettings($this->request->getPost());
        }

        // Success!
        Tools::set_message('success', lang('Core.saved_data'), lang('Core.cool_success'));
        $redirectAfterForm = [
            'url'                   => '/' . env('CI_SITE_AREA') . '/users',
            'action'                => 'edit',
            'submithandler'         => $this->request->getPost('submithandler'),
            'id'                    => $this->request->getPost('uuid'),
        ];
        $this->redirectAfterForm($redirectAfterForm);
    }

    public function postProcessAdd()
    {
        // validate
        $users = new CustomerModel();
        $rules = [
            'email'        => 'required|valid_email|is_unique[users.email,id,' . $this->request->getPost('id') . ']',
            'id_group'     => 'required',
            'password'     => 'required|strong_password',
            'pass_confirm' => 'required|matches[password]',
        ];


        if (!$this->validate($rules)) {
            Tools::set_message('danger', $this->validator->getErrors(), lang('Core.warning_error'));
            return redirect()->back()->withInput();
        }

        // Try to create the user
        $user = new User($this->request->getPost());

        $phoneInternationalMobile = Tools::phoneInternational($user->full_phone_mobile);
        if ($phoneInternationalMobile['status'] == 200) {
            $user->phone_mobile = $phoneInternationalMobile['message'];
        } else {
            Tools::set_message('danger', $phoneInternationalMobile['message'] . ': mobile', lang('Core.warning_error'));
            return redirect()->back()->withInput();
        }
        if (!empty($user->full_phone)) {
            $phoneInternationalPhone = Tools::phoneInternational($user->full_phone);
            if ($phoneInternationalPhone['status'] == 200) {
                $user->phone = $phoneInternationalPhone['message'];
            } else {
                Tools::set_message('danger', $phoneInternationalPhone['message'] . ': phone', lang('Core.warning_error'));
                return redirect()->back()->withInput();
            }
        }
        $user->company_id = user()->company_id;
        //$user->_id = passwdGen('20', 'NUMERIC');
        $user->username = ucfirst(trim(strtolower($user->firstname))) . ucfirst($user->lastname[0]) . time();
        $user->force_pass_reset = ($user->force_pass_reset == '1') ? $user->force_pass_reset : '0';
        $uuid = service('uuid');
        $uuid4 = $uuid->uuid4();
        $user->uuid = $uuid4->toString();

        try {
            $users->save($user);
        } catch (\Exception $e) {
            //Tools::set_message('danger', $users->errors(), lang('Core.warning_error'));
            Tools::set_message('danger', $e->getMessage(), lang('Core.warning_error'));
            return redirect()->back()->withInput();
        }

        $userId = $users->insertID();

        // groups
        $groupModel = new \App\Models\GroupModel();
        $idGroupCurrent = array_flip($this->request->getPost('id_group'));
        foreach ($idGroupCurrent as $k => $v) {
            $groupModel->addUserToGroup($userId, $k);
        }

        // Success!
        Tools::set_message('success', lang('Core.saved_data'), lang('Core.cool_success'));
        $redirectAfterForm = [
            'url'                   => '/' . env('CI_SITE_AREA') . '/users',
            'action'                => 'add',
            'submithandler'         => $this->request->getPost('submithandler'),
            'id'                    => $user->uuid,
        ];
        $this->redirectAfterForm($redirectAfterForm);
    }

    public function ajaxProcessGetPassword()
    {
        helper('common');
        $throttler = \Config\Services::throttler();
        if ($throttler->check($this->request->getIPAddress(), 2, MINUTE) === false) {
            return $this->respond([csrf_token() => csrf_hash(), 'message' => lang('Auth.tooManyRequests', [$throttler->getTokentime()])], 429);
        }
        return $this->respond([csrf_token() => csrf_hash(), 'error' => false, 'message' => lang('Core.Good!!'), 'password' => generer_mot_de_passe(15)]);
    }

    public function workCrudGroup($user)
    {
        $groupModel = new \App\Models\GroupModel();
        $user->groups =  $groupModel->getGroupsForUserLight($user->id);
        //it's me je suis sur mon compte
        if (user()->id == $user->id) {
            // Si je suis super admin
            // je dois rester super admin.
            $idGroupCurrent = array_flip($this->request->getPost('id_group'));
            $firstKey = array_key_first($user->groups);

            if (!isset($idGroupCurrent[$firstKey])) {
                return ['status' => 406, 'message' => lang('Core.not_change_group_principal')];
            }
            foreach ($user->groups as $k => $v) {
                if (!isset($idGroupCurrent[$k])) {
                    $groupModel->removeUserFromGroup($user->id, $k);
                }
            }
            foreach ($idGroupCurrent as $k => $v) {
                if (!isset($user->groups[$k])) {
                    $groupModel->addUserToGroup($user->id, $k);
                }
            }
        } else {
            //ce n'est pas moi
            $idGroupCurrent = array_flip($this->request->getPost('id_group'));
            $firstKey = array_key_first($user->groups);
            foreach ($user->groups as $k => $v) {
                if (!isset($idGroupCurrent[$k])) {
                    $groupModel->removeUserFromGroup($user->id, $k);
                }
            }
            foreach ($idGroupCurrent as $k => $v) {
                if (!isset($user->groups[$k])) {
                    $groupModel->addUserToGroup($user->id, $k);
                }
            }
        }
        return true;
    }

    public function ajaxProcessDelete()
    {
        if ($value = $this->request->getPost('value')) {
            if (!empty($value['selected'])) {
                $itsme = false;
                $comptePrincipal = false;
                $notAccesSuperUser = false;
                foreach ($value['selected'] as $uuid) {

                    // Je recupére l'id user
                    $this->uuidUser =  $uuid;
                    $idUser = $this->getIdUserByUUID();

                    // C'est moi
                    if ($idUser == user()->id) {
                        $itsme = true;
                        break;
                    }

                    // C'est le compte principal
                    if ($idUser == "1") {
                        $comptePrincipal = true;
                        break;
                    }

                    // si c'est un super user et que l'on est pas super user
                    if (inGroups(1, $idUser) &&  !inGroups(1, user()->id)) {
                        $notAccesSuperUser = true;
                        break;
                    }

                    $this->tableModel->deleteAllUser($idUser);
                }
                if ($itsme == true) {
                    return $this->respond(['status' => false, 'type' => 'warning', 'message' => lang('Js.not_delete_propre_compte')], 200);
                } else if ($comptePrincipal == true) {
                    return $this->respond(['status' => false, 'type' => 'warning', 'message' => lang('Js.not_delete_principal_compte')], 200);
                } else if ($notAccesSuperUser == true) {
                    return $this->respond(['status' => false, 'type' => 'warning', 'message' => lang('Js.not_delete_superuser_compte')], 200);
                } else {
                    return $this->respond(['status' => true, 'type' => 'success', 'message' => lang('Js.your_selected_records_have_been_deleted')], 200);
                }
            }
        }
        die(1);
    }

    public function ajaxProcessUpdate()
    {
        if ($value = $this->request->getPost('value')) {
            $data = [];
            if (isset($value['selected']) && !empty($value['selected'])) {
                $itsme = false;
                $comptePrincipal = false;
                $notAccesSuperUser = false;
                foreach ($value['selected'] as $uuid) {
                    // Je recupére l'id user
                    $this->uuidUser =  $uuid;
                    $idUser = $this->getIdUserByUUID();

                    // C'est moi
                    if ($idUser == user()->id) {
                        $itsme = true;
                        break;
                    }

                    // C'est le compte principal
                    if ($idUser == "1") {
                        $comptePrincipal = true;
                        break;
                    }

                    // si c'est un super user et que l'on est pas super user
                    if (inGroups(1, $idUser) &&  !inGroups(1, user()->id)) {
                        $notAccesSuperUser = true;
                        break;
                    }

                    $data[] = [
                        'id'      => $idUser,
                        'active' => $value['active'],
                    ];
                }
            }
            if ($itsme == true) {
                return $this->respond(['status' => false, 'message' => lang('Js.not_desactiveoractive_propre_compte')], 400);
            } else if ($comptePrincipal == true) {
                return $this->respond(['status' => false, 'type' => 'warning', 'message' => lang('Js.not_desactive_principal_compte')], 200);
            } else if ($notAccesSuperUser == true) {
                return $this->respond(['status' => false, 'type' => 'warning', 'message' => lang('Js.not_desactive_superuser_compte')], 200);
            } else {
                if ($this->tableModel->updateBatch($data, 'id')) {
                    return $this->respond(['status' => true, 'message' => lang('Js.your_seleted_records_statuses_have_been_updated')], 200);
                } else {
                    return $this->respond(['status' => false, 'database' => true, 'display' => 'modal', 'message' => lang('Js.aucun_enregistrement_effectue')], 200);
                }
            }
            //print_r($value);
        }
    }

    public function saveSettings(array $posts)
    {

        $setting_notification_email = (!isset($posts['setting_notification_email'])) ? false : true;
        $setting_notification_sms = (!isset($posts['setting_notification_sms'])) ? false : true;
        $setting_connexion_unique = (!isset($posts['setting_connexion_unique'])) ? false : true;
        cache()->delete("settings:contents:{$setting_notification_email}:{user()->id}");
        cache()->delete("settings:contents:{$setting_notification_sms}:{user()->id}");
        cache()->delete("settings:contents:{$setting_connexion_unique}:{user()->id}");
        service('Settings')->setting_notification_email = $setting_notification_email;
        service('Settings')->setting_notification_sms = $setting_notification_sms;
        service('Settings')->setting_connexion_unique = $setting_connexion_unique;
    }

    public function ajaxProcessUpdatePermissions()
    {
        if ($value = $this->request->getPost('value')) {
            $this->permissionModel  = new \App\Models\PermissionModel();
            $details = explode('|', $value);
            if ($this->request->getPost('crud') == 'add') {
                $this->permissionModel->addPermissionToUser($details[1], $details[0]);
            } else {
                $this->permissionModel->removePermissionFromUser($details[1], $details[0]);
            }
            return $this->respond(['status' => true, 'type' => 'Js.cool_success', 'message' => lang('Js.saved_data')], 200);
        }
        die(1);
    }

    public function ajaxProcessDeleteSession()
    {
        if ($value = $this->request->getPost('value')) {
            $this->tableModel->deleteSession($value);
            return $this->respond(['status' => true, 'type' => 'success', 'message' => lang('Core.session_deleted')], 200);
        }
    }
}


// d98138f2-271c-4975-87d4-85945bc11c2f
