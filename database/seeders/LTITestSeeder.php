<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
Use Schema;

/*
 * This seeder is only for testing purposes.
 * It is not used in the production environment.
 */ 
class LTITestSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {

    Schema::disableForeignKeyConstraints();
    $this->call(LTISeedLti2ConsumerTableSeeder::class);
    $this->call(LTISeedLti2ResourceLinkTableSeeder::class);
    $this->call(LTISeedLti2UserResultTableSeeder::class);
    $this->call(LTISeedUsersTableSeeder::class);
    $this->call(LTISeedChimesTableSeeder::class);
    $this->call(LTISeedChimeUserTableSeeder::class);
    $this->call(LTISeedFoldersTableSeeder::class);
    $this->call(LTISeedQuestionsTableSeeder::class);
    $this->call(LTISeedSessionsTableSeeder::class);
    $this->call(LTISeedResponsesTableSeeder::class);
    Schema::enableForeignKeyConstraints();
    

    }
}
