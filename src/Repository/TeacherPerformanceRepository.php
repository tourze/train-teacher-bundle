<?php

namespace Tourze\TrainTeacherBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;

/**
 * 教师绩效数据访问仓库
 * @extends ServiceEntityRepository<TeacherPerformance>
 */
#[AsRepository(entityClass: TeacherPerformance::class)]
class TeacherPerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeacherPerformance::class);
    }

    /**
     * 根据教师获取绩效历史
     * @return array<int, TeacherPerformance>
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
            'performancePeriod' => $period,
        ]);
    }

    /**
     * 根据绩效等级获取教师列表
     * @return array<int, TeacherPerformance>
     */
    public function findByPerformanceLevel(string $performanceLevel): array
    {
        return $this->findBy(['performanceLevel' => $performanceLevel], ['performanceScore' => 'DESC']);
    }

    /**
     * 获取绩效排名
     * @return array<int, array<string, mixed>>
     */
    public function getPerformanceRanking(int $limit = 20): array
    {
        /** @var list<array<string, mixed>> $result */
        $result = $this->createQueryBuilder('p')
            ->select('t.id, t.teacherName, p.performanceScore, p.performanceLevel')
            ->join('p.teacher', 't')
            ->orderBy('p.performanceScore', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * 获取指定周期的绩效排名
     * @return array<int, array<string, mixed>>
     */
    public function getPerformanceRankingByPeriod(\DateTimeInterface $period, int $limit = 20): array
    {
        // 使用findBy方法获取数据，避免JOIN查询问题
        $performances = $this->findBy(['performancePeriod' => $period]);

        // 在应用层进行排序和数据转换
        $result = [];
        foreach ($performances as $performance) {
            $teacher = $performance->getTeacher();
            $result[] = [
                'id' => $teacher->getId(),
                'teacherName' => $teacher->getTeacherName(),
                'performanceScore' => $performance->getPerformanceScore(),
                'performanceLevel' => $performance->getPerformanceLevel(),
            ];
        }

        // 按分数降序排序
        usort($result, function ($a, $b) {
            return $b['performanceScore'] <=> $a['performanceScore'];
        });

        // 限制结果数量
        return array_slice($result, 0, $limit);
    }

    /**
     * 获取绩效统计信息
     * @return array<string, mixed>
     */
    public function getPerformanceStatistics(): array
    {
        $total = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $excellent = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.performanceLevel = :excellent')
            ->setParameter('excellent', '优秀')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $good = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.performanceLevel = :good')
            ->setParameter('good', '良好')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $average = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.performanceLevel = :average')
            ->setParameter('average', '一般')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $poor = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.performanceLevel = :poor')
            ->setParameter('poor', '较差')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $avgScore = $this->createQueryBuilder('p')
            ->select('AVG(p.performanceScore)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return [
            'total' => $total,
            'excellent' => $excellent,
            'good' => $good,
            'average' => $average,
            'poor' => $poor,
            'averageScore' => null !== $avgScore ? (float) $avgScore : 0.0,
        ];
    }

    /**
     * 获取教师绩效趋势
     * @return array<int, TeacherPerformance>
     */
    public function getPerformanceTrend(Teacher $teacher, int $months = 12): array
    {
        $startDate = new \DateTime();
        $startDate->modify("-{$months} months");

        /** @var list<TeacherPerformance> $result */
        $result = $this->createQueryBuilder('p')
            ->where('p.teacher = :teacher')
            ->andWhere('p.performancePeriod >= :startDate')
            ->setParameter('teacher', $teacher)
            ->setParameter('startDate', $startDate)
            ->orderBy('p.performancePeriod', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * 比较多个教师的绩效
     * @param array<int, string> $teacherIds
     * @return array<int, array<string, mixed>>
     */
    public function compareTeacherPerformance(array $teacherIds, \DateTimeInterface $period): array
    {
        /** @var list<array<string, mixed>> $result */
        $result = $this->createQueryBuilder('p')
            ->select('t.id, t.teacherName, p.performanceScore, p.performanceLevel, p.averageEvaluation')
            ->join('p.teacher', 't')
            ->where('t.id IN (:teacherIds)')
            ->andWhere('p.performancePeriod = :period')
            ->setParameter('teacherIds', $teacherIds)
            ->setParameter('period', $period)
            ->orderBy('p.performanceScore', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * 获取最新绩效记录
     * @return array<int, TeacherPerformance>
     */
    public function getLatestPerformances(int $limit = 10): array
    {
        // 避免JOIN查询问题，使用简单查询
        /** @var list<TeacherPerformance> $result */
        $result = $this->createQueryBuilder('p')
            ->orderBy('p.createTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    public function save(TeacherPerformance $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TeacherPerformance $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
