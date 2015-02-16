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
	
	function __construct($args, $opts, $flags)
	{
		$this->args = $args;
		$this->opts = $opts;
		$this->flags = $flags;
		$this->command = null;
		$this->commands = array(
			'__DEFAULT__'=>'__help',
			'execute'=>array(
				'expecting'=>null,
			),
			'callback'=>array(
				'expecting'=>null,
			)
		);

		$this->cli = new \League\CLImate\CLImate;
	}

	public function getArg($index, $default = null)
	{
		if (count($this->args) > $index)
		{
			return $this->args[$index];
		}
		return $default;
	}

	public function getOpt($index, $default = null)
	{
		if (isset($this->opts[$index]))
		{
			return $this->opts[$index];
		}
		return $default;
	}

	public function hasExpected($function)
	{
		$checker = $this->commands[$function];
		if (isset($this->commands[$function]['expecting']['args']))
		{
			
		}
		if (isset($this->commands[$function]['expecting']['opts']))
		{
			foreach ($this->commands[$function]['expecting']['opts'] as $k => $opt)
			{
				if (is_null($this->getOpt($k)) and isset($opt['default']))
				{
					$this->opts[$k] = $opt['default'];
				}
			}
		}
		return true;
	}

	public function __execute($function = null)
	{
		if (is_null($function))
		{
			$function = $this->commands['__DEFAULT__'];
		}

		if (isset($this->commands[$function]))
		{
			if (isset($thic->commands[$function]['alias']))
			{
				$function = $thic->commands[$function]['alias'];
			}

			if (!$this->hasExpected($function))
			{
				// TODO
			}
		}

		if (method_exists($this, $function))
		{
			$this->command = $function;
			$this->$function();
		}
	}

	public function __help()
	{
		$this->cli->out($this->name);
		$this->cli->out('Usage');
		foreach ($this->commands as $command => $data)
		{
			if ($command !== '__DEFAULT__')
			{
				$line = ':'.$command;
				if (isset($data['expecting']) and !is_null($data['expecting']))
				{
					if (isset($data['expecting']['args']))
					{
						foreach ($data['expecting']['args'] as $arg)
						{
							if (isset($arg['required']) and $arg['required'] === true)
							{
								$line .= ' <';
							}
							else
							{
								$line .= ' [';
							}

							$line .= $arg['name'].':'.$arg['type'];

							if (isset($arg['required']) and $arg['required'] === true)
							{
								$line .= '>';
							}
							else
							{
								$line .= ']';
							}
						}
					}
					if (isset($data['expecting']['opts']))
					{
						foreach ($data['expecting']['opts'] as $key => $opt)
						{
							if (isset($opt['required']) and $opt['required'] === true)
							{
								$line .= ' <';
							}
							else
							{
								$line .= ' [';
							}

							$line .= '--'.$key.'='.$opt['default'].':'.$opt['type'];

							if (isset($opt['required']) and $opt['required'] === true)
							{
								$line .= '>';
							}
							else
							{
								$line .= ']';
							}
						}
					}
				}
				$this->cli->out($line);
			}
		}
	}

	public function execute()
	{
		$this->cli->out('basic command...');
		$this->cli->out('args : '.print_r($this->args, 1));
		$this->cli->out('opts : '.print_r($this->opts, 1));
		$this->cli->out('flags : '.print_r($this->flags, 1));
	}

	public function callback()
	{
		$this->cli->out('basic command callback...');
		$this->cli->out('args : '.print_r($this->args, 1));
		$this->cli->out('opts : '.print_r($this->opts, 1));
		$this->cli->out('flags : '.print_r($this->flags, 1));
	}
}