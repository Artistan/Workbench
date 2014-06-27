<?php
namespace Artistan\Workbench\Commands;

use Artistan\Workbench\Helpers\BenchHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class InstallCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workbench:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
        $this->benchhelper = new BenchHelper();
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
        if($this->option('destroy')){
            if($this->confirm('Are you sure you want to remove all configured workbench packages? [yes|no]'))
            {
                $this->benchhelper->destroy(array_keys($packages));
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
            array('destroy','d', InputOption::VALUE_NONE, 'Destroy current packages.'),
            array('upstream','u', InputOption::VALUE_NONE, 'Fetch upstream.'),
            array('merge','m', InputOption::VALUE_OPTIONAL, 'Merge upstream into this branch.'),
            array('skipComposer','c', InputOption::VALUE_NONE, 'skip composer install/update'),
            array('skipBower','b', InputOption::VALUE_NONE, 'skip bower install/update'),
            array('skipAssets','a', InputOption::VALUE_NONE, 'skip publishing assets from workbench'),
            array('publishConfigs','i', InputOption::VALUE_NONE, 're-publish the config files'),
        );
    }

}
