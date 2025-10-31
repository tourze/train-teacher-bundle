<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\TrainTeacherBundle\TrainTeacherBundle;

/**
 * @internal
 */
#[CoversClass(TrainTeacherBundle::class)]
#[RunTestsInSeparateProcesses]
final class TrainTeacherBundleTest extends AbstractBundleTestCase
{
}
