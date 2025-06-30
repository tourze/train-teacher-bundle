<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Integration\Repository;

use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;
use Tourze\TrainTeacherBundle\Tests\Integration\IntegrationTestCase;

class TeacherEvaluationRepositoryTest extends IntegrationTestCase
{
    private TeacherEvaluationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = static::getContainer()->get(TeacherEvaluationRepository::class);
    }

    public function testRepositoryIsInstantiable(): void
    {
        $this->assertInstanceOf(TeacherEvaluationRepository::class, $this->repository);
    }

    public function testFindAllReturnsArray(): void
    {
        $result = $this->repository->findAll();
        $this->assertContainsOnly('object', $result);
    }

    public function testFindByReturnsArray(): void
    {
        $result = $this->repository->findBy(['evaluationType' => 'student']);
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

    public function testFindByEvaluationType(): void
    {
        $result = $this->repository->findBy(['evaluationType' => 'peer']);
        $this->assertContainsOnly('object', $result);
    }

    public function testFindByEvaluationScore(): void
    {
        $result = $this->repository->findBy(['evaluationScore' => 90]);
        $this->assertContainsOnly('object', $result);
    }
}