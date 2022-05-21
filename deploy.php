<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/yarn.php';

// Configuration

set('ssh_type', 'native');
set('ssh_multiplexing', true);

set('repository', 'https://github.com/UMN-LATIS/ChimeIn2.0.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Servers

host('dev')
    ->set('hostname',"cla-chimein-dev.oit.umn.edu")
    ->set('remote_user', 'swadm')
    ->set('labels', ['stage' => 'development'])
    ->set('bin/php', '/opt/remi/php81/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

host('stage')
    ->set('hostname',"cla-chimein-tst.oit.umn.edu")
    ->set('remote_user','swadm')
    ->set('labels', ['stage' => 'stage'])
    ->set('bin/php', '/opt/remi/php81/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

host('prod')
    ->set('hostname', "cla-chimein-prd.oit.umn.edu")
    ->set('remote_user','swadm')
    ->set('labels', ['stage' => 'production'])
    ->set('bin/php', '/opt/remi/php81/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

task('assets:generate', function() {
  cd('{{release_path}}');
  run('yarn run production');
})->desc('Assets generation');

task('deploy:makecache', function() {
  cd('{{release_path}}');
  run('mkdir -p bootstrap/cache');
})->desc('Make Cache');


// task('fix_storage_perms', function () {
//   cd('{{release_path}}');
//   run('touch storage/logs/laravel.log');
//   run('sudo chown apache storage/logs/laravel.log');
//   run('sudo chgrp apache storage/logs/laravel.log');
// })->desc("Fix Apache Logs");

// after('artisan:migrate', 'fix_storage_perms');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
before('deploy:symlink', 'artisan:migrate');
after('deploy:update_code', 'yarn:install');
after('yarn:install', 'assets:generate');
after('yarn:install', 'deploy:makecache');
// after('artisan:queue:restart', 'fix_storage_perms');
after('artisan:migrate', 'artisan:queue:restart');
