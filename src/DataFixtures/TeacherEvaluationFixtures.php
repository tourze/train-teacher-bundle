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
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;

#[When(env: 'test')]
#[When(env: 'dev')]
class TeacherEvaluationFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const EVALUATION_REFERENCE_PREFIX = 'evaluation-';

    public static function getGroups(): array
    {
        return ['teacher'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('zh_CN');

        // 为资深教师创建评价
        $seniorTeacher = $this->getReference(TeacherFixtures::SENIOR_TEACHER_REFERENCE, Teacher::class);
        $this->createSeniorTeacherEvaluations($manager, $faker, $seniorTeacher);

        // 为初级教师创建评价
        $juniorTeacher = $this->getReference(TeacherFixtures::JUNIOR_TEACHER_REFERENCE, Teacher::class);
        $this->createJuniorTeacherEvaluations($manager, $faker, $juniorTeacher);

        // 为其他教师创建随机评价
        $this->createRandomTeacherEvaluations($manager, $faker);

        $manager->flush();
    }

    /**
     * @param Generator $faker
     */
    private function createSeniorTeacherEvaluations(ObjectManager $manager, $faker, Teacher $seniorTeacher): void
    {
        for ($i = 1; $i <= 5; ++$i) {
            $evaluation = new TeacherEvaluation();
            $evaluation->setId('evaluation-senior-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
            $evaluation->setTeacher($seniorTeacher);
            $evaluatorType = $faker->randomElement(['学员', '同行', '管理层', '自我']);
            $evaluation->setEvaluatorType(is_string($evaluatorType) ? $evaluatorType : '学员');
            $evaluation->setEvaluatorId('evaluator-' . $faker->uuid());
            $evaluationType = $faker->randomElement(['课程评价', '教学质量评价', '专业技能评价']);
            $evaluation->setEvaluationType(is_string($evaluationType) ? $evaluationType : '课程评价');
            $evaluation->setEvaluationDate(CarbonImmutable::createFromDate(2024, $faker->numberBetween(1, 12), $faker->numberBetween(1, 28)));
            $evaluation->setEvaluationItems([
                'teaching_method' => '优秀',
                'knowledge_mastery' => '很好',
                'interaction_effect' => '良好',
            ]);
            $evaluation->setEvaluationScores([
                'teaching_method' => $faker->randomFloat(1, 8.0, 10.0),
                'knowledge_mastery' => $faker->randomFloat(1, 8.5, 10.0),
                'interaction_effect' => $faker->randomFloat(1, 7.5, 9.5),
            ]);
            $evaluation->setOverallScore($faker->randomFloat(1, 8.0, 9.5));
            $evaluation->setEvaluationComments($faker->sentence(10));
            $evaluation->setSuggestions([
                '继续保持高水平教学',
                '可以增加实践案例',
            ]);
            $evaluation->setIsAnonymous($faker->boolean(30));
            $evaluation->setEvaluationStatus('已完成');
            $evaluation->setCreateTime(CarbonImmutable::now()->modify('-' . $faker->numberBetween(1, 30) . ' days'));

            $manager->persist($evaluation);
            $this->addReference(self::EVALUATION_REFERENCE_PREFIX . 'senior-' . $i, $evaluation);
        }
    }

    /**
     * @param Generator $faker
     */
    private function createJuniorTeacherEvaluations(ObjectManager $manager, $faker, Teacher $juniorTeacher): void
    {
        for ($i = 1; $i <= 3; ++$i) {
            $evaluation = new TeacherEvaluation();
            $evaluation->setId('evaluation-junior-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
            $evaluation->setTeacher($juniorTeacher);
            $evaluatorType = $faker->randomElement(['学员', '同行', '管理层']);
            $evaluation->setEvaluatorType(is_string($evaluatorType) ? $evaluatorType : '学员');
            $evaluation->setEvaluatorId('evaluator-' . $faker->uuid());
            $evaluationType = $faker->randomElement(['课程评价', '教学质量评价']);
            $evaluation->setEvaluationType(is_string($evaluationType) ? $evaluationType : '课程评价');
            $evaluation->setEvaluationDate(CarbonImmutable::createFromDate(2024, $faker->numberBetween(1, 12), $faker->numberBetween(1, 28)));
            $evaluation->setEvaluationItems([
                'teaching_method' => '良好',
                'knowledge_mastery' => '良好',
                'interaction_effect' => '一般',
            ]);
            $evaluation->setEvaluationScores([
                'teaching_method' => $faker->randomFloat(1, 6.0, 8.0),
                'knowledge_mastery' => $faker->randomFloat(1, 6.5, 8.5),
                'interaction_effect' => $faker->randomFloat(1, 5.5, 7.5),
            ]);
            $evaluation->setOverallScore($faker->randomFloat(1, 6.0, 8.0));
            $evaluation->setEvaluationComments($faker->sentence(8));
            $evaluation->setSuggestions([
                '需要提高课堂互动',
                '增强专业知识深度',
            ]);
            $evaluation->setIsAnonymous($faker->boolean(50));
            $evaluation->setEvaluationStatus('已完成');
            $evaluation->setCreateTime(CarbonImmutable::now()->modify('-' . $faker->numberBetween(1, 20) . ' days'));

            $manager->persist($evaluation);
            $this->addReference(self::EVALUATION_REFERENCE_PREFIX . 'junior-' . $i, $evaluation);
        }
    }

    /**
     * @param Generator $faker
     */
    private function createRandomTeacherEvaluations(ObjectManager $manager, $faker): void
    {
        for ($teacherIndex = 3; $teacherIndex <= 10; ++$teacherIndex) {
            $teacher = $this->getReference(TeacherFixtures::TEACHER_REFERENCE_PREFIX . $teacherIndex, Teacher::class);
            $numEvaluations = $faker->numberBetween(1, 4);

            for ($evalIndex = 1; $evalIndex <= $numEvaluations; ++$evalIndex) {
                $this->createRandomEvaluation($manager, $faker, $teacher, $teacherIndex, $evalIndex);
            }
        }
    }

    /**
     * @param Generator $faker
     */
    private function createRandomEvaluation(ObjectManager $manager, $faker, Teacher $teacher, int $teacherIndex, int $evalIndex): void
    {
        $evaluation = new TeacherEvaluation();
        $evaluation->setId('evaluation-' . $teacherIndex . '-' . str_pad((string) $evalIndex, 3, '0', STR_PAD_LEFT));
        $evaluation->setTeacher($teacher);
        $evaluatorType = $faker->randomElement(['学员', '同行', '管理层', '自我']);
        $evaluation->setEvaluatorType(is_string($evaluatorType) ? $evaluatorType : '学员');
        $evaluation->setEvaluatorId('evaluator-' . $faker->uuid());
        $evaluationType = $faker->randomElement(['课程评价', '教学质量评价', '专业技能评价', '综合评价']);
        $evaluation->setEvaluationType(is_string($evaluationType) ? $evaluationType : '课程评价');
        $evaluation->setEvaluationDate(CarbonImmutable::createFromDate(2024, $faker->numberBetween(1, 12), $faker->numberBetween(1, 28)));

        [$evaluationItems, $evaluationScores] = $this->generateEvaluationItems($faker);
        $evaluation->setEvaluationItems($evaluationItems);
        $evaluation->setEvaluationScores($evaluationScores);
        $evaluation->setOverallScore($faker->randomFloat(1, 4.0, 9.5));
        $evaluation->setEvaluationComments($faker->optional(0.8)->sentence(12));
        $evaluation->setSuggestions($this->generateSuggestions($faker));
        $evaluation->setIsAnonymous($faker->boolean(40));
        $evaluationStatus = $faker->randomElement(['已完成', '进行中', '待审核']);
        $evaluation->setEvaluationStatus(is_string($evaluationStatus) ? $evaluationStatus : '已完成');
        $evaluation->setCreateTime(CarbonImmutable::now()->modify('-' . $faker->numberBetween(1, 45) . ' days'));

        $manager->persist($evaluation);
        $this->addReference(self::EVALUATION_REFERENCE_PREFIX . $teacherIndex . '-' . $evalIndex, $evaluation);
    }

    /**
     * @param Generator $faker
     *
     * @return array{0: array<string, string>, 1: array<string, float>}
     */
    private function generateEvaluationItems($faker): array
    {
        $items = ['teaching_method', 'knowledge_mastery', 'interaction_effect', 'course_design', 'assignment_planning'];
        $selectedItems = $faker->randomElements($items, $faker->numberBetween(3, 5));
        /** @var array<string, string> $evaluationItems */
        $evaluationItems = [];
        /** @var array<string, float> $evaluationScores */
        $evaluationScores = [];

        foreach ($selectedItems as $item) {
            if (is_string($item)) {
                $itemValue = $faker->randomElement(['优秀', '很好', '良好', '一般', '需改进']);
                $evaluationItems[$item] = is_string($itemValue) ? $itemValue : '良好';
                $evaluationScores[$item] = $faker->randomFloat(1, 4.0, 10.0);
            }
        }

        return [$evaluationItems, $evaluationScores];
    }

    /**
     * @param Generator $faker
     *
     * @return array<string>
     */
    private function generateSuggestions($faker): array
    {
        $suggestions = $faker->randomElements([
            '增加实践环节',
            '提高互动频率',
            '加强知识深度',
            '改进教学方法',
            '丰富教学案例',
        ], $faker->numberBetween(0, 3));
        /** @var array<string> $filteredSuggestions */
        $filteredSuggestions = array_filter($suggestions, static fn ($s): bool => is_string($s));

        return array_values($filteredSuggestions);
    }

    public function getDependencies(): array
    {
        return [
            TeacherFixtures::class,
        ];
    }
}
