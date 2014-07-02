<?php
namespace Artistan\Workbench\Commands;

use Artistan\Workbench\Helpers\BenchHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workbench:publish';

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
        foreach($packages as $name=>$package){
            $this->info( "PACKAGE: $name" );
            if(isset($package['git'])){
                if(!$this->option('skipAssets')){
                    $this->call('asset:publish', array('--bench' => $name));
                }
                if($this->option('publishConfigs')){
                    // this should not be done all the time, first time only (install)
                    $this->call('config:publish', array('package' => $name, '--path' => 'workbench/'.$name.'/src/config'));
                }
            }
            $this->info( "============================" );
        }
        $this->info( "publishing complete!" );
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
            array('skipAssets','a', InputOption::VALUE_NONE, 'skip publishing assets from workbench'),
            array('publishConfigs','i', InputOption::VALUE_NONE, 're-publish the config files'),
        );
    }

}
