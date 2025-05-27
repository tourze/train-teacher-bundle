<?php

namespace Tourze\TrainTeacherBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;

/**
 * 教师绩效数据访问仓库
 */
class TeacherPerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeacherPerformance::class);
    }

    /**
     * 根据教师获取绩效历史
     */
    public function findByTeacher(Teacher $teacher): array
    {
        return $this->findBy(['teacher' => $teacher], ['performancePeriod' => 'DESC']);
    }

    /**
     * 获取教师指定周期的绩效
     */
    public function findByTeacherAndPeriod(Teacher $teacher, \DateTimeInterface $period): ?TeacherPerformance
    {
        return $this->findOneBy([
            'teacher' => $teacher,
            'performancePeriod' => $period
        ]);
    }

    /**
     * 根据绩效等级获取教师列表
     */
    public function findByPerformanceLevel(string $performanceLevel): array
    {
        return $this->findBy(['performanceLevel' => $performanceLevel], ['performanceScore' => 'DESC']);
    }

    /**
     * 获取绩效排名
     */
    public function getPerformanceRanking(int $limit = 20): array
    {
        return $this->createQueryBuilder('p')
            ->select('t.id, t.teacherName, p.performanceScore, p.performanceLevel')
            ->join('p.teacher', 't')
            ->orderBy('p.performanceScore', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取指定周期的绩效排名
     */
    public function getPerformanceRankingByPeriod(\DateTimeInterface $period, int $limit = 20): array
    {
        return $this->createQueryBuilder('p')
            ->select('t.id, t.teacherName, p.performanceScore, p.performanceLevel')
            ->join('p.teacher', 't')
            ->where('p.performancePeriod = :period')
            ->setParameter('period', $period)
            ->orderBy('p.performanceScore', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取绩效统计信息
     */
    public function getPerformanceStatistics(): array
    {
        $qb = $this->createQueryBuilder('p');
        
        $total = $qb->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $excellent = $qb->select('COUNT(p.id)')
            ->where('p.performanceLevel = :excellent')
            ->setParameter('excellent', '优秀')
            ->getQuery()
            ->getSingleScalarResult();

        $good = $qb->select('COUNT(p.id)')
            ->where('p.performanceLevel = :good')
            ->setParameter('good', '良好')
            ->getQuery()
            ->getSingleScalarResult();

        $average = $qb->select('COUNT(p.id)')
            ->where('p.performanceLevel = :average')
            ->setParameter('average', '一般')
            ->getQuery()
            ->getSingleScalarResult();

        $poor = $qb->select('COUNT(p.id)')
            ->where('p.performanceLevel = :poor')
            ->setParameter('poor', '较差')
            ->getQuery()
            ->getSingleScalarResult();

        $avgScore = $qb->select('AVG(p.performanceScore)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'excellent' => $excellent,
            'good' => $good,
            'average' => $average,
            'poor' => $poor,
            'averageScore' => $avgScore ? (float) $avgScore : 0.0,
        ];
    }

    /**
     * 获取教师绩效趋势
     */
    public function getPerformanceTrend(Teacher $teacher, int $months = 12): array
    {
        $startDate = new \DateTime();
        $startDate->modify("-{$months} months");

        return $this->createQueryBuilder('p')
            ->where('p.teacher = :teacher')
            ->andWhere('p.performancePeriod >= :startDate')
            ->setParameter('teacher', $teacher)
            ->setParameter('startDate', $startDate)
            ->orderBy('p.performancePeriod', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 比较多个教师的绩效
     */
    public function compareTeacherPerformance(array $teacherIds, \DateTimeInterface $period): array
    {
        return $this->createQueryBuilder('p')
            ->select('t.id, t.teacherName, p.performanceScore, p.performanceLevel, p.averageEvaluation')
            ->join('p.teacher', 't')
            ->where('t.id IN (:teacherIds)')
            ->andWhere('p.performancePeriod = :period')
            ->setParameter('teacherIds', $teacherIds)
            ->setParameter('period', $period)
            ->orderBy('p.performanceScore', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取最新绩效记录
     */
    public function getLatestPerformances(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.teacher', 't')
            ->orderBy('p.createTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
} 