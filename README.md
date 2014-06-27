Workbench Helper for Laravel
==============

adds configurable workbench management commands to laravel

### Composer Configuration

Include the artistan workbench package as a dependency in your `composer.json` [Packagist](https://packagist.org/packages/artistan/workbench):

    "artistan/workbench": "*"

### Installation

Once you update your composer configuration, run `composer install` to download the dependencies.

Add a ServiceProvider to your providers array in `app/config/app.php`:

	'providers' => array(

		'Artistan\Workbench\WorkbenchServiceProvider',

	)

	Finally, publish the configuration files via `php artisan config:publish artistan/workbench`.

### Configuration

Once you have published the configuration files, you can add your workbench package options to the config file in
`app/config/packages/artistan/workbench/config.php`.

    /*
     * config for commands workbench:*
     */
    'packages'=>[
        'vendor/package'=>[
            'git'=>'git@github.com:vendor/devPackage.git',
            'upstream'=>'git@github.com:vendor/masterPackage.git',
        ],
    ]

### Usage

Command Line Interface

    php artisan workbench:install

        OPTIONS:
            -d          : destroy workbench directory and start over from scratch
            -u          : fetch the upstream repository
            -m{string}  : merge upstream with this branch locally
            -c          : skip composer install/update
            -b          : skip bower install
            -p          : skip publishing assets and configs
