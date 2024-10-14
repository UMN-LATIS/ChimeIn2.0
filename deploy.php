<?php

namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/npm.php';

// Configuration

set('ssh_type', 'native');
set('ssh_multiplexing', true);
set('update_code_strategy', 'clone');
set('repository', 'https://github.com/UMN-LATIS/ChimeIn2.0.git');

set('keep_releases', 5);
set('default_timeout', 600);

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Servers
host('dev')
    ->set('hostname', "cla-chimein-dev.oit.umn.edu")
    ->set('remote_user', 'swadm')
    ->set('labels', ['stage' => 'development'])
    ->set('bin/php', '/opt/remi/php81/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

host('stage')
    ->set('hostname', "cla-chimein-tst.oit.umn.edu")
    ->set('remote_user', 'swadm')
    ->set('labels', ['stage' => 'stage'])
    ->set('bin/php', '/opt/remi/php81/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

host('prod')
    ->set('hostname', "cla-chimein-prd.oit.umn.edu")
    ->set('remote_user', 'swadm')
    ->set('labels', ['stage' => 'production'])
    ->set('bin/php', '/opt/remi/php81/root/usr/bin/php')
    ->set('deploy_path', '/swadm/var/www/html/');

// LARAVEL RECIPE
// https://deployer.org/docs/7.x/recipe/laravel
//
// deploy:prepare
// - deploy:info
// - deploy:setup
// - deploy:lock
// - deploy:release
// - deploy:update_code
after('deploy:update_code', 'npm:install');

task('assets:generate', function () {
    cd('{{release_path}}');
    run('npm run build');
})->desc('Assets generation');
after('npm:install', 'assets:generate');

task('deploy:makecache', function () {
    cd('{{release_path}}');
    run('mkdir -p bootstrap/cache');
})->desc('Make Cache');
after('npm:install', 'deploy:makecache');

// - deploy:shared
// - deploy:writable
// deploy:vendors
// artisan:storage:link
// artisan:config:cache
// artisan:route:cache
// artisan:view:cache
// artisan:event:cache
// artisan:migrate
after('artisan:migrate', 'artisan:queue:restart');

task('composer:private', function() {
    cd('{{release_path}}');
    run('source .env && /swadm/var/www/html/.dep/composer.phar config "http-basic.nova.laravel.com" "$NOVA_USERNAME" "$NOVA_LICENSE_KEY"');
});
  
before('deploy:vendors', 'composer:private');

// deploy:publish
// - deploy:symlink
// - deploy:unlock
// - deploy:cleanup
// - deploy:success

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
