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
	public $_command = null;
	
	function __construct($argv)
	{
		$_command = preg_split('#\:#', $argv[1]);

		if (count($_command) > 1)
		{
			$this->callback = $_command[1];
		}
		$_command = $_command[0];

		$this->_command = $_command;

		$argv = array_slice($argv, 2);

		foreach ($argv as $arg)
		{
			if (preg_match('#--#', $arg))
			{
				$arg = preg_replace('#--#', '', $arg);
				$arg = preg_split('#=#', $arg);
				$this->opts[$arg[0]] = true;

				if (count($arg) > 1)
				{
					$this->opts[$arg[0]] = $arg[1];
				}
			}
			elseif (preg_match('#-#', $arg))
			{
				$arg = preg_replace('#-#', '', $arg);
				$this->flags[$arg] = true;
			}
			else
			{
				$this->args[] = $arg;
			}
		}

		$class = $this->getClass();
		if ($class === false)
		{
			throw new Exception("Unknown Class : ".$this->_command, 1);
			
		}
		$this->command = new $class($this->args, $this->opts, $this->flags);

		return true;
	}

	public function getClass()
	{
		if (class_exists('D2G\\Reactor\\'.$this->_command))
		{
			$class = 'D2G\\Reactor\\'.$this->_command;
		}
		elseif (class_exists('D2G\\Reactor\\Commands\\'.$this->_command))
		{
			$class = 'D2G\\Reactor\\Commands\\'.$this->_command;
		}
		elseif (class_exists($this->_command))
		{
			$class = $this->_command;
		}
		else
		{
			// die($this->_command);
			return false;
		}

		return $class;
	}

	public function ignite()
	{
		if (!is_null($this->command))
		{
			return $this->command->__execute($this->callback);
		}
		echo 'Unknown command';
	}
}