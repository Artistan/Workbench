<?php
namespace Artistan\Workbench\Commands;

use Artistan\Workbench\Helpers\BenchHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LaunchCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workbench:launch';

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
        echo "This will publish assets & configs.\n";
        echo "Optionally publish views (-v).\n";
        if($this->confirm('Do you wish to launch your configured packages from vendors directory?'))
        {
            $this->benchhelper->chStorage();
            $packages = \Config::get('workbench::packages');
            foreach($packages as $name=>$package){
                echo "--- $name ---\n";
                try{
                    $this->benchhelper->exec('php artisan config:publish '.$name);
                    echo "published configs\n";
                } catch(\Exception $e) {
                    echo "Failed to publish configs.\n";
                }
                try{
                    $this->benchhelper->exec('php artisan asset:publish '.$name);
                    echo "published assets\n";
                } catch(\Exception $e) {
                    echo "Failed to publish assets.\n";
                }
                if($this->option('publishViews')){
                    try{
                        $this->benchhelper->exec('php artisan view:publish '.$name);
                        echo "published views\n";
                    } catch(\Exception $e) {
                        echo "Failed to publish views.\n";
                    }
                } else {
                    echo "skipping views\n";
                }
            }
        }
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
            array('publishViews','v', InputOption::VALUE_NONE, 'Publish the views also.'),
        );
    }

}
