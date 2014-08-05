<?php
/**
 * @author: jaimz
 * @copyright:
 * @date: 7/29/2014
 * @time: 1:10 PM
 */

class MandateTest extends \PHPUnit_Framework_TestCase {

	public function testQueue()
	{
		$mandate = new \VertigoLabs\Mandate\Mandate();

		$simpleCommand = new SimpleTestCommand(null);
		$this->assertEquals(0,$mandate->getQueueCount());
		$mandate->queue($simpleCommand);
		$this->assertEquals(1,$mandate->getQueueCount());
		$mandate->clearQueue();
		$this->assertEquals(0,$mandate->getQueueCount());
	}

	public function testExecuteSimple()
	{
		$mandate = new \VertigoLabs\Mandate\Mandate();

		$handler = new SimpleReverseTestHandler();
		$handler->produceArtifact('testArtifactReversed');

		$command = new SimpleTestCommand('test');
		$command->addHandler($handler);

		$mandate->queue($command)
		        ->execute();

		$this->assertEquals('tset',$handler->getArtifacts('testArtifactReversed')->getValue());
	}

	public function testExecuteSimpleArtifacts()
	{
		$mandate = new \VertigoLabs\Mandate\Mandate();

		$handler = new SimpleReverseTestHandler();
		$handler->produceArtifact('testArtifactReversed');

		$command1 = new SimpleTestCommand('test');
		$command1->addHandler($handler);

		$command2 = new SimpleTestCommand(null);
		$command2->bindArtifact('param1','testArtifactReversed');
		$command2->addHandler($handler);

		$mandate->queue($command1)
				->queue($command2)
				->execute();

		$this->assertEquals('test',$handler->getArtifacts('testArtifactReversed')->getValue());
	}

	public function testExecuteAdvancedArtifacts()
	{
		$mandate = new \VertigoLabs\Mandate\Mandate();

		$handler = new SimpleReverseTestHandler();
		$handler->produceArtifact('testArtifactReversed');

		$handler2 = new SimpleUpperCaseTestHandler();
		$handler2->produceArtifact('testArtifactUpperCased');

		$command1 = new SimpleTestCommand(null);
		$command1->bindArtifact('param1','testArtifactReversed',true);
		$command1->addHandler($handler);

		$command2 = new SimpleTestCommand('test');
		$command2->addHandler($handler2);

		$command3 = new SimpleTestCommand('test');
		$command3->addHandler($handler);

		$mandate->queue($command1)
				->queue($command2)
				->queue($command3)
				->execute();

		$this->assertEquals('test',$handler->getArtifacts('testArtifactReversed')->getValue());
		$this->assertEquals('TEST',$handler2->getArtifacts('testArtifactUpperCased')->getValue());
	}

