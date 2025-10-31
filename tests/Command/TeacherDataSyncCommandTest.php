<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainTeacherBundle\Command\TeacherDataSyncCommand;

/**
 * @internal
 */
#[CoversClass(TeacherDataSyncCommand::class)]
#[RunTestsInSeparateProcesses]
final class TeacherDataSyncCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    public function testExecuteWithDryRun(): void
    {
        $exitCode = $this->commandTester->execute([
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteBasicSync(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithFixData(): void
    {
        $exitCode = $this->commandTester->execute([
            '--fix-data' => true,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithCheckDuplicates(): void
    {
        $exitCode = $this->commandTester->execute([
            '--check-duplicates' => true,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithUpdateStatus(): void
    {
        $exitCode = $this->commandTester->execute([
            '--update-status' => true,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithAllOptions(): void
    {
        $exitCode = $this->commandTester->execute([
            '--dry-run' => true,
            '--fix-data' => true,
            '--check-duplicates' => true,
            '--update-status' => true,
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertEquals('teacher:data:sync', TeacherDataSyncCommand::NAME);
    }

    public function testOptionDryRun(): void
    {
        $exitCode = $this->commandTester->execute(['--dry-run']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionFixData(): void
    {
        $exitCode = $this->commandTester->execute(['--fix-data']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionCheckDuplicates(): void
    {
        $exitCode = $this->commandTester->execute(['--check-duplicates']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionUpdateStatus(): void
    {
        $exitCode = $this->commandTester->execute(['--update-status']);
        $this->assertEquals(0, $exitCode);
    }

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(TeacherDataSyncCommand::class);
        self::assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->add($command);

        $command = $application->find('teacher:data:sync');
        $this->commandTester = new CommandTester($command);
    }
}
