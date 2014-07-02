<?php
namespace Artistan\Workbench\Commands;

use Artistan\Workbench\Helpers\BenchHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BranchCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workbench:branch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the currently configured packages from artistan/workspace config.';

    /**
     * workbench helper class
     *
     * @var Workbench
     */
    protected $benchhelper = false;

    /**
     *
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->benchhelper = new BenchHelper($this);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->benchhelper->chStorage();
        $packages = \Config::get('workbench::packages');
        $packageNames = array_keys($packages);
        $destroy = false;
        if($this->option('destroy')){
            if($this->confirm('Are you sure you want to remove all configured workbench packages? [yes|no]'))
            {
                $destroy=true;
            }
        }
        if($this->option('fromBranch')){
            $fromBranch = $this->option('fromBranch');
        } else {
            $fromBranch = 'master';
        }
        if($newBranch = $this->option('createBranch')){
            if($this->confirm("Are you sure you want to switch to branch called $newBranch? [yes|no]"))
            {
                foreach($packages as $name=>$package){
                    $this->info( "PACKAGE: $name" );
                    if(isset($package['git'])){
                        $action = $this->benchhelper->branchGit($name, $package['git'], $newBranch, $fromBranch, $destroy);
                        if( $this->option('remote') && !empty($package['remotes']) && is_array($package['remotes']) ){
                            $this->info( "remotes" );
                            $this->benchhelper->fetchRemotes($name,$package['remotes']);
                        }
                        if($this->option('merge')){
                            $this->benchhelper->mergeRemote($this->option('merge'));
                        }
                        if(!$this->option('skipBower')){
                            $this->benchhelper->bower($name);
                        }
                        if(!$this->option('skipComposer')){
                            $this->benchhelper->composer($packageNames,$name,$action);
                        }
                        if(!$this->option('skipAssets')){
                            $this->call('asset:publish', array('--bench' => $name));
                        }
                        if($this->option('publishConfigs') || $action=='install'){
                            // this should not be done all the time, first time only (install)
                            $this->call('config:publish', array('package' => $name, '--path' => 'workbench/'.$name.'/src/config'));
                        }
                    }
                    $this->info( "============================" );
                }
            }
        }
        $this->info( "do not forget to register your providers!" );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            //array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('createBranch','c', InputOption::VALUE_REQUIRED, 'New Branch Name.'),
            array('fromBranch','f', InputOption::VALUE_OPTIONAL, 'From Branch Name (master).'),
            array('destroy','d', InputOption::VALUE_NONE, 'Destroy current packages.'),
            array('remote','r', InputOption::VALUE_NONE, 'fetch the remote repositories.'),
            array('merge','m', InputOption::VALUE_OPTIONAL, 'Merge {remote name} into this branch.'),
            array('skipComposer','s', InputOption::VALUE_NONE, 'skip composer install/update'),
            array('skipBower','b', InputOption::VALUE_NONE, 'skip bower install/update'),
            array('skipAssets','a', InputOption::VALUE_NONE, 'skip publishing assets from workbench'),
            array('publishConfigs','i', InputOption::VALUE_NONE, 're-publish the config files'),
        );
    }

}
