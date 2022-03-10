<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
Use Schema;

class LTISeeder extends Seeder
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
