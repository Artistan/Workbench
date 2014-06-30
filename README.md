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
            'remotes'=>[
                'upstream'=>'git@github.com:vendor/masterPackage.git',
                'upstream2'=>'git@github.com:vendor/masterPackage.git',
            ]
        ],
    ]

### Usage

Command Line Interface

    php artisan workbench:install

        OPTIONS:
            -d          : destroy workbench directory and start over from scratch
            -u          : fetch the remote repositories
            -m{string}  : merge {remote name} with this branch locally
            -c          : skip composer install/update
            -b          : skip bower install
            -p          : skip publishing assets and configs

### Details

* update storage permissions
* get packages config
** de


        $this->benchhelper->chStorage();
        $packages = \Config::get('workbench::packages');
        if($this->option('destroy')){
            if ($this->confirm('Are you sure you want to remove all current workbench packages? [yes|no]'))
            {
                $this->benchhelper->destroy();
            }
        }
        foreach($packages as $name=>$package){
            echo "PACKAGE: $name\n";
            if(isset($package['git'])){
                $this->benchhelper->mkdir($name);
                $action = $this->benchhelper->getGit($name,$package);
                if($this->option('upstream')){
                    echo "upstream\n";
                    $this->benchhelper->getUpstream($name,$package,$this->option('merge'));
                }
                if(!$this->option('skipBower')){
                    $this->benchhelper->bower($name);
                }
                if(!$this->option('skipComposer')){
                    $this->benchhelper->composer($name,$action);
                }
                if(!$this->option('skipAssets')){
                    $this->call('asset:publish', array('--bench' => $name));
                }
                if($this->option('publishConfigs') || $action=='install'){
                    // this should not be done all the time, first time only (install)
                    $this->call('config:publish', array('argument' => $name, '--path' => 'workbench/'.$name.'/src/config'));
                }
            }
            echo "============================\n\n\n";
        }
        if(!$this->option('skipBower')){
            $this->benchhelper->bower();
        }
        if(!$this->option('skipComposer')){
            $this->benchhelper->composer();
            // remove any packages from vendors directory that you are workbenching
            $this->benchhelper->composerVendorCleanup(array_keys($packages));
        }
        //$this->call('command:name', array('argument' => 'foo', '--option' => 'bar'));