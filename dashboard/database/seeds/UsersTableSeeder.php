<?php


use Phinx\Seed\AbstractSeed;

class UsersTableSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
         $data = [];
            $data[] = [
                'name'      => 'Admin',
                'email'         => 'admin@bhsi.ac.tz',
                'password'      => sha1('admin'),
            ];
         $this->insert('users', $data); 
    }
}
