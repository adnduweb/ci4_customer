<?php

namespace Adnduweb\Ci4_client\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_customer_tables extends Migration
{
    public function up()
    {
        /*
         * customer
         */
        $this->forge->addField([
            'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'             => ['type' => 'BINARY', 'constraint' => 16, 'unique' => true],
            'lastname'         => ['type' => 'VARCHAR',  'constraint' => 255],
            'firstname'        => ['type' => 'VARCHAR',  'constraint' => 255,  'null' => true],
            'email'            => ['type' => 'varchar', 'constraint' => 255],
            'username'         => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'password_hash'    => ['type' => 'varchar', 'constraint' => 255],
            'reset_hash'       => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'reset_at'         => ['type' => 'datetime', 'null' => true],
            'reset_expires'    => ['type' => 'datetime', 'null' => true],
            'activate_hash'    => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'status'           => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'status_message'   => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'active'           => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'force_pass_reset' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'is_new'           => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 1],
            'created_at'       => ['type' => 'datetime', 'null' => true],
            'updated_at'       => ['type' => 'datetime', 'null' => true],
            'deleted_at'       => ['type' => 'datetime', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('username');

        $this->forge->createTable('authf_customer', true);

        /*
         * Auth Login Attempts
         */
        $this->forge->addField([
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ip_address'  => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'email'       => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'customer_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],             // Only for successful logins
            'date'        => ['type' => 'datetime'],
            'success'     => ['type' => 'tinyint', 'constraint' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('email');
        $this->forge->addKey('customer_id');
        // NOTE: Do NOT delete the customer_id or email when the user is deleted for security audits
        $this->forge->createTable('authf_logins', true);

        /*
         * Auth Tokens
         * @see https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
         */
        $this->forge->addField([
            'id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'selector'        => ['type' => 'varchar', 'constraint' => 255],
            'hashedValidator' => ['type' => 'varchar', 'constraint' => 255],
            'customer_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'expires'         => ['type' => 'datetime'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('selector');
        $this->forge->addForeignKey('customer_id', 'authf_customer', 'id', false, 'CASCADE');
        $this->forge->createTable('authf_tokens', true);

        /*
         * Password Reset Table
         */
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email'      => ['type' => 'varchar', 'constraint' => 255],
            'ip_address' => ['type' => 'varchar', 'constraint' => 255],
            'user_agent' => ['type' => 'varchar', 'constraint' => 255],
            'token'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'datetime', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('authf_reset_attempts');

        /*
         * Activation Attempts Table
         */
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ip_address' => ['type' => 'varchar', 'constraint' => 255],
            'user_agent' => ['type' => 'varchar', 'constraint' => 255],
            'token'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'datetime', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('authf_activation_attempts');

        /*
         * Groups Table
         */
        $fields = [
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'varchar', 'constraint' => 255],
            'description' => ['type' => 'varchar', 'constraint' => 255],
            'login_destination' => ['type' => 'VARCHAR', 'constraint' => 255, 'after' => 'description'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'datetime', 'null' => true],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->createTable('authf_groups', true);

        /*
         * Permissions Table
         */
        $fields = [
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'varchar', 'constraint' => 255],
            'description' => ['type' => 'varchar', 'constraint' => 255],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->createTable('authf_permissions', true);

        /*
         * Groups/Permissions Table
         */
        $fields = [
            'group_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'permission_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey(['group_id', 'permission_id']);
        $this->forge->addForeignKey('group_id', 'authf_groups', 'id', false, 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'authf_permissions', 'id', false, 'CASCADE');
        $this->forge->createTable('authf_groups_permissions', true);

        /*
         * customer/Groups Table
         */
        $fields = [
            'group_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'customer_id'  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey(['group_id', 'customer_id']);
        $this->forge->addForeignKey('group_id', 'authf_groups', 'id', false, 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'authf_customer', 'id', false, 'CASCADE');
        $this->forge->createTable('authf_groups_customer', true);

        /*
         * customer/Permissions Table
         */
        $fields = [
            'customer_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'permission_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey(['customer_id', 'permission_id']);
        $this->forge->addForeignKey('customer_id', 'authf_customer', 'id', false, 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'authf_permissions', 'id', false, 'CASCADE');
        $this->forge->createTable('authf_customer_permissions');

    }



    //--------------------------------------------------------------------

    public function down()
    {
        // drop constraints first to prevent errors
        if ($this->db->DBDriver != 'SQLite3') {
            $this->forge->dropForeignKey('authf_tokens', 'authf_tokens_customer_id_foreign');
            $this->forge->dropForeignKey('authf_groups_permissions', 'authf_groups_permissions_group_id_foreign');
            $this->forge->dropForeignKey('authf_groups_permissions', 'authf_groups_permissions_permission_id_foreign');
            $this->forge->dropForeignKey('authf_groups_customer', 'authf_groups_customer_group_id_foreign');
            $this->forge->dropForeignKey('authf_groups_customer', 'authf_groups_customer_customer_id_foreign');
            $this->forge->dropForeignKey('authf_customer_permissions', 'authf_customer_permissions_customer_id_foreign');
            $this->forge->dropForeignKey('authf_customer_permissions', 'authf_customer_permissions_permission_id_foreign');
        }

        $this->forge->dropTable('authf_customer', true);
        $this->forge->dropTable('authf_logins', true);
        $this->forge->dropTable('authf_tokens', true);
        $this->forge->dropTable('authf_reset_attempts', true);
        $this->forge->dropTable('authf_activation_attempts', true);
        $this->forge->dropTable('authf_groups', true);
        $this->forge->dropTable('authf_permissions', true);
        $this->forge->dropTable('authf_groups_permissions', true);
        $this->forge->dropTable('authf_groups_customer', true);
        $this->forge->dropTable('authf_customer_permissions', true);

    }
}
