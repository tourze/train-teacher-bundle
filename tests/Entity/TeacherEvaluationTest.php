<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;

/**
 * TeacherEvaluation实体单元测试
 *
 * @internal
 */
#[CoversClass(TeacherEvaluation::class)]
final class TeacherEvaluationTest extends AbstractEntityTestCase
{
    private TeacherEvaluation $evaluation;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluation = new TeacherEvaluation();
        $this->teacher = new Teacher();
    }

    protected function createEntity(): object
    {
        return new TeacherEvaluation();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'id' => ['id', 'eval_123'],
            'evaluatorType' => ['evaluatorType', '学员'],
            'evaluatorId' => ['evaluatorId', 'student_001'],
            'evaluationType' => ['evaluationType', '课程评价'],
            'evaluationDate' => ['evaluationDate', new \DateTime('2024-01-01')],
            'evaluationItems' => ['evaluationItems', ['教学态度', '专业水平']],
            'evaluationScores' => ['evaluationScores', ['教学态度' => 5.0, '专业水平' => 4.5]],
            'overallScore' => ['overallScore', 4.7],
            'evaluationComments' => ['evaluationComments', '教学认真负责'],
            'suggestions' => ['suggestions', ['建议增加实践案例']],
            'isAnonymous' => ['isAnonymous', false],
            'evaluationStatus' => ['evaluationStatus', '已提交'],
            'createTime' => ['createTime', new \DateTime('2024-01-01')],
        ];
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $evaluation = new TeacherEvaluation();
        $this->assertNotNull($evaluation->getCreateTime());
        $this->assertInstanceOf(\DateTimeInterface::class, $evaluation->getCreateTime());
    }

    public function testIdGetterAndSetter(): void
    {
        $id = 'eval_123';
        $this->evaluation->setId($id);

        $this->assertEquals($id, $this->evaluation->getId());
    }

    public function testTeacherGetterAndSetter(): void
    {
        $this->evaluation->setTeacher($this->teacher);

        $this->assertSame($this->teacher, $this->evaluation->getTeacher());
    }

    public function testEvaluatorTypeGetterAndSetter(): void
    {
        $type = '学员';
        $this->evaluation->setEvaluatorType($type);

        $this->assertEquals($type, $this->evaluation->getEvaluatorType());
    }

    public function testEvaluatorIdGetterAndSetter(): void
    {
        $evaluatorId = 'evaluator_123';
        $this->evaluation->setEvaluatorId($evaluatorId);

        $this->assertEquals($evaluatorId, $this->evaluation->getEvaluatorId());
    }

    public function testEvaluationTypeGetterAndSetter(): void
    {
        $type = '课程评价';
        $this->evaluation->setEvaluationType($type);

        $this->assertEquals($type, $this->evaluation->getEvaluationType());
    }

    public function testEvaluationDateGetterAndSetter(): void
    {
        $date = new \DateTime('2024-01-01');
        $this->evaluation->setEvaluationDate($date);

        $this->assertEquals($date, $this->evaluation->getEvaluationDate());
    }

    public function testEvaluationItemsGetterAndSetter(): void
    {
        $items = ['teaching_attitude' => '教学态度', 'professional_level' => '专业水平', 'communication' => '沟通能力'];
        $this->evaluation->setEvaluationItems($items);

        $this->assertEquals($items, $this->evaluation->getEvaluationItems());
    }

    public function testEvaluationItemsDefaultEmptyArray(): void
    {
        $evaluation = new TeacherEvaluation();

        $this->assertEquals([], $evaluation->getEvaluationItems());
    }

    public function testEvaluationScoresGetterAndSetter(): void
    {
        $scores = [
            '教学态度' => 5,
            '专业水平' => 4.5,
            '沟通能力' => 4.8,
        ];
        $this->evaluation->setEvaluationScores($scores);

        $this->assertEquals($scores, $this->evaluation->getEvaluationScores());
    }

    public function testEvaluationScoresDefaultEmptyArray(): void
    {
        $evaluation = new TeacherEvaluation();

        $this->assertEquals([], $evaluation->getEvaluationScores());
    }

    public function testOverallScoreGetterAndSetter(): void
    {
        $score = 4.7;
        $this->evaluation->setOverallScore($score);

        $this->assertEquals($score, $this->evaluation->getOverallScore());
    }

    public function testEvaluationCommentsGetterAndSetter(): void
    {
        $comments = '教学认真负责，专业知识扎实';
        $this->evaluation->setEvaluationComments($comments);

        $this->assertEquals($comments, $this->evaluation->getEvaluationComments());
    }

    public function testEvaluationCommentsCanBeNull(): void
    {
        $this->evaluation->setEvaluationComments(null);

        $this->assertNull($this->evaluation->getEvaluationComments());
    }

    public function testSuggestionsGetterAndSetter(): void
    {
        $suggestions = ['建议增加实践案例', '可以多与学员互动'];
        $this->evaluation->setSuggestions($suggestions);

        $this->assertEquals($suggestions, $this->evaluation->getSuggestions());
    }

    public function testSuggestionsDefaultEmptyArray(): void
    {
        $evaluation = new TeacherEvaluation();

        $this->assertEquals([], $evaluation->getSuggestions());
    }

    public function testIsAnonymousGetterAndSetter(): void
    {
        $this->evaluation->setIsAnonymous(true);
        $this->assertTrue($this->evaluation->isAnonymous());

        $this->evaluation->setIsAnonymous(false);
        $this->assertFalse($this->evaluation->isAnonymous());
    }

    public function testIsAnonymousDefaultFalse(): void
    {
        $evaluation = new TeacherEvaluation();

        $this->assertFalse($evaluation->isAnonymous());
    }

    public function testEvaluationStatusGetterAndSetter(): void
    {
        $status = '已提交';
        $this->evaluation->setEvaluationStatus($status);

        $this->assertEquals($status, $this->evaluation->getEvaluationStatus());
    }

    public function testCreateTimeGetterAndSetter(): void
    {
        $createTime = new \DateTime('2024-01-01 10:00:00');
        $this->evaluation->setCreateTime($createTime);

        $this->assertEquals($createTime, $this->evaluation->getCreateTime());
    }

    public function testSettersWorkCorrectly(): void
    {
        $this->evaluation->setId('eval_001');
        $this->evaluation->setTeacher($this->teacher);
        $this->evaluation->setEvaluatorType('学员');
        $this->evaluation->setEvaluatorId('student_001');
        $this->evaluation->setEvaluationType('课程评价');

        $this->assertEquals('eval_001', $this->evaluation->getId());
        $this->assertSame($this->teacher, $this->evaluation->getTeacher());
        $this->assertEquals('学员', $this->evaluation->getEvaluatorType());
        $this->assertEquals('student_001', $this->evaluation->getEvaluatorId());
        $this->assertEquals('课程评价', $this->evaluation->getEvaluationType());
    }

    public function testCompleteEvaluationData(): void
    {
        $evaluationDate = new \DateTime('2024-01-15');
        $items = ['teaching_attitude' => '教学态度', 'professional_level' => '专业水平', 'communication' => '沟通能力', 'classroom_management' => '课堂管理'];
        $scores = [
            '教学态度' => 5,
            '专业水平' => 4.5,
            '沟通能力' => 4.8,
            '课堂管理' => 4.6,
        ];
        $suggestions = ['practice' => '建议增加实践案例', 'interaction' => '可以多与学员互动'];

        $this->evaluation->setId('eval_001');
        $this->evaluation->setTeacher($this->teacher);
        $this->evaluation->setEvaluatorType('学员');
        $this->evaluation->setEvaluatorId('student_001');
        $this->evaluation->setEvaluationType('课程评价');
        $this->evaluation->setEvaluationDate($evaluationDate);
        $this->evaluation->setEvaluationItems($items);
        $this->evaluation->setEvaluationScores($scores);
        $this->evaluation->setOverallScore(4.7);
        $this->evaluation->setEvaluationComments('教学认真负责，专业知识扎实');
        $this->evaluation->setSuggestions($suggestions);
        $this->evaluation->setIsAnonymous(false);
        $this->evaluation->setEvaluationStatus('已提交');

        $this->assertEquals('eval_001', $this->evaluation->getId());
        $this->assertSame($this->teacher, $this->evaluation->getTeacher());
        $this->assertEquals('学员', $this->evaluation->getEvaluatorType());
        $this->assertEquals('student_001', $this->evaluation->getEvaluatorId());
        $this->assertEquals('课程评价', $this->evaluation->getEvaluationType());
        $this->assertEquals($evaluationDate, $this->evaluation->getEvaluationDate());
        $this->assertEquals($items, $this->evaluation->getEvaluationItems());
        $this->assertEquals($scores, $this->evaluation->getEvaluationScores());
        $this->assertEquals(4.7, $this->evaluation->getOverallScore());
        $this->assertEquals('教学认真负责，专业知识扎实', $this->evaluation->getEvaluationComments());
        $this->assertEquals($suggestions, $this->evaluation->getSuggestions());
        $this->assertFalse($this->evaluation->isAnonymous());
        $this->assertEquals('已提交', $this->evaluation->getEvaluationStatus());
    }

    public function testAnonymousEvaluation(): void
    {
        $this->evaluation->setEvaluatorType('学员');
        $this->evaluation->setEvaluatorId('anonymous_001');
        $this->evaluation->setIsAnonymous(true);
        $this->evaluation->setEvaluationComments('匿名评价内容');

        $this->assertTrue($this->evaluation->isAnonymous());
        $this->assertEquals('匿名评价内容', $this->evaluation->getEvaluationComments());
    }

    public function testDifferentEvaluatorTypes(): void
    {
        $types = ['学员', '同行', '管理层', '自我'];

        foreach ($types as $type) {
            $this->evaluation->setEvaluatorType($type);
            $this->assertEquals($type, $this->evaluation->getEvaluatorType());
        }
    }

    public function testScorePrecision(): void
    {
        $preciseScore = 4.75;
        $this->evaluation->setOverallScore($preciseScore);

        $this->assertEquals($preciseScore, $this->evaluation->getOverallScore());
    }

    public function testEmptyArraysHandling(): void
    {
        $this->evaluation->setEvaluationItems([]);
        $this->evaluation->setEvaluationScores([]);
        $this->evaluation->setSuggestions([]);

        $this->assertEquals([], $this->evaluation->getEvaluationItems());
        $this->assertEquals([], $this->evaluation->getEvaluationScores());
        $this->assertEquals([], $this->evaluation->getSuggestions());
    }

    public function testClassInstantiation(): void
    {
        $instance = new TeacherEvaluation();
        $this->assertInstanceOf(TeacherEvaluation::class, $instance);
    }

    public function testStringable(): void
    {
        $evaluation = new TeacherEvaluation();
        $evaluation->setId('test-evaluation-id');
        $this->assertEquals('test-evaluation-id', (string) $evaluation);
    }
}
