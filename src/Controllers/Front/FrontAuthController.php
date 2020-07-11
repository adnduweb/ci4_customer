<?php

namespace Adnduweb\Ci4_customer\Controllers\Front;

use CodeIgniter\API\ResponseTrait;
use Adnduweb\Ci4_customer\Entities\Customer;
use Adnduweb\Ci4_customer\Models\CustomerModel;

class FrontAuthController extends \App\Controllers\Front\FrontController
{

    public $name_module = 'customer';

    protected $authcustomer;
    /**
     * @var Authcustomer
     */
    protected $config;

    /**
     * @var \CodeIgniter\Session\Session
     */
    protected $session;


    public function __construct()
    {
        parent::__construct();
        $this->config = config('Authcustomer');
        $this->authcustomer = service('authenticationcustomer');

        if ($this->authcustomer->check() == false) {
            return redirect()->to('signin');
        }
    }
    public function index()
    {
        //Silent
    }
}
