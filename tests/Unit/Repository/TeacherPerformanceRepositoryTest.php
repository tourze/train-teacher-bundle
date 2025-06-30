<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainTeacherBundle\Repository\TeacherPerformanceRepository;

/**
 * TeacherPerformanceRepository测试
 */
class TeacherPerformanceRepositoryTest extends TestCase
{
    public function testRepositoryInstantiation(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new TeacherPerformanceRepository($registry);

        $this->assertInstanceOf(TeacherPerformanceRepository::class, $repository);
    }
}
