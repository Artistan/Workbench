<?php
namespace Artistan\Workbench\Helpers;

class BenchHelper {


    /**
     * The console command object.
     *
     * @var InstallCommand
     */
    protected $cmd = false;

    public function __construct(&$command){
        $this->cmd = $command;
    }
    /**
     * destroys all workbench packages
     */
    public function destroy($packages) {
        $this->cmd->info( "removing all configured packages" );
        foreach($packages as $name){
            // check if exists, also do not remove artistan/workbench cause I am working on it!!!
            while( is_dir( base_path().'/workbench/'.$name) && $name!='artistan/workbench' ){
                $path = explode('/',$name);
                $this->exec( 'rm -rf '.base_path().'/workbench/'.$path[0] );
                clearstatcache ( true, base_path().'/workbench/'.$name );
            }
        }
    }

    public function chStorage(){
        $this->exec('chmod -R 0777 app/storage');
        $this->exec('chown -R apache:apache app/storage');
    }

    /**
     * @param $name
     * @return bool
     * @throws Exception
     */
    public function mkdir($name) {
        $this->cmd->info( "make dir $name" );
        if(!empty($name)){
            if(is_dir(base_path().'/workbench/'.$name)){
                $this->cmd->error( "dir $name exists" );exit;
                return true;
            } else {
                $this->cmd->error( "dir $name make" );
                return mkdir(base_path().'/workbench/'.$name,0777,true);
            }
        } else {
            $this->error('No Git Repo Name Set');
        }
    }

    /**
     * @param $name
     * @param $action
     * @throws Exception
     */
    public function composer($name='',$action='') {
        $this->cmd->info( "composer $name" );
        if(!empty($name) && !empty($action)){
            if(is_dir(base_path().'/workbench/'.$name)){
                chdir(base_path().'/workbench/'.$name);
                echo shell_exec('composer '.$action.' --no-dev');
                echo shell_exec('composer dump-autoload');
            } else {
                $this->error('Package does not exist');
            }
        } else {
            chdir(base_path());
            echo shell_exec('composer dump-autoload');
            echo shell_exec('php artisan dump-autoload');
        }
    }

    /**
     * do not want to be working on package that is installed via composer.
     *
     * @param $packages
     */
    public function composerVendorCleanup($packages) {
        $this->cmd->info( "composerVendorCleanup" );
        foreach($packages as $name){
            if(is_dir(base_path().'/vendor/'.$name)){
                $this->exec('rm -rf '.base_path().'/vendor/'.$name);
            }
        }
    }

    /**
     * @param $name
     * @throws Exception
     */
    public function bower($name='') {
        $this->cmd->info( "bower $name" );
        if(!empty($name)){
            if(is_dir(base_path().'/workbench/'.$name)){
                chdir(base_path().'/workbench/'.$name);
                if(file_exists('./bower.json')){
                    echo shell_exec('bower install');
                } else {
                    $this->cmd->error( "No bower.json to install" );
                }
            } else {
                $this->error('Package does not exist');
            }
        } else {
            chdir(base_path());
            echo shell_exec('bower install');
        }
    }

    /**
     * @param $name
     * @param array $package
     *      array( 'git'=>'' [,'upstream'=>''] )
     * @return string
     * @throws Exception
     */
    public function getGit($name,array $package) {
        $this->cmd->info( "get git $name" );
        if(!empty($package['git'])){
            $this->mkdir($name);
            chdir(base_path().'/workbench/'.$name);
            if(is_dir('.git')){
                // just git pull
                $this->exec('git pull');
                return 'update';
            } else {
                // git clone
                $this->exec('git clone '.$package['git'].' .');
                if(!is_dir('.git')){
                    $this->error('getGit Failed to get git');
                }
                return 'install';
            }
        } else {
            $this->error('No Git Repo Set');
        }
    }


    public function mergeRemote($merge){
        if($merge){
            $this->exec('git merge '.$merge);
        } else {        
            $this->error( "No remote merge requested" );       
        }
    }

    public function fetchRemotes($name,array $remotes){
        foreach($remotes as $remoteName=>$location){
            $this->getRemote($name,$remoteName,$location);
        }
    }
    public function getRemote($name,$remoteName,$location){
        $this->cmd->info( "get git $name :: $remoteName" );
        if(!empty($location)){
            chdir(base_path().'/workbench/'.$name);
            if(is_dir('.git')){
                if(!$this->verifyRemote($name,$remoteName)){
                    $this->exec('git remote add '.$remoteName.' '.$location);
                }
                if($this->verifyRemote($name,$remoteName)){
                    $this->exec('git fetch '.$remoteName);
                }
            } else {
                $this->error('git repo does not exist in '.base_path().'/workbench/'.$name);
            }
        } else {
            $this->error('No Git Remote Set');
        }
    }

    public function verifyRemote($name,$remoteName){
        chdir(base_path().'/workbench/'.$name);

        $str = shell_exec('git remote -v');
        return (strpos($str,$remoteName)!==false);
    }

    // http://stackoverflow.com/questions/1281140/run-process-with-realtime-output-in-php
    /**
     * @param $cmd
     * @param bool $echo
     */
    public function exec($cmd,$echo=true){
        $this->cmd->info( "Command: $cmd" );
        if($echo){
            $descriptor_spec = array(
                0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
                1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
                2 => array("pipe", "w")    // stderr is a pipe that the child will write to
            );
            flush();
            $process = proc_open($cmd, $descriptor_spec, $pipes, realpath('./'), array());
            if (is_resource($process)) {
                while ($s = fgets($pipes[1])) {
                    echo $s."\n";
                    flush();
                }
            }
        } else {
            exec($cmd);
        }
    }

    public function error($message,$exit=false){
        $this->cmd->info( "$message" );
        if($exit){
            exit;
        }
    }
}