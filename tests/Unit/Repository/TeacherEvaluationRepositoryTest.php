<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;

/**
 * TeacherEvaluationRepository测试
 */
class TeacherEvaluationRepositoryTest extends TestCase
{
    public function testRepositoryInstantiation(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new TeacherEvaluationRepository($registry);

        $this->assertInstanceOf(TeacherEvaluationRepository::class, $repository);
    }
}
