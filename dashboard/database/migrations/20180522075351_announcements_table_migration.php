<?php


use Phinx\Migration\AbstractMigration;

class AnnouncementsTableMigration extends AbstractMigration
{
    public function up()
    {
        $users = $this->table('announcements');
        $users->addColumn('title', 'string')
              ->addColumn('path', 'string')
              ->addColumn('visible', 'boolean', ['default' => false])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->save();
    }

    public function down()
    {
        $this->dropTable('announcements');
    }
}
