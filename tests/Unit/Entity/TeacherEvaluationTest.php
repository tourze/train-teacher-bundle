<?php

namespace Tourze\TrainTeacherBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;

/**
 * TeacherEvaluation实体单元测试
 */
class TeacherEvaluationTest extends TestCase
{
    private TeacherEvaluation $evaluation;
    private Teacher $teacher;

    protected function setUp(): void
    {
        $this->evaluation = new TeacherEvaluation();
        $this->teacher = new Teacher();
    }

    public function test_constructor_sets_default_values(): void
    {
        $evaluation = new TeacherEvaluation();
        
        $this->assertInstanceOf(\DateTimeInterface::class, $evaluation->getCreateTime());
    }

    public function test_id_getter_and_setter(): void
    {
        $id = 'eval_123';
        $this->evaluation->setId($id);
        
        $this->assertEquals($id, $this->evaluation->getId());
    }

    public function test_teacher_getter_and_setter(): void
    {
        $this->evaluation->setTeacher($this->teacher);
        
        $this->assertSame($this->teacher, $this->evaluation->getTeacher());
    }

    public function test_evaluator_type_getter_and_setter(): void
    {
        $type = '学员';
        $this->evaluation->setEvaluatorType($type);
        
        $this->assertEquals($type, $this->evaluation->getEvaluatorType());
    }

    public function test_evaluator_id_getter_and_setter(): void
    {
        $evaluatorId = 'evaluator_123';
        $this->evaluation->setEvaluatorId($evaluatorId);
        
        $this->assertEquals($evaluatorId, $this->evaluation->getEvaluatorId());
    }

    public function test_evaluation_type_getter_and_setter(): void
    {
        $type = '课程评价';
        $this->evaluation->setEvaluationType($type);
        
        $this->assertEquals($type, $this->evaluation->getEvaluationType());
    }

    public function test_evaluation_date_getter_and_setter(): void
    {
        $date = new \DateTimeImmutable('2024-01-01');
        $this->evaluation->setEvaluationDate($date);
        
        $this->assertEquals($date, $this->evaluation->getEvaluationDate());
    }

    public function test_evaluation_items_getter_and_setter(): void
    {
        $items = ['教学态度', '专业水平', '沟通能力'];
        $this->evaluation->setEvaluationItems($items);
        
        $this->assertEquals($items, $this->evaluation->getEvaluationItems());
    }

    public function test_evaluation_items_default_empty_array(): void
    {
        $evaluation = new TeacherEvaluation();
        
        $this->assertEquals([], $evaluation->getEvaluationItems());
    }

    public function test_evaluation_scores_getter_and_setter(): void
    {
        $scores = [
            '教学态度' => 5,
            '专业水平' => 4.5,
            '沟通能力' => 4.8
        ];
        $this->evaluation->setEvaluationScores($scores);
        
        $this->assertEquals($scores, $this->evaluation->getEvaluationScores());
    }

    public function test_evaluation_scores_default_empty_array(): void
    {
        $evaluation = new TeacherEvaluation();
        
        $this->assertEquals([], $evaluation->getEvaluationScores());
    }

    public function test_overall_score_getter_and_setter(): void
    {
        $score = 4.7;
        $this->evaluation->setOverallScore($score);
        
        $this->assertEquals($score, $this->evaluation->getOverallScore());
    }

    public function test_evaluation_comments_getter_and_setter(): void
    {
        $comments = '教学认真负责，专业知识扎实';
        $this->evaluation->setEvaluationComments($comments);
        
        $this->assertEquals($comments, $this->evaluation->getEvaluationComments());
    }

    public function test_evaluation_comments_can_be_null(): void
    {
        $this->evaluation->setEvaluationComments(null);
        
        $this->assertNull($this->evaluation->getEvaluationComments());
    }

    public function test_suggestions_getter_and_setter(): void
    {
        $suggestions = ['建议增加实践案例', '可以多与学员互动'];
        $this->evaluation->setSuggestions($suggestions);
        
        $this->assertEquals($suggestions, $this->evaluation->getSuggestions());
    }

