<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;

#[When(env: 'test')]
#[When(env: 'dev')]
class TeacherPerformanceFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const PERFORMANCE_REFERENCE_PREFIX = 'performance-';

    public static function getGroups(): array
    {
        return ['teacher'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('zh_CN');

        // 为资深教师创建绩效记录
        $seniorTeacher = $this->getReference(TeacherFixtures::SENIOR_TEACHER_REFERENCE, Teacher::class);

        for ($i = 1; $i <= 4; ++$i) {
            $performance = new TeacherPerformance();
            $performance->setId('performance-senior-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
            $performance->setTeacher($seniorTeacher);
            $performance->setPerformancePeriod(CarbonImmutable::createFromDate(2024, $i * 3, 1));
            $performance->setAverageEvaluation($faker->randomFloat(1, 8.5, 9.5));
            $performance->setPerformanceMetrics([
                '授课时长' => $faker->numberBetween(120, 180),
                '学员满意度' => $faker->randomFloat(2, 85.0, 95.0),
                '课程完成率' => $faker->randomFloat(2, 90.0, 100.0),
                '创新教学法应用' => $faker->numberBetween(3, 5),
                '论文发表数量' => $faker->numberBetween(1, 3),
            ]);
            $performance->setPerformanceScore($faker->randomFloat(2, 85.0, 95.0));
            $performance->setPerformanceLevel('优秀');
            $performance->setAchievements([
                '获得优秀教师奖',
                '课程评分第一名',
                '发表学术论文' . $faker->numberBetween(1, 3) . '篇',
            ]);
            $performance->setCreateTime(CarbonImmutable::now()->modify('-' . $faker->numberBetween(30, 120) . ' days'));

            $manager->persist($performance);
            $this->addReference(self::PERFORMANCE_REFERENCE_PREFIX . 'senior-' . $i, $performance);
        }

        // 为初级教师创建绩效记录
        $juniorTeacher = $this->getReference(TeacherFixtures::JUNIOR_TEACHER_REFERENCE, Teacher::class);

        for ($i = 1; $i <= 3; ++$i) {
            $performance = new TeacherPerformance();
            $performance->setId('performance-junior-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
            $performance->setTeacher($juniorTeacher);
            $performance->setPerformancePeriod(CarbonImmutable::createFromDate(2024, $i * 4, 1));
            $performance->setAverageEvaluation($faker->randomFloat(1, 6.5, 8.0));
            $performance->setPerformanceMetrics([
                '授课时长' => $faker->numberBetween(60, 120),
                '学员满意度' => $faker->randomFloat(2, 70.0, 85.0),
                '课程完成率' => $faker->randomFloat(2, 80.0, 95.0),
                '创新教学法应用' => $faker->numberBetween(1, 3),
                '专业技能提升' => $faker->numberBetween(2, 4),
            ]);
            $performance->setPerformanceScore($faker->randomFloat(2, 70.0, 82.0));
            $performanceLevel = $faker->randomElement(['良好', '中等']);
            $performance->setPerformanceLevel(is_string($performanceLevel) ? $performanceLevel : '良好');
            $performance->setAchievements([
                '完成新教师培训',
                '学员反馈积极',
                '课程改进建议被采纳',
            ]);
            $performance->setCreateTime(CarbonImmutable::now()->modify('-' . $faker->numberBetween(15, 90) . ' days'));

            $manager->persist($performance);
            $this->addReference(self::PERFORMANCE_REFERENCE_PREFIX . 'junior-' . $i, $performance);
        }

        // 为其他教师创建绩效记录
        for ($teacherIndex = 3; $teacherIndex <= 10; ++$teacherIndex) {
            $teacher = $this->getReference(TeacherFixtures::TEACHER_REFERENCE_PREFIX . $teacherIndex, Teacher::class);
            $numPerformances = $faker->numberBetween(1, 3);

            for ($perfIndex = 1; $perfIndex <= $numPerformances; ++$perfIndex) {
                $performance = new TeacherPerformance();
                $performance->setId('performance-' . $teacherIndex . '-' . str_pad((string) $perfIndex, 3, '0', STR_PAD_LEFT));
                $performance->setTeacher($teacher);
                $performance->setPerformancePeriod(CarbonImmutable::createFromDate(2024, $faker->numberBetween(1, 12), 1));
                $performance->setAverageEvaluation($faker->randomFloat(1, 4.0, 9.5));

                $baseScore = $faker->randomFloat(2, 50.0, 95.0);
                $performance->setPerformanceMetrics([
                    '授课时长' => $faker->numberBetween(30, 200),
                    '学员满意度' => $faker->randomFloat(2, 50.0, 95.0),
                    '课程完成率' => $faker->randomFloat(2, 60.0, 100.0),
                    '创新教学法应用' => $faker->numberBetween(0, 5),
                    '专业发展活动' => $faker->numberBetween(0, 8),
                    '同行合作次数' => $faker->numberBetween(0, 10),
                ]);
                $performance->setPerformanceScore($baseScore);

                $level = $this->getPerformanceLevel($baseScore);
                $performance->setPerformanceLevel($level);

                $achievements = $this->generateAchievements($level, $faker);
                $performance->setAchievements($achievements);
                $performance->setCreateTime(CarbonImmutable::now()->modify('-' . $faker->numberBetween(5, 150) . ' days'));

                $manager->persist($performance);
                $this->addReference(self::PERFORMANCE_REFERENCE_PREFIX . $teacherIndex . '-' . $perfIndex, $performance);
            }
        }

        $manager->flush();
    }

    private function getPerformanceLevel(float $baseScore): string
    {
        if ($baseScore >= 90) {
            return '优秀';
        }
        if ($baseScore >= 80) {
            return '良好';
        }
        if ($baseScore >= 70) {
            return '中等';
        }
        if ($baseScore >= 60) {
            return '及格';
        }

        return '需改进';
    }

    /**
     * @return array<string>
     */
    private function generateAchievements(string $level, Generator $faker): array
    {
        $rawAchievements = match ($level) {
            '优秀' => $faker->randomElements([
                '获得月度优秀教师',
                '学员评价满分',
                '创新教学方法获奖',
                '教学研究成果突出',
            ], $faker->numberBetween(2, 4)),
            '良好' => $faker->randomElements([
                '教学质量稳定',
                '学员反馈良好',
                '积极参与教研活动',
                '课程设计有创新',
            ], $faker->numberBetween(1, 3)),
            '中等' => $faker->randomElements([
                '基础教学任务完成',
                '学员基本满意',
                '参与部分培训活动',
            ], $faker->numberBetween(1, 2)),
            '及格' => $faker->randomElements([
                '完成基本教学要求',
                '努力提升教学水平',
            ], $faker->numberBetween(0, 2)),
            default => $faker->randomElements([
                '积极参与改进培训',
                '制定个人提升计划',
            ], $faker->numberBetween(0, 1)),
        };

        /** @var array<string> $filteredAchievements */
        $filteredAchievements = array_filter($rawAchievements, static fn ($a): bool => is_string($a));

        return array_values($filteredAchievements);
    }

    public function getDependencies(): array
    {
        return [
            TeacherFixtures::class,
        ];
    }
}
