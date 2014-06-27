<?php
namespace Artistan\Workbench\Commands;

use Artistan\Workbench\Helpers\BenchHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DevelopCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workbench:develop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup dev for artistan/workbench.';

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
        // setup for development of this package
        echo "This will assist by composer dump and such\n please pass -c to publish config. do not edit this config unless adding features.\n\n";

        // publish config to package configs...
        if($this->option('publishConfig')){
            $this->benchhelper->exec('php artisan config:publish --path="workbench/artistan/workbench/src/config" artistan/workbench');
            echo "update configs in app/config/packages/artistan/workbench/config.php\n";
        }
        $this->benchhelper->composerVendorCleanup(['artistan/workbench']);

        $this->benchhelper->composer('artistan/workbench','update');
        $this->benchhelper->composer();
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
            array('publishConfig','c', InputOption::VALUE_NONE, 'Publish the config file.'),
        );
    }

}
