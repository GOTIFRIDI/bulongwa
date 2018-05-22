<?php


use Phinx\Migration\AbstractMigration;

class UsersTableMigration extends AbstractMigration
{
    public function up()
    {
        $users = $this->table('users');
        $users->addColumn('name', 'string')
              ->addColumn('email', 'string')
              ->addColumn('password', 'string')
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['email'], ['unique' => true])
              ->save();
    }
    public function down()
    {
        $this->dropTable('users');
    }
}