	public function testMultipleArtifacts()
	{
		$mandate = new \VertigoLabs\Mandate\Mandate();

		$handler = new SimpleUpperCaseAndReverseTestHandler();
		$handler->produceArtifact('testArtifactUpperCased')
				 ->produceArtifact('testArtifactReversed');

		$command1 = new SimpleTestCommand('test');
		$command1->addHandler($handler);

		$mandate->queue($command1)
				->execute();

		$this->assertEquals('tset',$handler->getArtifacts('testArtifactReversed')->getValue());
		$this->assertEquals('testArtifactReversed',$handler->getArtifacts('testArtifactReversed')->getName());
		$this->assertEquals('TEST',$handler->getArtifacts('testArtifactUpperCased')->getValue());
		$this->assertEquals(2,count($handler->getArtifacts(['testArtifactReversed','testArtifactUpperCased'])));
		$this->assertEquals(1,count($handler->getArtifacts(['testArtifactReversed'])));
		$this->assertEquals(2,count($handler->getArtifacts()));
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testExecuteArtifactException1()
	{
		$mandate = new \VertigoLabs\Mandate\Mandate();

		$handler = new SimpleReverseTestHandler();
		$handler->produceArtifact('testArtifactReversed');

		$command1 = new SimpleTestCommand(null);
		$command1->bindArtifact('param1','testArtifactReversed',true);
		$command1->addHandler($handler);

		$mandate->queue($command1)
				->execute();
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testExecuteArtifactException2()
	{
		$mandate = new \VertigoLabs\Mandate\Mandate();

		$handler = new SimpleReverseTestHandler();
		$handler->produceArtifact('testArtifactReversed');

		$handler2 = new SimpleUpperCaseTestHandler();
		$handler2->produceArtifact('testArtifactUpperCased');

		$command1 = new SimpleTestCommand(null);
		$command1->bindArtifact('param1','testArtifactReversed',true);
		$command1->addHandler($handler);

		$command2 = new SimpleTestCommand('test');
		$command2->addHandler($handler2);

		$mandate->queue($command1)
				->queue($command2)
				->execute();
	}

	public function testExecuteOnFailure()
	{
		$passed = false;

		$mandate = new \VertigoLabs\Mandate\Mandate();

		$handler = new SimpleReverseTestHandler();
		$handler->produceArtifact('testArtifactReversed');

		$command1 = new SimpleTestCommand('pleaseFail');
		$command1->addHandler($handler)
				 ->onFailure(function($exception) use (&$passed) {
					 $passed = true;
				 });

		$mandate->queue($command1)
				->execute();

		$this->assertTrue($passed);
	}

	public function testExecuteOnSuccess()
	{
		$passed = false;

		$mandate = new \VertigoLabs\Mandate\Mandate();

		$handler = new SimpleReverseTestHandler();
		$handler->produceArtifact('testArtifactReversed');

		$command1 = new SimpleTestCommand('test');
		$command1->addHandler($handler)
				 ->onSuccess(function() use (&$passed) {
					 $passed = true;
				 });

		$mandate->queue($command1)
				->execute();

		$this->assertTrue($passed);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testNoHandlerException()
	{
		$mandate = new \VertigoLabs\Mandate\Mandate();

		$command1 = new SimpleTestCommand('test');

		$mandate->queue($command1)
				->execute();
	}

	/**
	 * @expectedException \Exception
	 */
	public function testUnregisteredArtifact()
	{
		$mandate = new \VertigoLabs\Mandate\Mandate();

		$handler = new SimpleReverseTestHandler();

		$command1 = new SimpleTestCommand('test');
		$command1->addHandler($handler)
				->onFailure(function ($exception) {
					throw $exception;
				});

		$mandate->queue($command1)
				->execute();
	}

	public function testCommandParameters()
	{
		$command = new SimpleTestCommand('test');

		$this->assertEquals('test',$command->param1);
		$this->assertNull($command->notAProperty);
	}

	public function testCommandBoundArtifacts()
	{
		$command = new SimpleTestCommand('test');
		$command->bindArtifact('param1','someArtifact');

		$this->assertEquals(1,count($command->getArtifactBindings()));
	}
}

class SimpleTestCommand extends \VertigoLabs\Mandate\Command
{
	private $param1;

	public function __construct($param1)
	{
		$this->param1 = $param1;
	}

}

/**
 * Class SimpleTestHandler
 *
 * Simply reverse the string that is passed into the command
 */
class SimpleReverseTestHandler extends \VertigoLabs\Mandate\Handler
{
	public function handle(\VertigoLabs\Mandate\Interfaces\CommandInterface $command)
	{
		if ($command->param1 === 'pleaseFail') {
			throw new \InvalidArgumentException('you failed!');
		}

		$reversed = strrev($command->param1);

		$this->emitArtifact('testArtifactReversed',$reversed);
		return true;
	}
}

/**
 * Class SimpleTestHandler
 *
 * Simply reverse the string that is passed into the command
 */
class SimpleUpperCaseTestHandler extends \VertigoLabs\Mandate\Handler
{
	public function handle(\VertigoLabs\Mandate\Interfaces\CommandInterface $command)
	{
		$uppercase = strtoupper($command->param1);

		$this->emitArtifact('testArtifactUpperCased',$uppercase);
		return true;
	}
}

/**
 * Class SimpleTestHandler
 *
 * Simply reverse the string that is passed into the command
 */
class SimpleUpperCaseAndReverseTestHandler extends \VertigoLabs\Mandate\Handler
{
	public function handle(\VertigoLabs\Mandate\Interfaces\CommandInterface $command)
	{
		$reverse = strrev($command->param1);
		$uppercase = strtoupper($command->param1);

		$this->emitArtifact('testArtifactReversed',$reverse);
		$this->emitArtifact('testArtifactUpperCased',$uppercase);
		return true;
	}
}