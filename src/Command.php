<?php
namespace D2G\Reactor;

/**
* 
*/
class Command
{
    protected $name = 'Basic Command';
    protected $args = array();
    protected $opts = array();
    protected $flags = array();
    protected $commands = array();
    public $cli = null;
    
    /**
     * constructor
     * @param array $args  cli arguments
     * @param array $opts  cli options
     * @param array $flags cli flags
     */
    function __construct($args, $opts, $flags)
    {
        $this->cli = new \League\CLImate\CLImate;

        $this->args = $args;
        $this->opts = $opts;
        $this->flags = $flags;

        $this->__debug('args :
    '.print_r($args, 1));
        $this->__debug('opts :
    '.print_r($opts, 1));
        $this->__debug('flags :
    '.print_r($flags, 1));
        
        $this->command = null;
        $this->commands = array(
            '__DEFAULT__'=>'__help'
        );
    }

    /**
     * get an argument with its index in the cli input
     * @param  integer $index
     * @param  mixed $default optional the default value if the arg is not found
     * @return mixed
     */
    public function getArg($index, $default = null)
    {
        if (count($this->args) > $index){
            return $this->args[$index];
        }
        elseif ($this->getFlag('i')){
            $str = 'Argument '.$index.' needed :
';
            if (
                isset($this->commands[$this->command])
                and isset($this->commands[$this->command]['expecting']['args'])
                and isset($this->commands[$this->command]['expecting']['args'][$index])
            ){
                $srt = 'Argument '.$this->commands[$this->command]['expecting']['args'][$index]['name'].' needed :
';
            }
            $prompt = $this->cli->input($str);
            $prompt = $prompt->prompt();
            $this->args[$index] = $prompt;
            return $prompt;
        }
        return $default;
    }

    /**
     * return all the arguments of the command
     * @return array 
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * override an argument value
     * @param integer $index
     * @param mixed $value
     * @return boolean
     */
    public function setArg($index, $value)
    {
        if (count($this->args) > $index) {
            $this->args[$index] = $value;
            return true;
        }
        return false;
    }

    /**
     * set the array of arguments
     * @param array $values
     * @return  boolean [<description>]
     */
    public function setArgs($values)
    {
        $this->args[] = $values;
        return true;
    }

    /**
     * get an option from the cli option input
     * @param  string $index the name of the option
     * @param  mixed $default the default value of the option
     * @return mixed
     */
    public function getOpt($index, $default = null)
    {
        if (isset($this->opts[$index])) {
            return $this->opts[$index];
        }
        elseif ($this->getFlag('i')) {
            $prompt = $this->cli->input('Option '.$index.' needed :
');
            $prompt = $prompt->prompt();
            $this->opts[$index] = $prompt;
            return $prompt;
        }
        return $default;
    }

    /**
     * get all input options
     * @return array
     */
    public function getopts()
    {
        return $this->opts;
    }

    /**
     * override the value of an option
     * @param string $index
     * @param mixed $value
     * @return boolean
     */
    public function setOpt($index, $value)
    {
        if (isset($this->opts[$index])) {
            if (!is_array($this->opts[$index])) {
                $this->opts[$index] = array($this->opts[$index]);
            }
        }
        else {
            $this->opts[$index] = $value;
        }
        return true;
    }

    /**
     * set the value of the options' array
     * @param array $values
     */
    public function setOpts($values)
    {
        $this->opts[] = $values;
        return true;
    }

    /**
     * get a flag from the cli input
     * @param  string  $index
     * @param  boolean $default
     * @return boolean
     */
    public function getFlag($index, $default = false)
    {
        if (isset($this->flags[$index])) {
            return true;
        }
        return $default;
    }

    /**
     * get all flags fomr cli input
     * @return array
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * override a flag
     * @param string  $index
     * @param boolean $value
     */
    public function setFlag($index, $value = true)
    {
        $this->flags[$index] = true;
        if (!$value) {
            unset($this->flags[$index]);
        }
        
        return true;
    }

    /**
     * check if the called function has expected argument
     * @param  string  $function
     * @return boolean
     */
    protected function hasExpected($function)
    {
        $checker = $this->commands[$function];
        if (isset($this->commands[$function]['expecting']['args'])) {
            foreach ($this->commands[$function]['expecting']['args'] as $index => $arg) {
                if (is_null($this->getArg($index)) and $this->getFlag('ii')) {
                    $this->getArg($index);
                }
                elseif (is_null($this->getArg($index)) and isset($arg['required']) and $arg['required']) {
                    return false;
                }
            }
        }
        if (isset($this->commands[$function]['expecting']['opts'])) {
            foreach ($this->commands[$function]['expecting']['opts'] as $k => $opt) {
                if (is_null($this->getOpt($k)) and isset($opt['default'])) {
                    $this->opts[$k] = $opt['default'];
                }
                elseif (is_null($this->getOpt($k)) and $this->getFlag('ii')) {
                    $this->getOpt($k);
                }
                elseif (is_null($this->getOpt($k)) and isset($opt['required']) and $opt['required']) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * execute a command
     * @param  string $function the method name
     * @return mixed
     */
    public function __execute($function = null)
    {
        if (is_null($function)) {
            $function = $this->commands['__DEFAULT__'];
        }

        $this->__debug('executing '.$function);

        if (isset($this->commands[$function])) {
            if (isset($thic->commands[$function]['alias'])) {
                $function = $thic->commands[$function]['alias'];
            }

            if (!$this->hasExpected($function)) {
                $this->cli->backgroundRed()->white()->out('Missing inputs...');
                $this->__help();
                return false;
            }
        }

        if (method_exists($this, $function)) {
            $this->command = $function;
            try {
                $this->$function();
            }
            catch (\Exception $e) {
                $this->cli->backgroundRed()->white()->dump($e);
            }
        }
        else {
            $this->cli->backgroundRed()->white()->out($function.' does not exist !');
        }
    }

    /**
     * display the class help
     */
    public function __help()
    {
        $this->__out($this->name);
        $this->__out('Usage');
        foreach ($this->commands as $command => $data) {
            if ($command !== '__DEFAULT__') {
                $this->__out("\n:".$command);
                $line = '';
                if (isset($data['description'])) {
                    $this->__out($data['description']);
                }
                if (isset($data['expecting']) and !is_null($data['expecting'])) {
                    $bol = "\n\t<";
                    $eol = '>';
                    if (isset($arg['required']) and $arg['required'] === true) {
                        $bol = "\n\t[";
                        $eol = ']';
                    }
                    if (isset($data['expecting']['args']) and !empty($data['expecting']['args'])) {
                        $line .= "arguments : \n";
                        foreach ($data['expecting']['args'] as $arg) {
                            $line .= $bol.$arg['name'].':'.$arg['type'].$eol;
                            if (isset($arg['description'])) {
                                $line .= "\t".$arg['description'];
                            }
                        }
                    }
                    if (isset($data['expecting']['opts']) and !empty($data['expecting']['opts'])) {
                        $line .= "options : \n";
                        foreach ($data['expecting']['opts'] as $key => $opt) {
                            $line .=  $bol.'--'.$key.':'.$opt['type'].$eol;
                            if (isset($opt['description'])) {
                                $line .= "\t".$opt['description'];
                            }
                        }
                    }
                    if (isset($data['expecting']['flags']) and !empty($data['expecting']['flags'])) {
                        $line .= "flags : \n";
                        foreach ($data['expecting']['flags'] as $arg) {
                            $line .= $bol.$arg.$eol;
                            if (isset($arg['description'])) {
                                $line .= "\t".$arg['description'];
                            }
                        }
                    }
                }
                $this->cli->green($line);
            }
        }
    }

    /**
     * output a string to the terminal
     * @param  string $str the string to output
     */
    public function __out($str)
    {
        if (!$this->getFlag('q')) {
            $this->cli->out($str);
        }
    }

    /**
     * output a string to the terminal as an error message
     * @param  string $str the string to output
     */
    public function __error($str)
    {
        $this->cli->backgroundRed($str);
    }

    /**
     * output a string to the terminal as a success message
     * @param  string $str the string to output
     */
    public function __success($str)
    {
        $this->cli->green($str);
    }

    /**
     * output a string to the terminal as a notice
     * @param  string $str the string to output
     */
    public function __notice($str)
    {
        $this->cli->blue($str);
    }

    /**
     * output a string to the terminal as a warning
     * @param  string $str the string to output
     */
    public function __warning($str)
    {
        $this->cli->backgroundYellow()->black($str);
    }

    /**
     * output a string to the terminal if verbose is active
     * @param  string $str the string to output
     */
    public function __verbose($str)
    {
        if (
            $this->getFlag('v')
            or $this->getFlag('verbose')
            or $this->getFlag('vv')
            or $this->getFlag('debug')
        ) {
            $this->cli->out($str);
        }
    }

    /**
     * output a string to the terminal if very verbose is active
     * @param  string $str the string to output
     */
    public function __verbose2($str)
    {
        if ($this->getFlag('vv') or $this->getFlag('debug')) {
            $this->cli->out($str);
        }
    }

    /**
     * output a string to the terminal if debug is active
     * @param  string $str the string to output
     */
    public function __debug($str)
    {
        if ($this->getFlag('debug')) {
            $this->cli->backgroundYellow()->black()->out($str);
        }
    }
}
