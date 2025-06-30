<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;

/**
 * TeacherRepository测试
 */
class TeacherRepositoryTest extends TestCase
{
    public function testRepositoryInstantiation(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new TeacherRepository($registry);

        $this->assertInstanceOf(TeacherRepository::class, $repository);
    }
}
