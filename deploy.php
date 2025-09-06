<?php

namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/npm.php';
require 'contrib/cachetool.php';

set('cachetool_args', '--tmp-dir=/var/www/chimein');
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
// Servers
$devHost = 'cla-chimein-r9-dev.oit.umn.edu';
$tstHost = 'cla-chimein-r9-tst.oit.umn.edu';
$prodHost = 'cla-chimein-r9-prd.oit.umn.edu';


host('dev')
  ->set('hostname', $devHost)
  ->set('remote_user', 'latis_deploy')
  ->set('labels', ['stage' => 'development'])
  // ->identityFile()
  ->set('deploy_path', '/var/www/chimein/');

host('stage')
  ->set('hostname', $tstHost)
  ->set('remote_user', 'latis_deploy')
  ->set('labels', ['stage' => 'stage'])
  // ->identityFile()
  ->set('deploy_path', '/var/www/chimein/');

host('prod')
  ->set('hostname', $prodHost)
  ->set('remote_user', 'latis_deploy')
  ->set('labels', ['stage' => 'production'])
  ->set('deploy_path', '/var/www/chimein/');

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
after('deploy:vendors', 'assets:generate');

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
task('composer:private', function () {
    cd('{{release_path}}');
    run('source .env && {{bin/composer}} config "http-basic.nova.laravel.com" "$NOVA_USERNAME" "$NOVA_LICENSE_KEY"');
});
  
before('deploy:vendors', 'composer:private');

#add a task to restart the queue-worker and laravel-reverb systemd services
task('restart:services', function () {
    run('sudo service laravel-reverb restart');
    run('sudo service queue-worker restart');
})->desc('Restart services');
after('deploy:symlink', 'restart:services');


// deploy:publish
// - deploy:symlink
// - deploy:unlock
// - deploy:cleanup
// - deploy:success

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
after('deploy:symlink', 'cachetool:clear:opcache');