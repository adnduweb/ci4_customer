<?php

namespace Adnduweb\Ci4_customer\Database\Seeds;

use joshtronic\LoremIpsum;

class CustomerSeeder extends \CodeIgniter\Database\Seeder
{
    //\\Adnduweb\\Ci4_customer\\Database\\Seeds\\BlogSeeder
    /**
     * @return mixed|void
     */
    function run()
    {

        $rowsGroups = [
            [
                'id'                => 1,
                'name'              => 'default',
                'description'       => 'default',
                'login_destination' => 'dashboard',
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ],

        ];

        // on insrére les groupes par défault
        $db = \Config\Database::connect();
        foreach ($rowsGroups as $row) {
            $tabRow =  $db->table('authf_groups')->where('name', $row['name'])->get()->getRow();
            if (empty($tabRow)) {
                // No langue - add the row
                $db->table('authf_groups')->insert($row);
            }
        }


        // gestionde l'application
        $rowsBlogTabs = [
            'id_parent'         => 0,
            'depth'             => 2,
            'left'              => 11,
            'right'             => 19,
            'position'          => 1,
            'section'           => 0,
            'module'            => 'Adnduweb\Ci4_customer',
            'class_name'        => '',
            'active'            =>  1,
            'icon'              => '<span class="svg-icon svg-icon-primary svg-icon-2x"><!--begin::Svg Icon | path:/var/www/preview.keenthemes.com/metronic/releases/2020-07-07-181510/theme/html/demo1/dist/../src/media/svg/icons/General/User.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <polygon points="0 0 24 0 24 24 0 24"/>
                                        <path d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                        <path d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z" fill="#000000" fill-rule="nonzero"/>
                                    </g>
                                </svg><!--end::Svg Icon--></span>',
            'slug'             => 'Clients',
            'name_controller'       => ''
        ];

        $rowsBlogTabsLangs = [
            [
                'id_lang'         => 1,
                'name'             => 'clients',
            ],
            [
                'id_lang'         => 2,
                'name'             => 'customers',
            ],
        ];


        $rowsArticlesTabs = [
            'depth'             => 3,
            'left'              => 12,
            'right'             => 13,
            'position'          => 1,
            'section'           => 0,
            'module'            => 'Adnduweb\Ci4_customer',
            'class_name'        => 'AdminCustomers',
            'active'            =>  1,
            'icon'              => '',
            'slug'             => 'list',
            'name_controller'       => ''
        ];

        $rowsArticlesTabsLangs = [
            [
                'id_lang'         => 1,
                'name'             => 'lists',
            ],
            [
                'id_lang'         => 2,
                'name'             => 'lists',
            ],
        ];

        $rowsCatTabs = [
            'depth'             => 3,
            'left'              => 14,
            'right'             => 15,
            'position'          => 1,
            'section'           => 0,
            'module'            => 'Adnduweb\Ci4_customer',
            'class_name'        => 'AdminAdresses',
            'active'            =>  1,
            'icon'              => '',
            'slug'             => 'adresses',
            'name_controller'       => ''
        ];

        $rowsCatTabsLangs = [
            [
                'id_lang'         => 1,
                'name'             => 'adresses',
            ],
            [
                'id_lang'         => 2,
                'name'             => 'adresses',
            ],
        ];

        $rowsManTabs = [
            'depth'             => 3,
            'left'              => 15,
            'right'             => 16,
            'position'          => 4,
            'section'           => 0,
            'module'            => 'Adnduweb\Ci4_customer',
            'class_name'        => 'AdminGroupes',
            'active'            =>  1,
            'icon'              => '',
            'slug'             => 'groupes',
            'name_controller'       => ''
        ];

        $rowsManTabsLangs = [
            [
                'id_lang'         => 1,
                'name'             => 'groupes',
            ],
            [
                'id_lang'         => 2,
                'name'             => 'groupes',
            ],
        ];

        $tabBlog = $db->table('tabs')->where('class_name', $rowsBlogTabs['class_name'])->get()->getRow();
        //print_r($tab); exit;
        if (empty($tabBlog)) {
            // No setting - add the row
            $db->table('tabs')->insert($rowsBlogTabs);
            $newInsert = $db->insertID();
            $i = 0;
            foreach ($rowsBlogTabsLangs as $rowLang) {
                $rowLang['tab_id']   = $newInsert;
                // No setting - add the row
                $db->table('tabs_langs')->insert($rowLang);
                $i++;
            }

            // on insere les articles
            $tabArticles = $db->table('tabs')->where('class_name', $rowsArticlesTabs['class_name'])->get()->getRow();
            //print_r($tab); exit;
            if (empty($tabArticles)) {
                // No setting - add the row
                $rowsArticlesTabs['id_parent']  = $newInsert;
                $db->table('tabs')->insert($rowsArticlesTabs);
                $newInsertArt = $db->insertID();
                $i = 0;
                foreach ($rowsArticlesTabsLangs as $rowLang) {
                    $rowLang['tab_id']   = $newInsertArt;
                    // No setting - add the row
                    $db->table('tabs_langs')->insert($rowLang);
                    $i++;
                }
            }

            // On Insére les categories
            $tabCategorie = $db->table('tabs')->where('class_name', $rowsCatTabs['class_name'])->get()->getRow();
            //print_r($tab); exit;
            if (empty($tabCategorie)) {
                // No setting - add the row
                $rowsCatTabs['id_parent']  = $newInsert;
                $db->table('tabs')->insert($rowsCatTabs);
                $newInsertCat = $db->insertID();
                $i = 0;
                foreach ($rowsCatTabsLangs as $rowLang) {
                    $rowLang['tab_id']   = $newInsertCat;
                    // No setting - add the row
                    $db->table('tabs_langs')->insert($rowLang);
                    $i++;
                }
            }

            // On Insére les brands
            $tabCategorie = $db->table('tabs')->where('class_name', $rowsManTabs['class_name'])->get()->getRow();
            //print_r($tab); exit;
            if (empty($tabCategorie)) {
                // No setting - add the row
                $rowsManTabs['id_parent']  = $newInsert;
                $db->table('tabs')->insert($rowsManTabs);
                $newInsertCat = $db->insertID();
                $i = 0;
                foreach ($rowsManTabsLangs as $rowLang) {
                    $rowLang['tab_id']   = $newInsertCat;
                    // No setting - add the row
                    $db->table('tabs_langs')->insert($rowLang);
                    $i++;
                }
            }

        }


        /**
         *
         * Gestion des permissions
         */
        $rowsPermissionsEcommerce = [
            [
                'name'              => 'Customer::views',
                'description'       => 'Voir les Produits',
                'is_natif'          => '0',
            ],
            [
                'name'              => 'Customer::create',
                'description'       => 'Créer des Produits',
                'is_natif'          => '0',
            ],
            [
                'name'              => 'Customer::edit',
                'description'       => 'Modifier les Produits',
                'is_natif'          => '0',
            ],
            [
                'name'              => 'Customer::delete',
                'description'       => 'Supprimer des articles',
                'is_natif'          => '0',
            ]

        ];

        // On insére le role par default au user
        foreach ($rowsPermissionsEcommerce as $row) {
            $tabRow =  $db->table('auth_permissions')->where(['name' => $row['name']])->get()->getRow();
            if (empty($tabRow)) {
                // No langue - add the row
                $db->table('auth_permissions')->insert($row);
            }
        }

        //Gestion des module
        $rowsModulePages = [
            'name'       => 'customer',
            'namespace'  => 'Adnduweb\Ci4_customer',
            'active'     => 1,
            'version'    => '1.0.2',
            'created_at' =>  date('Y-m-d H:i:s')
        ];

        $tabRow =  $db->table('modules')->where(['name' => $rowsModulePages['name']])->get()->getRow();
        if (empty($tabRow)) {
            // No langue - add the row
            $db->table('modules')->insert($rowsModulePages);
        }
    }
}
