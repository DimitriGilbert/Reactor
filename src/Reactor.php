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
    public $_command = null;
    
    /**
     * constructor
     * @param array $argv command line input array
     */
    function __construct($argv)
    {
        // if (!is_file(APPPATH.'/reactor.json'))
        // {
        //  file_put_contents(APPPATH.'/reactor.json', json_encode(array(
        //      'namespaces'=>array(
        //          'D2G\\Reactor\\',
        //          'D2G\\Reactor\\Commands\\'
        //      )
        //  )));
        // }
        if (is_file(APPPATH.'/reactor.json')) {
            $this->config = json_decode(file_get_contents(APPPATH.'/reactor.json'), 1);
        }

        if (!isset($argv[1]) or $argv[1] === '') {
            $argv[1] = 'Command';
        }
        
        $_command = preg_split('#\:#', $argv[1]);

        if (count($_command) > 1) {
            $this->callback = $_command[1];
        }

        if (preg_match('#\/#', $_command[0])) {
            $_command[0] = preg_replace('#\/\/#', '/Commands/', $_command[0]);
            $_command[0] = preg_replace('#\/#', '\\', $_command[0]);
        }

        $this->_command = $this->getAllias($_command[0]);

        $argv = array_slice($argv, 2);

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
            throw new \Exception("Unknown Class : ".$this->_command, 1);
            
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
        if (class_exists($this->_command)) {
            $class = $this->_command;
        }
        elseif (isset($this->config['namespaces'])) {
            foreach ($this->config['namespaces'] as $namespace) {
                if (class_exists($namespace.$this->_command)) {
                    $class = $namespace.$this->_command;
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
        if (!is_null($this->command)) {
            return $this->command->__execute($this->callback);
        }
        echo 'Unknown command';
    }
}
