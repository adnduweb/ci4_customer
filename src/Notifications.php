<?php

namespace Adnduweb\Ci4_customer;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Config\Services;
use Adnduweb\Ci4_customer\Customer;
use Adnduweb\Ci4_customer\Models\CustomerModel;
use App\Exceptions\DataException;


class Notifications
{
    public $section = 'event';

    public function __getNotifications()
    {
        $eventAttenteCount = (new CustomerModel())->getAllListeAttNotification(0);
        $eventAttenteDetails = (new CustomerModel())->getAllListeAttNotification(1);

        $response =  [
            'customers' =>
            [
                'count' => count($eventAttenteCount),
                'details' => $eventAttenteDetails,
            ]
        ];
        // print_r($response);
        // exit;
        return $response;
    }  
}
