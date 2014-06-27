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
    protected $workbench = false;

    /**
     *
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->workbench = new BenchHelper();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->chStorage();
        $packages = \Config::get('workbench::packages');
        if($this->option('destroy')){
            if ($this->confirm('Are you sure you want to remove all current workbench packages? [yes|no]'))
            {
                $this->workbench->destroy();
            }
        }
        foreach($packages as $name=>$package){
            echo "PACKAGE: $name\n";
            if(isset($package['git'])){
                $this->workbench->mkdir($name);
                $action = $this->workbench->getGit($name,$package);
                if($this->option('upstream')){
                    echo "upstream\n";
                    $this->workbench->getUpstream($name,$package,$this->option('merge'));
                }
                if(!$this->option('skipBower')){
                    $this->workbench->bower($name);
                }
                if(!$this->option('skipComposer')){
                    $this->workbench->composer($name,$action);
                }
                if(!$this->option('skipPublish')){
                    $this->call('config:publish', array('argument' => $name));
                    $this->call('asset:publish', array('argument' => $name));
                }
            }
            echo "============================\n\n\n";
        }
        if(!$this->option('skipBower')){
            $this->workbench->bower();
        }
        if(!$this->option('skipComposer')){
            $this->workbench->composer();
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
            array('skipPublish','p', InputOption::VALUE_NONE, 'skip publishing stuff from workbench'),
        );
    }

}