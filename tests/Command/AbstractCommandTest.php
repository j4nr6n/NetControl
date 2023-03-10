<?php

namespace App\Tests\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandTest extends KernelTestCase
{
    /**
     * This helper method abstracts the boilerplate code needed to test the execution of a command.
     *
     * @param array $arguments All the arguments passed when executing the command
     * @param array $inputs The (optional) answers given to the command when it asks
     *                      for the value of the missing arguments
     */
    protected function executeCommand(array $arguments, array $inputs = []): CommandTester
    {
        self::bootKernel();

        /** @var Command $command */
        $command = static::getContainer()->get($this->getCommandFqcn());

        $command->setApplication(new Application(self::$kernel));

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);

        return $commandTester;
    }

    abstract protected function getCommandFqcn(): string;
}
