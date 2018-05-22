<?php


use Phinx\Seed\AbstractSeed;

class AnnouncementsTableSeeder extends AbstractSeed
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
         $faker = Faker\Factory::create();
         $data = [];
         for ($i = 0; $i < 5; $i++) {
            $data[] = [
                'title'      => $faker->sentence,
                'path'       => $faker->uuid . '.pdf',
            ];
         }
         $this->insert('announcements', $data);
    }
}
