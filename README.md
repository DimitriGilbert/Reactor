#Command line micro-framework in php

## Usage
Create your command class etending D2G\Reactor\Command
<pre>
class Mycommand extends \D2G\Reactor\Command
{
...
}
</pre>

Call your Command
<pre>
	./reactor.php Mycommand[:callback] [arguments] [options] [flags]
</pre>