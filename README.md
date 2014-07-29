Mandate
=============
[![Build Status](https://travis-ci.org/jaimz22/Mandate.svg?branch=master)](https://travis-ci.org/jaimz22/Mandate)
[![Coverage Status](https://coveralls.io/repos/jaimz22/Mandate/badge.png)](https://coveralls.io/r/jaimz22/Mandate)

A command queue that supports command priority, chaining of commands, multiple handlers per command and artifacts

Mandate is pretty simple to use.

Usage
-----
##### Files
Below are all the files that will be used in the examples

NumberCommand.php

```php

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

A very basic example:
------
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

Command Callbacks:
------

#### Usage

Commands can run callback functions upon successful execution, or failure:

##### Closures as callbacks
```php
 
	$command1 = new NumberCommand(1);
	$command1->onSuccess(function(){
							echo "Command 1 completed successfully!";
						});
```

```php
 
	$command1 = new NumberCommand(1);
	$command1->onFailure(function(){
							echo "Oops! I failed :(";
						});
```

##### Methods as callbacks
```php

	$command1 = new NumberCommand(1);
	$command1->onSuccess(['myObject','myMethod'])
				->onFailure(['otherObject','anotherMethod']);
```

Artifacts:
-----

**Artifacts are still experimental**

Traditionally, Command/Handler patterns don't allow for return values, at least a majority of what I've seen don't, please correct me if I'm wrong. Either way, Mandate goes a completely different way as far as 'return data' is concerned.

Mandate uses **Artifacts**. An artifact is basically data that is returned from a handler. Handlers **emit** artifacts. Handlers can emit more than one artifact. Each artifact is given a name so that they can be accessed.

#### Simple Artifact Usage:
For this example, you'll need another handler file:

addThreeHandler.php
```php

	use VertigoLabs\Mandate\Handler;
	user VerigoLabs\Mandate\Command;
	
	class AddThreeHandler extends Handler
	{
		public function handle(Command $command)
		{
			if(!is_numeric($command->number)){
				throw new \InvalidArgumentException('Number must be numeric');
			}
			$sum3 = $command->number + 3;
			var_dump($sum3);
			$this->emitArtifact('sum3', $sum3);
			return true;
		}
	}
```

To allow a handler to emit an artifact, it should be "registered" to the instance of the handler. This is intentional so that developers can see that an artifact is registered without the need to dig into the handler to check.

```php

	$mandate = new \VertigoLabs\Mandate\Mandate();
	
	// set up the handler
	$addThreeHandler = new AddThreeHandler();
	$addThreeHandler->produceArtifact('sum3');
	
	// set up the command
	$command = new NumberCommand(1);
	$command->addHandler($addThreeHandler);
	
	$mandate->queue($command)->execute();
	
	var_dump($addThreeHandler->getArtifacts('sum3'));
```
	
This produces the following output:
```
	
	int(4) // remember the handler has a var_dump also...
	int(4)
```	

#### Complex Artifact Usage

The power of artifacts is shown when using them in more complex situations.

##### Binding Artifacts To Commands

Suppose you've got a command that relies on the output from another command/handler. In this type of situation, you can bind artifacts to the command!

```php

	$mandate = new \VertigoLabs\Mandate\Mandate();
	
	// setup the handler
	$addThreeHandler = new AddThreeHandler();
	$addThreeHandler->produceArtifact('sum3');
	
	// setup the commands
	$command1 = new NumberCommand(1);
	$command->addHandler($addThreeHandler);
	
	// here we set up our second command to receive the "sum3" artifact
	// we'll insert null into the command's constructor since that value is
	// the result of command1 being performed by the handler
	$command2 = new NumberCommand(null);
	$command2->addHandler($addThreeHandler);
	// notice the first parameter is the name of the constructor's parameter
	// the second parameter is the name of the artifact
	$command2->bindArtifact('number','sum3');
	
	$mandate->queue($command1)
			->queue($command2)
			->execute();
```

This produces the following output:
```

	int(4)
	int(7) // because the result of $command1 is 4, so we added 3!
```	

Artifacts are overwritten when a new one with the same name is emitted:

```php

	$mandate = new \VertigoLabs\Mandate\Mandate();
	
	// setup the handler
	$addThreeHandler = new AddThreeHandler();
	$addThreeHandler->produceArtifact('sum3');
	
	// setup the commands
	$command1 = new NumberCommand(1);
	$command->addHandler($addThreeHandler);
	
	$command2 = new NumberCommand(null);
	$command2->addHandler($addThreeHandler);
	$command2->bindArtifact('number','sum3');
	
	$command3 = new NumberCommand(null);
	$command3->addHandler($addThreeHandler);
	$command3->bindArtifact('number','sum3');
	
	$mandate->queue($command1)
			->queue($command2)
			->queue($command3)
			->execute();
```

This produces the following output:
```

	int(4) // the artifact "sum3" is set to 4 at this point
	int(7) // the artifact "sum3" is updated to 7 at this point
	int(10) // the artifact "sum3" is updated to 10 at this point
```

##### Waiting For Artifact Availability
Sometimes Artifacts aren't immediately available at the time that a command is ran. For this situation, you can instruct the command to wait for the artifact to become available.

When a command is instructed to wait for an artifact it is requeued automatically with to the next lower priority level

```php

	$mandate = new \VertigoLabs\Mandate\Mandate();
	
	// setup the handlers
	// remember AddTwoHandler does not produce artifacts
	$addTwoHandler = new AddTwoHandler();
	
	$addThreeHandler = new AddThreeHandler();
	$addThreeHandler->produceArtifact('sum3');
	
	// setup the commands
	$command1 = new NumberCommand(null);
	$command1->addHandler($addTwoHandler);
	$command1->bindArtifact('number','sum3', true);
	
	$command2 = new NumberCommand(2);
	$command2->addHandler($addThreeHandler);
	
	$command3 = new NumberCommand(null);
	$command3->addHandler($addThreeHandler);
	$command3->bindArtifact('number','sum3');
	
	$mandate->queue($command1)
			->queue($command2)
			->queue($command3)
			->execute();
```

This produces the following output:
```

	// the "sum3" artifact is not found, requeued $command1
	// $command2 is ran and produces "sum3" with a value of 5
	int(5)
	// now "sum3" is available so command 1 runs
	int(7)
	// at this point, "sum3" is still available with a value of 5
	int(8)
	// the artifact "sum3" still exists, however with a value of 8
```

A command that waits for an artifact will be continually requeued until the artifact is available. When all possible commands are exhausted and the artifact is never produced an exception will occur to let you know that the artifact was never found.
