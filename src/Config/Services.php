<?php

namespace Adnduweb\Ci4_customer\Config;


use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Config\BaseConfig;

use CodeIgniter\Model;
use Michalsn\Uuid\UuidModel;
use Adnduweb\Ci4_customer\Authorization\FlatAuthorization;
use Adnduweb\Ci4_customer\Models\CustomerModel;
use Adnduweb\Ci4_customer\Models\LoginModel;
use Adnduweb\Ci4_customer\Authorization\GroupModel;
use Adnduweb\Ci4_customer\Authentication\Passwords\PasswordValidator;
use Adnduweb\Ci4_customer\Authentication\Activators\CustomerActivator;
use Adnduweb\Ci4_customer\Authentication\Resetters\CustomerResetter;

class Services extends CoreServices
{

    public static function authenticationcustomer(string $lib = 'local', UuidModel $customerModel = null, Model $loginModel = null, bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('authenticationcustomer', $lib, $customerModel, $loginModel);
        }

        // config() checks first in app/Config
        $config = config('Authcustomer');

        $class = $config->authenticationcustomerLibs[$lib];

        $instance = new $class($config);

        if (empty($customerModel)) {
            $customerModel = new CustomerModel();
        }

        if (empty($loginModel)) {
            $loginModel = new LoginModel();
        }

        return $instance
            ->setCustomerModel($customerModel)
            ->setLoginModel($loginModel);
    }

    public static function authorizationcustomer(Model $groupModel = null, Model $permissionModel = null, UuidModel $customerModel = null, bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('authorizationcustomer', $groupModel, $permissionModel, $customerModel);
        }

        if (is_null($groupModel)) {
            $groupModel = new GroupModel();
        }

        if (is_null($permissionModel)) {
            $permissionModel = new PermissionModel();
        }

        $instance = new FlatAuthorization($groupModel, $permissionModel);

        if (is_null($customerModel)) {
            $customerModel = new CustomerModel();
        }

        return $instance->setcustomerModel($customerModel);
    }

    /**
     * Returns an instance of the password validator.
     *
     * @param null $config
     * @param bool $getShared
     *
     * @return mixed|PasswordValidator
     */
    public static function passwordsCustomer($config = null, bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('passwordsCustomer', $config);
        }

        if (empty($config)) {
            $config = config(Authcustomer::class);
        }

        return new PasswordValidator($config);
    }

    /**
     * Returns an instance of the activator.
     *
     * @param null $config
     * @param bool $getShared
     *
     * @return mixed|Activator
     */
    public static function activatorCustomer($config = null, bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('activatorCustomer', $config);
        }

        if (empty($config)) {
            $config = config(Authcustomer::class);
        }

        return new CustomerActivator($config);
    }

    /**
     * Returns an instance of the resetter.
     *
     * @param null $config
     * @param bool $getShared
     *
     * @return mixed|Activator
     */
    public static function resetterCustomer($config = null, bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('resetterCustomer', $config);
        }

        if (empty($config)) {
            $config = config(Authcustomer::class);
        }

        return new CustomerResetter($config);
    }
}
