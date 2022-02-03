<?php
namespace Deployer;
require 'recipe/laravel.php';
require 'recipe/yarn.php';

// Configuration

set('ssh_type', 'native');
set('ssh_multiplexing', true);

set('repository', 'https://github.com/UMN-LATIS/ChimeIn2.0.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Servers
host('dev')
    ->hostname("cla-chimein-dev.oit.umn.edu")
    ->user('swadm')
    ->stage('development')
    ->set('bin/php', '/opt/rh/rh-php73/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

host('stage')
    ->hostname("cla-chimein-tst.oit.umn.edu")
    ->user('swadm')
    ->stage('stage')
    ->set('bin/php', '/opt/rh/rh-php73/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

host('prod')
    ->hostname("cla-chimein-prd.oit.umn.edu")
    ->user('swadm')
    ->stage('production')
    ->set('bin/php', '/opt/rh/rh-php73/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

task('assets:generate', function() {
  cd('{{release_path}}');
  run('yarn run production');
})->desc('Assets generation');

task('deploy:makecache', function() {
  cd('{{release_path}}');
  run('mkdir -p bootstrap/cache');
})->desc('Make Cache');


task('fix_storage_perms', '
    touch storage/logs/laravel.log
    sudo chown apache storage/logs/laravel.log
    sudo chgrp apache storage/logs/laravel.log
')->desc("Fix Apache Logs");


// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
before('deploy:symlink', 'artisan:migrate');

// js asset generation needs to happen after
// `deploy:shared` so that the assets can use the
// symlinked `.env` file for variables like `MIX_APP_ENV`.
after('deploy:shared', 'yarn:install');
after('yarn:install', 'assets:generate');
after('yarn:install', 'deploy:makecache');
after('artisan:queue:restart', 'fix_storage_perms');
after('artisan:migrate', 'artisan:queue:restart');
