<?php

namespace Tourze\TrainTeacherBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainTeacherBundle\Entity\Teacher;

/**
 * 教师数据访问仓库
 */
class TeacherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Teacher::class);
    }

    /**
     * 根据教师编号查找教师
     */
    public function findByTeacherCode(string $teacherCode): ?Teacher
    {
        return $this->findOneBy(['teacherCode' => $teacherCode]);
    }

    /**
     * 根据教师类型查找教师列表
     */
    public function findByTeacherType(string $teacherType): array
    {
        return $this->findBy(['teacherType' => $teacherType], ['createTime' => 'DESC']);
    }

    /**
     * 根据教师状态查找教师列表
     */
    public function findByTeacherStatus(string $teacherStatus): array
    {
        return $this->findBy(['teacherStatus' => $teacherStatus], ['createTime' => 'DESC']);
    }

    /**
     * 根据身份证号查找教师
     */
    public function findByIdCard(string $idCard): ?Teacher
    {
        return $this->findOneBy(['idCard' => $idCard]);
    }

    /**
     * 根据手机号查找教师
     */
    public function findByPhone(string $phone): ?Teacher
    {
        return $this->findOneBy(['phone' => $phone]);
    }

    /**
     * 搜索教师（支持姓名、编号模糊搜索）
     */
    public function searchTeachers(string $keyword, int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.teacherName LIKE :keyword OR t.teacherCode LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('t.createTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取教师统计信息
     */
    public function getTeacherStatistics(): array
    {
        $total = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $fullTime = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.teacherType = :fullTime')
            ->setParameter('fullTime', '专职')
            ->getQuery()
            ->getSingleScalarResult();

        $partTime = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.teacherType = :partTime')
            ->setParameter('partTime', '兼职')
            ->getQuery()
            ->getSingleScalarResult();

        $active = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.teacherStatus = :active')
            ->setParameter('active', '在职')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'fullTime' => $fullTime,
            'partTime' => $partTime,
            'active' => $active,
        ];
    }

    /**
     * 获取最近加入的教师
     */
    public function getRecentTeachers(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.joinDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
} 