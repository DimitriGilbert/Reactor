<?php
namespace D2G\Reactor;

/**
* 
*/
class Reactor
{
    protected $args = array();
    protected $opts = array();
    protected $flags = array();
    protected $command = null;
    protected $callback = null;
    protected $config = array();
    public $command_ = null;
    
    /**
     * constructor
     * @param array $argv command line input array
     * @param string $command default command class
     * @param string $callback default method from command class
     */
    function __construct(array $argv, $command = null, $callback = null)
    {
        if (is_null($command)) {
            if (is_file(APPPATH.'/reactor.json')) {
                $this->config = json_decode(file_get_contents(APPPATH.'/reactor.json'), 1);
            }

            if (!isset($argv[1]) or $argv[1] === '') {
                $argv[1] = 'Command';
            }
            
            $command_ = preg_split('#\:#', $argv[1]);

            if (preg_match('#\/#', $command_[0])) {
                $command_[0] = preg_replace('#\/\/#', '/Commands/', $command_[0]);
                $command_[0] = preg_replace('#\/#', '\\', $command_[0]);
            }
        }
        else {
            $command_ = array($command, $argv[1]);
        }
        
        if (!is_null($callback)) {
            $this->callback = $callback;
        }
        elseif (count($command_) > 1) {
            $this->callback = $command_[1];
        }

        $sliceFrom = 2;
        if (!is_null($command) and !is_null($callback)) {
            $sliceFrom = 1;
        }

        $this->command_ = $this->getAllias($command_[0]);

        $argv = array_slice($argv, $sliceFrom);

        // parse given cli arg
        foreach ($argv as $arg) {
            // matching option
            if (preg_match('#^--#', $arg)) {
                $arg = preg_replace('#--#', '', $arg);
                $arg = preg_split('#=#', $arg);
                if (!isset($this->opts[$arg[0]])) {
                    $this->opts[$arg[0]] = true;

                    if (count($arg) > 1) {
                        $this->opts[$arg[0]] = $arg[1];
                    }
                }
                else {
                    if (!is_array($this->opts[$arg[0]])) {
                        $this->opts[$arg[0]] = array($this->opts[$arg[0]]);
                    }

                    if (count($arg) > 1) {
                        $this->opts[$arg[0]][] = $arg[1];
                    }
                    else {
                        $this->opts[$arg[0]][] = true;
                    }
                }
            }
            // matching flags
            elseif (preg_match('#^-#', $arg)) {
                $arg = preg_replace('#-#', '', $arg);
                $this->flags[$arg] = true;
            }
            // everything else goes in as an argument
            else {
                $this->args[] = $arg;
            }
        }

        $class = $this->getClass();
        if ($class === false) {
            throw new \Exception("Unknown Class : ".$this->command_, 1);
            
        }
        $this->command = new $class($this->args, $this->opts, $this->flags);

        return true;
    }

    /**
     * get a method alias defined in the config file
     * @param  string $command 
     * @return string
     */
    public function getAllias($command)
    {
        if (
            isset($this->config['alliases'])
            and in_array($command, $this->config['alliases'])
        ) {
            $data = $this->config['alliases'][$command];
            $command = $data['command'];
            if (
                is_null($this->callback)
                and isset($data['callback'])
            ) {
                $this->callback = $data['callback'];
            }
        }

        return $command;
    }

    /**
     * get the class name from argument
     * @return string
     */
    public function getClass()
    {
        $class = false;
        if (class_exists($this->command_)) {
            $class = $this->command_;
        }
        elseif (isset($this->config['namespaces'])) {
            foreach ($this->config['namespaces'] as $namespace) {
                if (class_exists($namespace.$this->command_)) {
                    $class = $namespace.$this->command_;
                }
            }
        }

        return $class;
    }

    /**
     * execute the command
     * @return mixed the result of the command
     */
    public function ignite()
    {
        try {
            if (is_null($this->command)) {
                throw new \Exception("this command is unknown : ".$this->command_.'::'.$this->callback, 1);
            }
            return $this->command->__execute($this->callback);
        }
        catch(\Exception $e) {
            die($e->getMessage());
        }
    }
}
