<?php
namespace Artistan\Workbench\Helpers;

class BenchHelper {
    /**
     * destroys all workbench packages
     */
    public function destroy() {
        echo "removing all workbench\n";
        $this->exec('rm -rf '.base_path().'/workbench');
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
        echo "make dir $name\n";
        if(!empty($name)){
            //$this->call('command:name', array('argument' => 'foo', '--option' => 'bar'));
            if(is_dir(base_path().'/workbench/'.$name)){
                return true;
            } else {
                return mkdir(base_path().'/workbench/'.$name,0777,true);
            }
        } else {
            throw new Exception('No Git Repo Name Set');
        }
    }

    /**
     * @param $name
     * @param $action
     * @throws Exception
     */
    public function composer($name='',$action='') {
        echo "composer $name\n";
        if(!empty($name) && !empty($action)){
            if(is_dir(base_path().'/workbench/'.$name)){
                chdir(base_path().'/workbench/'.$name);
                echo shell_exec('composer '.$action.' --no-dev');
                echo shell_exec('composer dump-autoload');
            } else {
                throw new Exception('Package does not exist');
            }
        } else {
            chdir(base_path());
            echo shell_exec('composer dump-autoload');
            echo shell_exec('php artisan dump-autoload');
        }
    }

    /**
     * @param $name
     * @throws Exception
     */
    public function bower($name='') {
        echo "bower $name\n";
        if(!empty($name)){
            if(is_dir(base_path().'/workbench/'.$name)){
                chdir(base_path().'/workbench/'.$name);
                if(file_exists('./bower.json')){
                    echo shell_exec('bower install');
                } else {
                    echo "No bower.json to install";
                }
            } else {
                throw new Exception('Package does not exist');
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
        echo "get git $name\n";
        if(!empty($package['git'])){
            //$this->call('command:name', array('argument' => 'foo', '--option' => 'bar'));
            chdir(base_path().'/workbench/'.$name);
            if(is_dir('.git')){
                // just git pull
                $this->exec('git pull');
                return 'update';
            } else {
                // git clone
                $this->exec('git clone '.$package['git'].' .');
                if(!is_dir('.git')){
                    throw new Exception('getGit Failed to get git');
                }
                return 'install';
            }
        } else {
            throw new Exception('No Git Repo Set');
        }
    }

    public function getUpstream($name,array $package,$merge){
        echo "get git $name\n";
        if(!empty($package['git'])){
            if(!empty($package['upstream'])){
                //$this->call('command:name', array('argument' => 'foo', '--option' => 'bar'));
                chdir(base_path().'/workbench/'.$name);
                if(is_dir('.git')){
                    if(!$this->verifyUpstream($name)){
                        $this->exec('git remote add upstream '.$package['upstream']);
                    }
                    if($this->verifyUpstream($name)){
                        $this->exec('git fetch upstream');
                        if($merge){
                            $this->exec('git merge upstream/'.$merge);
                        } else {
                            echo "No upstream merge requested\n";
                        }
                    }

                } else {
                    throw new Exception('git repo does not exist in '.base_path().'/workbench/'.$name);
                }
            } else {
                throw new Exception('No Git Upstream Set');
            }
        } else {
            throw new Exception('No Git Repo Set');
        }
    }

    public function verifyUpstream($name){
        chdir(base_path().'/workbench/'.$name);

        $str = shell_exec('git remote -v');
        return (strpos($str,'upstream')!==false);
    }

    // http://stackoverflow.com/questions/1281140/run-process-with-realtime-output-in-php
    /**
     * @param string $cmd
     */
    public function exec($cmd){
        echo "Command: $cmd\n";
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
    }
}