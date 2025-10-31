<?php

namespace Tourze\TrainTeacherBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;

/**
 * 教师评价数据访问仓库
 * @extends ServiceEntityRepository<TeacherEvaluation>
 */
#[AsRepository(entityClass: TeacherEvaluation::class)]
class TeacherEvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeacherEvaluation::class);
    }

    /**
     * 根据教师ID获取评价列表
     * @return array<int, TeacherEvaluation>
     */
    public function findByTeacher(Teacher $teacher): array
    {
        return $this->findBy(['teacher' => $teacher], ['evaluationDate' => 'DESC']);
    }

    /**
     * 根据评价者类型获取评价列表
     * @return array<int, TeacherEvaluation>
     */
    public function findByEvaluatorType(string $evaluatorType): array
    {
        return $this->findBy(['evaluatorType' => $evaluatorType], ['evaluationDate' => 'DESC']);
    }

    /**
     * 获取教师的平均评分
     */
    public function getAverageScore(Teacher $teacher): float
    {
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.overallScore) as avgScore')
            ->where('e.teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return null !== $result ? (float) $result : 0.0;
    }

    /**
     * 根据评价者类型获取教师平均评分
     */
    public function getAverageScoreByEvaluatorType(Teacher $teacher, string $evaluatorType): float
    {
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.overallScore) as avgScore')
            ->where('e.teacher = :teacher')
            ->andWhere('e.evaluatorType = :evaluatorType')
            ->setParameter('teacher', $teacher)
            ->setParameter('evaluatorType', $evaluatorType)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return null !== $result ? (float) $result : 0.0;
    }

    /**
     * 获取教师评价统计信息
     * @return array<string, mixed>
     */
    public function getEvaluationStatistics(Teacher $teacher): array
    {
        $total = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $studentEvaluations = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.teacher = :teacher')
            ->andWhere('e.evaluatorType = :student')
            ->setParameter('teacher', $teacher)
            ->setParameter('student', '学员')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $peerEvaluations = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.teacher = :teacher')
            ->andWhere('e.evaluatorType = :peer')
            ->setParameter('teacher', $teacher)
            ->setParameter('peer', '同行')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $managerEvaluations = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.teacher = :teacher')
            ->andWhere('e.evaluatorType = :manager')
            ->setParameter('teacher', $teacher)
            ->setParameter('manager', '管理层')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return [
            'total' => $total,
            'student' => $studentEvaluations,
            'peer' => $peerEvaluations,
            'manager' => $managerEvaluations,
            'averageScore' => $this->getAverageScore($teacher),
        ];
    }

    /**
     * 获取最高评分的教师列表
     * @return array<int, array<string, mixed>>
     */
    public function getTopRatedTeachers(int $limit = 10): array
    {
        /** @var list<array<string, mixed>> $result */
        $result = $this->createQueryBuilder('e')
            ->select('t.id, t.teacherName, t.teacherCode, AVG(e.overallScore) as avgScore')
            ->join('e.teacher', 't')
            ->groupBy('t.id, t.teacherName, t.teacherCode')
            ->orderBy('avgScore', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * 检查评价者是否已经评价过该教师
     */
    public function hasEvaluated(Teacher $teacher, string $evaluatorId, string $evaluationType): bool
    {
        $count = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.teacher = :teacher')
            ->andWhere('e.evaluatorId = :evaluatorId')
            ->andWhere('e.evaluationType = :evaluationType')
            ->setParameter('teacher', $teacher)
            ->setParameter('evaluatorId', $evaluatorId)
            ->setParameter('evaluationType', $evaluationType)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $count > 0;
    }

    /**
     * 获取指定时间范围内的评价
     * @return array<int, TeacherEvaluation>
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var list<TeacherEvaluation> $result */
        $result = $this->createQueryBuilder('e')
            ->where('e.evaluationDate >= :startDate')
            ->andWhere('e.evaluationDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.evaluationDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * 获取教师最近的评价记录
     * @return array<int, TeacherEvaluation>
     */
    public function findRecentEvaluations(Teacher $teacher, string $evaluationType, int $days): array
    {
        $startDate = new \DateTime();
        $startDate->modify("-{$days} days");

        /** @var list<TeacherEvaluation> $result */
        $result = $this->createQueryBuilder('e')
            ->where('e.teacher = :teacher')
            ->andWhere('e.evaluationType = :evaluationType')
            ->andWhere('e.evaluationDate >= :startDate')
            ->setParameter('teacher', $teacher)
            ->setParameter('evaluationType', $evaluationType)
            ->setParameter('startDate', $startDate)
            ->orderBy('e.evaluationDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    public function save(TeacherEvaluation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TeacherEvaluation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
