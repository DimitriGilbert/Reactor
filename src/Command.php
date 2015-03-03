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

	public function getArg($index, $default = null)
	{
		if (count($this->args) > $index)
		{
			return $this->args[$index];
		}
		elseif ($this->getFlag('i'))
		{
			$str = 'Argument '.$index.' needed :
';
			if (
				isset($this->commands[$this->command])
				and isset($this->commands[$this->command]['expecting']['args'])
				and isset($this->commands[$this->command]['expecting']['args'][$index])
			)
			{
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

	public function setArg($index, $value)
	{
		if (count($this->args) > $index)
		{
			$this->args[$index] = $value;
			return true;
		}
		return false;
	}

	public function setArgs($values)
	{
		$this->args[] = $values;
		return true;
	}

	public function getOpt($index, $default = null)
	{
		if (isset($this->opts[$index]))
		{
			return $this->opts[$index];
		}
		elseif ($this->getFlag('i'))
		{
			$prompt = $this->cli->input('Option '.$index.' needed :
');
			$prompt = $prompt->prompt();
			$this->opts[$index] = $prompt;
			return $prompt;
		}
		return $default;
	}

	public function setOpt($index, $value)
	{
		$this->opts[$index] = $value;
		return true;
	}

	public function setOpts($values)
	{
		$this->opts[] = $values;
		return true;
	}

	public function getFlag($index, $default = false)
	{
		if (isset($this->flags[$index]))
		{
			return true;
		}
		return $default;
	}

	public function setFlag($index, $value = true)
	{
		$this->flags[$index] = true;
		if (!$value)
		{
			unset($this->flags[$index]);
		}
		
		return true;
	}

	public function hasExpected($function)
	{
		$checker = $this->commands[$function];
		if (isset($this->commands[$function]['expecting']['args']))
		{
			foreach ($this->commands[$function]['expecting']['args'] as $index => $arg)
			{
				if (
					$this->getFlag('ii')
					and isset($opt['required'])
					and $opt['required']
				)
				{
					$this->getArg($index);
				}
				else
				{
					return false;
				}
			}
		}
		if (isset($this->commands[$function]['expecting']['opts']))
		{
			foreach ($this->commands[$function]['expecting']['opts'] as $k => $opt)
			{
				if (is_null($this->getOpt($k)) and isset($opt['default']))
				{
					$this->opts[$k] = $opt['default'];
				}
				elseif (
					$this->getFlag('ii')
					and is_null($this->getOpt($k))
					and !isset($opt['default'])
					and (
						!isset($opt['required'])
						or !$opt['required']
					)
				)
				{
					$this->getOpt($k);
				}
				else
				{
					return false;
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

		$this->__debug('executing '.$function);

		if (isset($this->commands[$function]))
		{
			if (isset($thic->commands[$function]['alias']))
			{
				$function = $thic->commands[$function]['alias'];
			}

			if (!$this->hasExpected($function))
			{
				$this->cli->backgroundRed()->white()->out('Missing inputs...');
				$this->__help();
				return false;
			}
		}

		if (method_exists($this, $function))
		{
			$this->command = $function;
			try 
			{
				$this->$function();
			}
			catch (Exception $e)
			{
				$this->cli->backgroundRed()->white()->dump($e);
			}
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
								$line .= '
	<';
							}
							else
							{
								$line .= '
	[';
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
								$line .= '
	<';
							}
							else
							{
								$line .= '
	[';
							}

							$line .= '--'.$key./*'='.isset($opt['default'])?$opt['default']:''.*/':'.$opt['type'];

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

	public function __out($str)
	{
		if (!$this->getFlag('q'))
		{
			$this->cli->out($str);
		}
	}

	public function __verbose($str)
	{
		if (
			$this->getFlag('v')
			or $this->getFlag('verbose')
			or $this->getFlag('vv')
			or $this->getFlag('debug')
		)
		{
			$this->cli->out($str);
		}
	}

	public function __verbose2($str)
	{
		if ($this->getFlag('vv') or $this->getFlag('debug'))
		{
			$this->cli->out($str);
		}
	}

	public function __debug($str)
	{
		if ($this->getFlag('debug'))
		{
			$this->cli->backgroundYellow()->black()->out($str);
		}
	}
}