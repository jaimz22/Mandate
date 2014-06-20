Mandate
=============

A command queue that supports command priority, chaining of commands, multiple handlers per command and artifacts

Mandate is pretty simple to use.

A very basic example:
------
##### Files
NumberCommand.php

```php
<?php
use VertigoLabs\Mandate\Command;

class NumberCommand extends Command
{
	protected $number;
   	public function __construct($number)
   	{
   		$this->number = $number;
   	}
   }
```

AddTwoHandler.php
```php
<?php
use VertigoLabs\Mandate\Handler;
user VerigoLabs\Mandate\Command;

class AddTwoHandler extends Handler
{
	public function handle(Command $command)
	{
		if(!is_numeric($command->number)){
			throw new \InvalidArgumentException('Number must be numeric');
		}
		// we'll just var_dump to get output.. as you should not return data from a handler
		var_dump($command->number + 2);	
		return true;
	}
}
```

#### Usage

###### Run commands in order
```php
$mandate = new \VertigoLabs\Mandate\Mandate();

$addTwoHandler = new AddTwoHandler();

$command1 = new NumberCommand(1);
$command1->addHandler($addTwoHandler);

$command2 = new NumberCommand(2);
$command2->addHandler($addTwoHandler);

$mandate->queue($command1)
		->queue($command2)
		->execute();
```

This produces the following output:
```
int(3)
int(4)
```

###### Run commands with priority
The higher the priority the sooner it runs
```php
$mandate = new \VertigoLabs\Mandate\Mandate();

$addTwoHandler = new AddTwoHandler();

$command1 = new NumberCommand(1);
$command1->addHandler($addTwoHandler);

$command2 = new NumberCommand(2);
$command2->addHandler($addTwoHandler);

$command3 = new NumberCommand(4);
$command3->addHandler($addTwoHandler);

$mandate->queue($command1,1)
		->queue($command2,4)
		->queue($command3,2)
		->execute();
```

This produces the following output:
```
int(4)
int(6)
int(3)
```
Notice the run order is $command2, $command3, $command1

###### Reusing commands
You can even reissue a command multiple times:
The higher the priority the sooner it runs
```php
$mandate = new \VertigoLabs\Mandate\Mandate();

$addTwoHandler = new AddTwoHandler();

$command1 = new NumberCommand(1);
$command1->addHandler($addTwoHandler);

$command2 = new NumberCommand(2);
$command2->addHandler($addTwoHandler);

$command3 = new NumberCommand(4);
$command3->addHandler($addTwoHandler);

$mandate->queue($command1,1)
		->queue($command2,4)
		->queue($command3,2)
		->queue($command2,7)
		->queue($command2,0)
		->execute();
```

This produces the following output:
```
int(4)
int(4)
int(6)
int(3)
int(4)
```