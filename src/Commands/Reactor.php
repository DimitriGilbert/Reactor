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
            'register'=>array(
                'expecting'=>array(
                    'args'=>array(
                        array(
                            'name'=>'namespace',
                            'type'=>'string',
                            'required'=>true
                        )
                    )
                ),
            )
        );

        $this->cli = new \League\CLImate\CLImate;
    }

    public function register()
    {
        $x = 0;
        $config = json_decode(file_get_contents(APPPATH.'reactor.json'), 1);
        while ($this->getArg($x, false))
        {
            if (!in_array($this->getArg($x), $config['namespaces']))
            {
                $config['namespaces'][] = $this->getArg($x);
            }
            $x++;
        }

        file_put_contents(APPPATH.'reactor.json', json_encode($config));
    }
}