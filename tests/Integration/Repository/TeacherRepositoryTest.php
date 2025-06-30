<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Integration\Repository;

use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Tests\Integration\IntegrationTestCase;

class TeacherRepositoryTest extends IntegrationTestCase
{
    private TeacherRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = static::getContainer()->get(TeacherRepository::class);
    }

    public function testRepositoryIsInstantiable(): void
    {
        $this->assertInstanceOf(TeacherRepository::class, $this->repository);
    }

    public function testFindAllReturnsArray(): void
    {
        $result = $this->repository->findAll();
        $this->assertContainsOnly('object', $result);
    }

    public function testFindByReturnsArray(): void
    {
        $result = $this->repository->findBy(['teacherStatus' => 'active']);
        $this->assertContainsOnly('object', $result);
    }

    public function testFindOneByReturnsNullForNonExistentRecord(): void
    {
        $result = $this->repository->findOneBy(['teacherCode' => 'non-existent-code']);
        $this->assertNull($result);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindByStatus(): void
    {
        $result = $this->repository->findBy(['teacherStatus' => 'active']);
        $this->assertContainsOnly('object', $result);
    }

    public function testFindByType(): void
    {
        $result = $this->repository->findBy(['teacherType' => 'full-time']);
        $this->assertContainsOnly('object', $result);
    }
}