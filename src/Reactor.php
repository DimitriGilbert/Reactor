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
	protected $config = null;
	public $_command = null;
	
	function __construct($argv)
	{
		if (!is_file(APPPATH.'reactor.json'))
		{
			file_put_contents(APPPATH.'/reactor.json', json_encode(array(
				'namespaces'=>array(
					'D2G\\Reactor\\',
					'D2G\\Reactor\\Commands\\'
				)
			)));
		}

		$this->config = json_decode(file_get_contents(APPPATH.'/reactor.json'), 1);
		$_command = preg_split('#\:#', $argv[1]);

		if (count($_command) > 1)
		{
			$this->callback = $_command[1];
		}

		$this->_command = $this->getAllias($_command[0]);

		$argv = array_slice($argv, 2);

		foreach ($argv as $arg)
		{
			if (preg_match('#^--#', $arg))
			{
				$arg = preg_replace('#--#', '', $arg);
				$arg = preg_split('#=#', $arg);
				$this->opts[$arg[0]] = true;

				if (count($arg) > 1)
				{
					$this->opts[$arg[0]] = $arg[1];
				}
			}
			elseif (preg_match('#^-#', $arg))
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
			throw new \Exception("Unknown Class : ".$this->_command, 1);
			
		}
		$this->command = new $class($this->args, $this->opts, $this->flags);

		return true;
	}

	public function getAllias($command)
	{
		if (
			isset($this->config['alliases'])
			and in_array($command, $this->config['alliases'])
		)
		{
			$data = $this->config['alliases'][$command];
			$command = $data['command'];
			if (
				is_null($this->callback)
				and isset($data['callback'])
			)
			{
				$this->callback = $data['callback'];
			}
		}

		return $command;
	}

	public function getClass()
	{
		$class = false;
		if (isset($this->config['namespaces']))
		{
			foreach ($this->config['namespaces'] as $namespace)
			{
				if (class_exists($namespace.$this->_command))
				{
					$class = $namespace.$this->_command;
				}
			}
		}
		
		if ($class === false and class_exists($this->_command))
		{
			$class = $this->_command;
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