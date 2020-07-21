<?php

namespace Adnduweb\Ci4_customer\Models;

use Michalsn\Uuid\UuidModel;
use Adnduweb\Ci4_customer\Models\GroupModel;
use Adnduweb\Ci4_customer\Entities\Customer;

class CustomerModel extends UuidModel
{
    use \Tatter\Relations\Traits\ModelTrait, \Adnduweb\Ci4_logs\Traits\AuditsTrait, \App\Models\BaseModel;

    protected $table      = 'authf_customer';
    protected $tableLang  = false;
    protected $primaryKey = 'id';
    protected $with       = ['authf_groups_customer'];
    protected $without    = [];
    protected $uuidFields = ['uuid'];

    protected $returnType = Customer::class;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'uuid', 'lastname', 'firstname', 'email', 'password_hash', 'reset_hash', 'reset_at', 'reset_expires', 'activate_hash',
        'status', 'status_message', 'active', 'force_pass_reset', 'permissions', 'deleted_at',
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'email'         => 'required|valid_email|is_unique[authf_customer.email,id,{id}]',
        'password_hash' => 'required',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    protected $afterInsert = ['addToGroup'];

    /**
     * The id of a group to assign.
     * Set internally by withGroup.
     * @var int
     */
    protected $assignGroup;

   
    protected $searchKtDatatable  = ['fonction', 'lastname', 'firstname', 'email', 'created_at'];

    public function __construct()
    {
        parent::__construct();
        $this->builder      = $this->db->table('authf_customer');
        $this->authf_groups_customer      = $this->db->table('authf_groups_customer');
    }

    public function getAllList(int $page, int $perpage, array $sort, array $query)
    {
        $usersRow = $this->getBaseAllList($page, $perpage, $sort, $query, $this->searchKtDatatable);

        if (!empty($usersRow)) {
            $i = 0;
            foreach ($usersRow as &$row) {
                $this->authf_groups_customer->select();
                $this->authf_groups_customer->join('authf_groups', 'authf_groups.id = authf_groups_customer.group_id');
                $this->authf_groups_customer->where('customer_id', $row->id);
                $authf_groups_customer = $this->authf_groups_customer->get();
                $row->group = $authf_groups_customer->getResult();
                // if (!empty($row->group)) {
                //     foreach ($row->group as $group) {
                //         if ($group->group_id == '1') {
                //             unset($usersRow[$i]);
                //         }
                //     }
                // }
                $i++;
            }
        }
        //array_splice($usersRow, count($usersRow));

        return $usersRow;

    }


    /**
     * Logs a password reset attempt for posterity sake.
     *
     * @param string      $email
     * @param string|null $token
     * @param string|null $ipAddress
     * @param string|null $userAgent
     */
    public function logResetAttempt(string $email, string $token = null, string $ipAddress = null, string $userAgent = null)
    {
        $this->db->table('authf_reset_attempts')->insert([
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Logs an activation attempt for posterity sake.
     *
     * @param string|null $token
     * @param string|null $ipAddress
     * @param string|null $userAgent
     */
    public function logActivationAttempt(string $token = null, string $ipAddress = null, string $userAgent = null)
    {
        $this->db->table('authf_activation_attempts')->insert([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Sets the group to assign any users created.
     *
     * @param string $groupName
     *
     * @return $this
     */
    public function withGroup(string $groupName)
    {
        $group = $this->db->table('authf_groups')->where('name', $groupName)->get()->getFirstRow();

        $this->assignGroup = $group->id;

        return $this;
    }

    /**
     * Clears the group to assign to newly created users.
     *
     * @return $this
     */
    public function clearGroup()
    {
        $this->assignGroup = null;

        return $this;
    }

    /**
     * If a default role is assigned in Config\Auth, will
     * add this user to that group. Will do nothing
     * if the group cannot be found.
     *
     * @param $data
     *
     * @return mixed
     */
    protected function addToGroup($data)
    {
        if (is_numeric($this->assignGroup)) {
            $groupModel = model(GroupModel::class);
            $groupModel->addCustomerToGroup($data['id'], $this->assignGroup);
        }

        return $data;
    }
}