    public function test_suggestions_default_empty_array(): void
    {
        $evaluation = new TeacherEvaluation();
        
        $this->assertEquals([], $evaluation->getSuggestions());
    }

    public function test_is_anonymous_getter_and_setter(): void
    {
        $this->evaluation->setIsAnonymous(true);
        $this->assertTrue($this->evaluation->isAnonymous());
        
        $this->evaluation->setIsAnonymous(false);
        $this->assertFalse($this->evaluation->isAnonymous());
    }

    public function test_is_anonymous_default_false(): void
    {
        $evaluation = new TeacherEvaluation();
        
        $this->assertFalse($evaluation->isAnonymous());
    }

    public function test_evaluation_status_getter_and_setter(): void
    {
        $status = '已提交';
        $this->evaluation->setEvaluationStatus($status);
        
        $this->assertEquals($status, $this->evaluation->getEvaluationStatus());
    }

    public function test_create_time_getter_and_setter(): void
    {
        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $this->evaluation->setCreateTime($createTime);
        
        $this->assertEquals($createTime, $this->evaluation->getCreateTime());
    }

    public function test_fluent_interface(): void
    {
        $result = $this->evaluation
            ->setId('eval_001')
            ->setTeacher($this->teacher)
            ->setEvaluatorType('学员')
            ->setEvaluatorId('student_001')
            ->setEvaluationType('课程评价');
        
        $this->assertSame($this->evaluation, $result);
        $this->assertEquals('eval_001', $this->evaluation->getId());
        $this->assertSame($this->teacher, $this->evaluation->getTeacher());
        $this->assertEquals('学员', $this->evaluation->getEvaluatorType());
        $this->assertEquals('student_001', $this->evaluation->getEvaluatorId());
        $this->assertEquals('课程评价', $this->evaluation->getEvaluationType());
    }

    public function test_complete_evaluation_data(): void
    {
        $evaluationDate = new \DateTimeImmutable('2024-01-15');
        $items = ['教学态度', '专业水平', '沟通能力', '课堂管理'];
        $scores = [
            '教学态度' => 5,
            '专业水平' => 4.5,
            '沟通能力' => 4.8,
            '课堂管理' => 4.6
        ];
        $suggestions = ['建议增加实践案例', '可以多与学员互动'];

        $this->evaluation
            ->setId('eval_001')
            ->setTeacher($this->teacher)
            ->setEvaluatorType('学员')
            ->setEvaluatorId('student_001')
            ->setEvaluationType('课程评价')
            ->setEvaluationDate($evaluationDate)
            ->setEvaluationItems($items)
            ->setEvaluationScores($scores)
            ->setOverallScore(4.7)
            ->setEvaluationComments('教学认真负责，专业知识扎实')
            ->setSuggestions($suggestions)
            ->setIsAnonymous(false)
            ->setEvaluationStatus('已提交');

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

    public function test_anonymous_evaluation(): void
    {
        $this->evaluation
            ->setEvaluatorType('学员')
            ->setEvaluatorId('anonymous_001')
            ->setIsAnonymous(true)
            ->setEvaluationComments('匿名评价内容');

        $this->assertTrue($this->evaluation->isAnonymous());
        $this->assertEquals('匿名评价内容', $this->evaluation->getEvaluationComments());
    }

    public function test_different_evaluator_types(): void
    {
        $types = ['学员', '同行', '管理层', '自我'];
        
        foreach ($types as $type) {
            $this->evaluation->setEvaluatorType($type);
            $this->assertEquals($type, $this->evaluation->getEvaluatorType());
        }
    }

    public function test_score_precision(): void
    {
        $preciseScore = 4.75;
        $this->evaluation->setOverallScore($preciseScore);
        
        $this->assertEquals($preciseScore, $this->evaluation->getOverallScore());
    }

    public function test_empty_arrays_handling(): void
    {
        $this->evaluation
            ->setEvaluationItems([])
            ->setEvaluationScores([])
            ->setSuggestions([]);

        $this->assertEquals([], $this->evaluation->getEvaluationItems());
        $this->assertEquals([], $this->evaluation->getEvaluationScores());
        $this->assertEquals([], $this->evaluation->getSuggestions());
    }
} 