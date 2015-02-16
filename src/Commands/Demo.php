<?php
namespace D2G\Reactor\Commands;

/**
* 
*/
class Demo extends \D2G\Reactor\Command
{
	protected $name = 'Demo Command';
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
			'create'=>array(
				'expecting'=>array(
					'args'=>array(
						array(
							'name'=>'name',
							'type'=>'string',
							'required'=>true
						)
					),
					'opts'=>array(
						'path'=>array(
							'type'=>'string',
							'required'=>false,
							'default'=>__DIR__
						),
						'namespace'=>array(
							'type'=>'string',
							'required'=>false,
							'default'=>'D2G\\Reactor\\'
						),
						'methods'=>array(
							'type'=>'string',
							'required'=>false,
							'default'=>'execute'
						)
					)
				),
			)
		);

		$this->cli = new \League\CLImate\CLImate;
	}

	public function create()
	{
		$name = $this->getArg(0);
		$this->cli->out(
			'Creating reactor command '.
			$name
			.' in '.
			$this->getOpt('path')
			.' with namespace '.
			$this->getOpt('namespace')
		);

		$file = $this->getOpt('path').'/'.ucfirst($name).'.php';

		$str = '<?php
namespace '.$this->getOpt('namespace').';

class '.ucfirst($name).' extends \D2G\Reactor\Reactor
{
';
		if (!is_null($this->getOpt('methods')))
		{
			$methods = preg_split('#,#', $this->getOpt('methods'));
			foreach ($methods as $m)
			{
				$str .= '

	public function '.$m.'()
	{
		# code...
	}';
			}
		}

		$str .='
}';
		file_put_contents($file, $str);
	}
}