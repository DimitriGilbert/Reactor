#Command line micro-framework in php

## why another cli tool ?
Because i did not find anything simple enough to use.
This is, in my opinion, quiet simple and will handle most of the nee i can think of concerning cli.

## Create your command line
### extend reactor command class
```php
class Mycommand extends \D2G\Reactor\Command
```
### define your methods and required argument/options
```php
function __construct($args, $opts, $flags)
{
    parent::construct($args, $opts, $flags);
    $this->commands = array(
        '<pre>__DEFAULT__</pre>'=>'__help',
        // the name of your method
        'my_method'=>array(
            // defining expected inputs 
            'expecting'=>array(
                // defining expected arguments
                'args'=>array(
                    array(
                        // used in error message
                        'name'=>'my-argument',
                        // internal function will try to cast the argument to this type
                        'type'=>'type of argument',
                        // command will fail if an expected argument is not present
                        'required'=>true|false
                    )
                ),
                // defining expected options
                'opts'=>array(
                    // option name used in cli prefixed with --
                    'option-name'=>array(
                        // internal function will try to cast the argument to this type
                        'type'=>'string',
                        // command will fail if an expected argument is not present
                        'required'=>true|false
                        // default value assigned to the options if not given
                        'default'=>''
                    )
                )
                // defining expected flags
                'args'=>array(
                    array(
                        // flags
                        'a',
                        'longflag'
                    )
                ),
            ),
        )
    );
}
```
## Usage
### Basic
on *nix
```sh
[php ][vendor/bin/]reactor Your/Command/Class/FQN/With/Slashes:yourCommandMethod firstArg secondArg --myOption=myOptionValue --myOtherOpt="value encapsed" -longFlag -a
```