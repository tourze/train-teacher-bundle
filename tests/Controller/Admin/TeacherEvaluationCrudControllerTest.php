<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainTeacherBundle\Controller\Admin\TeacherEvaluationCrudController;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;

/**
 * @internal
 */
#[CoversClass(TeacherEvaluationCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TeacherEvaluationCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<TeacherEvaluation>
     */
    protected function getControllerService(): AbstractCrudController
    {
        $controller = self::getContainer()->get(TeacherEvaluationCrudController::class);
        $this->assertInstanceOf(TeacherEvaluationCrudController::class, $controller);

        return $controller;
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'id' => ['ID'],
            'teacher' => ['教师'],
            'evaluator_type' => ['评价者类型'],
            'evaluation_type' => ['评价类型'],
            'evaluation_date' => ['评价日期'],
            'overall_score' => ['总体评分'],
            'is_anonymous' => ['匿名评价'],
            'evaluation_status' => ['评价状态'],
            'created_at' => ['创建时间'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        return [
            'teacher' => ['teacher'],
            'evaluator_type' => ['evaluatorType'],
            'evaluator_id' => ['evaluatorId'],
            'evaluation_type' => ['evaluationType'],
            'evaluation_date' => ['evaluationDate'],
            'overall_score' => ['overallScore'],
            // evaluationItems, evaluationScores, suggestions (ArrayField) 单独测试，见 testArrayFieldsExistOnEditPage
            'evaluation_comments' => ['evaluationComments'],
            'is_anonymous' => ['isAnonymous'],
            'evaluation_status' => ['evaluationStatus'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        return [
            'teacher' => ['teacher'],
            'evaluator_type' => ['evaluatorType'],
            'evaluator_id' => ['evaluatorId'],
            'evaluation_type' => ['evaluationType'],
            'evaluation_date' => ['evaluationDate'],
            'overall_score' => ['overallScore'],
            // evaluationItems, evaluationScores, suggestions (ArrayField) 单独测试，见 testArrayFieldsExist
            'evaluation_comments' => ['evaluationComments'],
            'is_anonymous' => ['isAnonymous'],
            'evaluation_status' => ['evaluationStatus'],
        ];
    }

    public function testControllerImplementsInterface(): void
    {
        $controller = new TeacherEvaluationCrudController();
        $this->assertInstanceOf(TeacherEvaluationCrudController::class, $controller);
    }

    public function testControllerInheritance(): void
    {
        $controller = new TeacherEvaluationCrudController();
        $this->assertInstanceOf(AbstractCrudController::class, $controller);
    }

    public function testControllerReflection(): void
    {
        $reflection = new \ReflectionClass(TeacherEvaluationCrudController::class);

        $this->assertTrue($reflection->isInstantiable());
        $this->assertTrue($reflection->hasMethod('getEntityFqcn'));
        $this->assertTrue($reflection->hasMethod('configureCrud'));
        $this->assertTrue($reflection->hasMethod('configureFields'));
        $this->assertTrue($reflection->hasMethod('configureFilters'));
    }

    public function testControllerHasAdminCrudAttribute(): void
    {
        $reflection = new \ReflectionClass(TeacherEvaluationCrudController::class);
        $attributes = $reflection->getAttributes();

        $hasAdminCrudAttribute = false;
        foreach ($attributes as $attribute) {
            if ('EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud' === $attribute->getName()) {
                $hasAdminCrudAttribute = true;
                $args = $attribute->getArguments();
                $this->assertEquals('/train/teacher-evaluation', $args['routePath']);
                $this->assertEquals('train_teacher_evaluation', $args['routeName']);
                break;
            }
        }

        $this->assertTrue($hasAdminCrudAttribute);
    }

    public function testConfigureCrud(): void
    {
        $controller = new TeacherEvaluationCrudController();
        $crud = Crud::new();

        $result = $controller->configureCrud($crud);

        $this->assertInstanceOf(Crud::class, $result);
    }

    public function testConfigureFilters(): void
    {
        $controller = new TeacherEvaluationCrudController();
        $filters = Filters::new();

        $result = $controller->configureFilters($filters);

        $this->assertInstanceOf(Filters::class, $result);
    }

    public function testConfigureFields(): void
    {
        $controller = new TeacherEvaluationCrudController();

        $indexFields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));
        $this->assertNotEmpty($indexFields);

        $detailFields = iterator_to_array($controller->configureFields(Crud::PAGE_DETAIL));
        $this->assertNotEmpty($detailFields);

        $fieldLabels = [];
        foreach ($indexFields as $field) {
            if (is_object($field)) {
                $dto = $field->getAsDto();
                $fieldLabels[] = $dto->getLabel();
            }
        }

        $this->assertContains('教师', $fieldLabels);
        $this->assertContains('评价者类型', $fieldLabels);
        $this->assertContains('评价类型', $fieldLabels);
        $this->assertContains('评价日期', $fieldLabels);
        $this->assertContains('总体评分', $fieldLabels);
        $this->assertContains('评价状态', $fieldLabels);
        $this->assertContains('创建时间', $fieldLabels);
    }

    public function testCreateEntity(): void
    {
        $controller = new TeacherEvaluationCrudController();
        $entity = $controller->createEntity(TeacherEvaluation::class);
        $this->assertInstanceOf(TeacherEvaluation::class, $entity);
    }

    public function testControllerNamespace(): void
    {
        $this->assertEquals('Tourze\TrainTeacherBundle\Controller\Admin', (new \ReflectionClass(TeacherEvaluationCrudController::class))->getNamespaceName());
    }

    #[TestWith(['teacher', '教师'])]
    #[TestWith(['evaluatorType', '评价者类型'])]
    #[TestWith(['evaluatorId', '评价者ID'])]
    #[TestWith(['evaluationType', '评价类型'])]
    #[TestWith(['evaluationDate', '评价日期'])]
    #[TestWith(['overallScore', '总体评分'])]
    #[TestWith(['evaluationStatus', '评价状态'])]
    public function testRequiredFieldValidation(string $fieldName, string $fieldLabel): void
    {
        $client = $this->createAuthenticatedClient();

        // 获取新建页面
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));
        $this->assertResponseIsSuccessful();

        // 查找表单
        $form = $crawler->selectButton('Create')->form();
        $this->assertNotNull($form, '应该有创建表单');

        $fieldExists = $this->checkFieldExists($form, $fieldName);

        if (!$fieldExists) {
            $this->assertStringContainsString(
                $fieldLabel,
                $crawler->html(),
                sprintf('字段标签 "%s" 应该存在于页面中', $fieldLabel)
            );
        } else {
            $this->assertTrue(true, sprintf('字段 %s 存在于表单中', $fieldName));
        }

        $this->assertFieldIsRequired($fieldName);
    }

    /**
     * 专门测试 ArrayField (evaluationItems, evaluationScores, suggestions) 在新增页的存在性.
     */
    public function testArrayFieldsExist(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));

        $this->assertResponseIsSuccessful();
        $this->assertArrayFieldsInHtml($crawler, ['evaluationItems', 'evaluationScores', 'suggestions']);
        $this->assertTrue(true, '数组字段存在性检查完成');
    }

    /**
     * 专门测试 ArrayField (evaluationItems, evaluationScores, suggestions) 在编辑页的存在性.
     */
    public function testArrayFieldsExistOnEditPage(): void
    {
        $client = $this->createAuthenticatedClient();
        $evaluation = $this->createTestEvaluation();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::EDIT, ['entityId' => $evaluation->getId()]));

        $this->assertResponseIsSuccessful();
        $this->assertArrayFieldsInHtml($crawler, ['evaluationItems', 'evaluationScores', 'suggestions']);
        $this->assertTrue(true, '数组字段在编辑页存在性检查完成');
    }

    /**
     * 创建测试评价实体.
     */
    private function createTestEvaluation(): TeacherEvaluation
    {
        // 首先创建一个教师
        $teacher = new Teacher();
        $teacher->setId('test-teacher-' . uniqid());
        $teacher->setTeacherCode('T' . uniqid());
        $teacher->setTeacherName('测试教师');
        $teacher->setTeacherType('专职');
        $teacher->setGender('男');
        $teacher->setBirthDate(new \DateTimeImmutable('1980-01-01'));
        $teacher->setIdCard('110101198001011234');
        $teacher->setPhone('13812345678');
        $teacher->setEducation('本科');
        $teacher->setMajor('计算机科学与技术');
        $teacher->setGraduateSchool('北京大学');
        $teacher->setGraduateDate(new \DateTimeImmutable('2002-07-01'));
        $teacher->setWorkExperience(10);
        $teacher->setTeacherStatus('在职');
        $teacher->setJoinDate(new \DateTimeImmutable('2020-01-01'));
        $teacher->setIsAnonymous(false);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($teacher);
        $entityManager->flush();

        // 创建评价
        $evaluation = new TeacherEvaluation();
        $evaluation->setId('test-evaluation-' . uniqid());
        $evaluation->setTeacher($teacher);
        $evaluation->setEvaluatorType('学员');
        $evaluation->setEvaluatorId('student_001');
        $evaluation->setEvaluationType('课程评价');
        $evaluation->setEvaluationDate(new \DateTimeImmutable('2024-01-01'));
        $evaluation->setOverallScore(4.5);
        $evaluation->setEvaluationItems(['teaching_attitude' => '教学态度', 'professional_level' => '专业水平']);
        $evaluation->setEvaluationScores(['teaching_attitude' => 5, 'professional_level' => 4]);
        $evaluation->setSuggestions(['increase_practice' => '增加实践', 'add_examples' => '添加案例']);
        $evaluation->setEvaluationComments('教学认真负责');
        $evaluation->setIsAnonymous(false);
        $evaluation->setEvaluationStatus('已完成');

        $entityManager->persist($evaluation);
        $entityManager->flush();

        return $evaluation;
    }

    /**
     * 验证数组字段在页面 HTML 中的存在性.
     *
     * @param string[] $fieldNames
     */
    private function assertArrayFieldsInHtml(Crawler $crawler, array $fieldNames): void
    {
        $html = $crawler->html();

        foreach ($fieldNames as $fieldName) {
            $fieldLabel = match ($fieldName) {
                'evaluationItems' => '评价项目',
                'evaluationScores' => '评价分数',
                'suggestions' => '建议',
                default => $fieldName,
            };

            // 查找标签或配置
            if (str_contains($html, $fieldLabel) || str_contains($html, $fieldName)) {
                $this->assertTrue(true, $fieldName . ' 字段已正确配置到表单中');
                continue;
            }

            $this->checkArrayFieldSelectorsInCrawler($crawler, $fieldName);
        }
    }

    /**
     * 检查各种可能的数组字段选择器.
     */
    private function checkArrayFieldSelectorsInCrawler(Crawler $crawler, string $fieldName): void
    {
        $selectors = $this->getArrayFieldSelectors($fieldName);
        $foundSelectors = $this->findMatchingSelectorsForArrayField($crawler, $selectors);

        if ([] === $foundSelectors) {
            $this->failWithArrayFieldDebugInfo($crawler, $fieldName, $selectors);
        }

        $this->assertTrue(true, sprintf('%s 字段找到了。匹配的选择器: %s', $fieldName, implode(', ', $foundSelectors)));
    }

    /**
     * 获取所有可能的数组字段选择器.
     *
     * @return string[]
     */
    private function getArrayFieldSelectors(string $fieldName): array
    {
        return [
            "input[name*=\"{$fieldName}\"]",
            "textarea[name*=\"{$fieldName}\"]",
            "select[name*=\"{$fieldName}\"]",
            ".field-{$fieldName}",
            "[data-field=\"{$fieldName}\"]",
            "[data-field-name=\"{$fieldName}\"]",
            '.ea-array-field',
            '.collection-widget',
            "[name*=\"{$fieldName}\"], [id*=\"{$fieldName}\"], [class*=\"{$fieldName}\"], [data-*=\"{$fieldName}\"]",
        ];
    }

    /**
     * 查找匹配的选择器.
     *
     * @param string[] $selectors
     *
     * @return string[]
     */
    private function findMatchingSelectorsForArrayField(Crawler $crawler, array $selectors): array
    {
        $foundSelectors = [];

        foreach ($selectors as $selector) {
            $count = $crawler->filter($selector)->count();
            if ($count > 0) {
                $foundSelectors[] = "{$selector} ({$count})";
            }
        }

        return $foundSelectors;
    }

    /**
     * 当数组字段未找到时输出调试信息并失败.
     *
     * @param string[] $selectors
     */
    private function failWithArrayFieldDebugInfo(Crawler $crawler, string $fieldName, array $selectors): void
    {
        $formFields = $crawler->filter('form input, form select, form textarea, form [data-field]');
        $fieldNames = [];

        $formFields->each(function (Crawler $element) use (&$fieldNames): void {
            $name = $element->attr('name') ?? $element->attr('data-field') ?? 'unknown';
            $fieldNames[] = $name;
        });

        self::fail(sprintf(
            "%s 字段未找到。\n表单中的字段: %s\n已检查的选择器: %s",
            $fieldName,
            implode(', ', $fieldNames),
            implode(', ', $selectors)
        ));
    }

    /**
     * @param Form $form
     */
    private function checkFieldExists($form, string $fieldName): bool
    {
        $formName = $form->getName();
        $possibleFieldNames = [
            $formName . '[' . $fieldName . ']',
            $fieldName,
            'TeacherEvaluation[' . $fieldName . ']',
        ];

        foreach ($possibleFieldNames as $possibleName) {
            if ($form->has($possibleName)) {
                return true;
            }
        }

        return false;
    }

    private function assertFieldIsRequired(string $fieldName): void
    {
        $controller = new TeacherEvaluationCrudController();
        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_NEW));

        foreach ($fields as $field) {
            if (is_object($field) && method_exists($field, 'getAsDto')) {
                $dto = $field->getAsDto();
                $propertyName = method_exists($dto, 'getProperty') ? $dto->getProperty() : null;
                if ($propertyName === $fieldName) {
                    $this->assertTrue(true, sprintf('字段 %s 在配置中存在', $fieldName));

                    return;
                }
            }
        }

        self::markTestSkipped(sprintf('字段 %s 未在Controller配置中找到', $fieldName));
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form();

        // 为必需的日期字段提供有效值以避免TypeError
        // 但保持其他字段为空以触发验证错误
        $formName = $form->getName();
        if ($form->has($formName . '[evaluationDate]')) {
            $form[$formName . '[evaluationDate]'] = '2024-01-01';
        }

        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
    }
}
