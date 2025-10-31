<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;
use Tourze\TrainTeacherBundle\Helper\EvaluationDataPopulator;

/**
 * @internal
 */
#[CoversClass(EvaluationDataPopulator::class)]
class EvaluationDataPopulatorTest extends TestCase
{
    private EvaluationDataPopulator $populator;

    protected function setUp(): void
    {
        $this->populator = new EvaluationDataPopulator();
    }

    public function testPopulateBasicInfo(): void
    {
        $evaluation = new TeacherEvaluation();
        $data = [
            'evaluatorType' => '学员',
            'evaluationType' => '教学评价',
            'evaluationDate' => new \DateTimeImmutable('2025-01-01'),
        ];

        $this->populator->populate($evaluation, $data);

        self::assertSame('学员', $evaluation->getEvaluatorType());
        self::assertSame('教学评价', $evaluation->getEvaluationType());
        self::assertEquals(new \DateTimeImmutable('2025-01-01'), $evaluation->getEvaluationDate());
    }

    public function testPopulateScores(): void
    {
        $evaluation = new TeacherEvaluation();
        $data = [
            'evaluationScores' => [
                '教学能力' => 4.5,
                '沟通能力' => 4.8,
            ],
        ];

        $this->populator->populate($evaluation, $data);

        $scores = $evaluation->getEvaluationScores();
        self::assertSame(4.5, $scores['教学能力']);
        self::assertSame(4.8, $scores['沟通能力']);
    }

    public function testPopulateWithDefaults(): void
    {
        $evaluation = new TeacherEvaluation();
        $data = [];

        $this->populator->populate($evaluation, $data);

        self::assertNotEmpty($evaluation->getEvaluationDate());
        self::assertSame('已提交', $evaluation->getEvaluationStatus());
    }

    public function testPopulateComments(): void
    {
        $evaluation = new TeacherEvaluation();
        $data = [
            'evaluationComments' => '教学效果很好',
            'suggestions' => ['建议增加互动环节'],
        ];

        $this->populator->populate($evaluation, $data);

        self::assertSame('教学效果很好', $evaluation->getEvaluationComments());
        self::assertContains('建议增加互动环节', $evaluation->getSuggestions());
    }
}
