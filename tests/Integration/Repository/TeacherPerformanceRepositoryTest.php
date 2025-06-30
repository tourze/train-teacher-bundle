<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Integration\Repository;

use Tourze\TrainTeacherBundle\Repository\TeacherPerformanceRepository;
use Tourze\TrainTeacherBundle\Tests\Integration\IntegrationTestCase;

class TeacherPerformanceRepositoryTest extends IntegrationTestCase
{
    private TeacherPerformanceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = static::getContainer()->get(TeacherPerformanceRepository::class);
    }

    public function testRepositoryIsInstantiable(): void
    {
        $this->assertInstanceOf(TeacherPerformanceRepository::class, $this->repository);
    }

    public function testFindAllReturnsArray(): void
    {
        $result = $this->repository->findAll();
        $this->assertContainsOnly('object', $result);
    }

    public function testFindByReturnsArray(): void
    {
        $result = $this->repository->findBy(['performanceLevel' => 'excellent']);
        $this->assertContainsOnly('object', $result);
    }

    public function testFindOneByReturnsNullForNonExistentRecord(): void
    {
        $result = $this->repository->findOneBy(['id' => 'non-existent-id']);
        $this->assertNull($result);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindByPerformanceLevel(): void
    {
        $result = $this->repository->findBy(['performanceLevel' => 'good']);
        $this->assertContainsOnly('object', $result);
    }

    public function testFindByPerformanceScore(): void
    {
        $result = $this->repository->findBy(['performanceScore' => 85]);
        $this->assertContainsOnly('object', $result);
    }
}