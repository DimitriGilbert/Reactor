<?php
namespace D2G\Reactor\Commands;

/**
* 
*/
class Reactor extends \D2G\Reactor\Command
{
    protected $name = 'Reactor Management Command';
    protected $args = array();
    protected $opts = array();
    protected $flags = array();
    protected $commands = array();
    public $cli = null;
    
    function __construct($args, $opts, $flags)
    {
        $this->args = $args;
        $this->opts = $opts;
        $this->flags = $flags;
        $this->commands = array(
            '__DEFAULT__'=>'__help',
            'create_bin'=>array(
                'description'=>'create a bin script for composer bin directory',
                'expecting'=>array(
                    'args'=>array(
                        array(
                            'name'=>'name',
                            'description'=>'name of the bin',
                            'type'=>'string',
                            'required'=>true
                        ),
                        array(
                            'name'=>'class',
                            'description'=>'command class of the bin, namespace separated with /',
                            'type'=>'string',
                            'required'=>true
                        ),
                        array(
                            'name'=>'output directory',
                            'description'=>'output directory of the bin script',
                            'type'=>'string',
                            'required'=>false
                        )
                    )
                ),
            ),
            'create_command'=>array(
                'description'=>'create a command class',
                'expecting'=>array(
                    'args'=>array(
                        array(
                            'name'=>'class',
                            'description'=>'name of the command',
                            'type'=>'string',
                            'required'=>true
                        ),
                        array(
                            'name'=>'namespace',
                            'description'=>'namespace for the command class, separated with /',
                            'type'=>'string',
                            'required'=>true
                        ),
                        array(
                            'name'=>'output directory',
                            'description'=>'output directory of the command class',
                            'type'=>'string',
                            'required'=>false
                        )
                    )
                ),
            )
        );

        $this->cli = new \League\CLImate\CLImate;
    }

    public function create_bin()
    {
        $class = preg_replace('#/#', '\\', $this->getArg(1));
        $tpl = file_get_contents(__DIR__.'/../../tpl/bin');

        $binStr = preg_replace('#\<classname\>#', $class, $tpl);
        if (!is_null($this->getArg(2))
            and is_dir($this->getArg(2)) 
            and is_writable($this->getArg(2))
        ) {
            file_put_contents($this->getArg(2).'/'.$this->getArg(0), $binStr);
        }

        $this->__out($binStr);
        return 0;
    }

    public function create_command()
    {
        $class = $this->getArg(0);
        $namespace = preg_replace('#/#', '\\', $this->getArg(1));
        $tpl = file_get_contents(__DIR__.'/../../tpl/Command');

        $cmdStr = preg_replace('#\<classname\>#', $class, $tpl);
        $cmdStr = preg_replace('#\<namespace\>#', $namespace, $cmdStr);
        if (!is_null($this->getArg(2))
            and is_dir($this->getArg(2)) 
            and is_writable($this->getArg(2))
        ) {
            file_put_contents($this->getArg(2).'/'.$this->getArg(0).'.php', $cmdStr);
        }

        $this->__out($cmdStr);
        return 0;
    }
}