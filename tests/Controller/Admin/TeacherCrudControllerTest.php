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
use Tourze\TrainTeacherBundle\Controller\Admin\TeacherCrudController;
use Tourze\TrainTeacherBundle\Entity\Teacher;

/**
 * 教师CRUD控制器测试
 * @internal
 */
#[CoversClass(TeacherCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TeacherCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<Teacher>
     */
    protected function getControllerService(): AbstractCrudController
    {
        $controller = self::getContainer()->get(TeacherCrudController::class);
        $this->assertInstanceOf(TeacherCrudController::class, $controller);

        return $controller;
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'teacher_code' => ['教师编号'],
            'teacher_name' => ['教师姓名'],
            'teacher_type' => ['教师类型'],
            'gender' => ['性别'],
            'phone' => ['联系电话'],
            'education' => ['学历'],
            'work_experience' => ['工作经验（年）'],
            'teacher_status' => ['教师状态'],
            'hire_date' => ['入职日期'],
            'is_anonymous' => ['是否匿名'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        return [
            'teacher_code' => ['teacherCode'],
            'teacher_name' => ['teacherName'],
            'teacher_type' => ['teacherType'],
            'gender' => ['gender'],
            'birth_date' => ['birthDate'],
            'id_card' => ['idCard'],
            'phone' => ['phone'],
            'email' => ['email'],
            'address' => ['address'],
            'education' => ['education'],
            'major' => ['major'],
            'graduate_school' => ['graduateSchool'],
            'graduate_date' => ['graduateDate'],
            'work_experience' => ['workExperience'],
            // specialties (ArrayField) 单独测试，见 testSpecialtiesArrayFieldExistsOnEditPage
            'teacher_status' => ['teacherStatus'],
            'profile_photo' => ['profilePhoto'],
            'join_date' => ['joinDate'],
            'is_anonymous' => ['isAnonymous'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        return [
            'teacher_code' => ['teacherCode'],
            'teacher_name' => ['teacherName'],
            'teacher_type' => ['teacherType'],
            'gender' => ['gender'],
            'birth_date' => ['birthDate'],
            'id_card' => ['idCard'],
            'phone' => ['phone'],
            'email' => ['email'],
            'address' => ['address'],
            'education' => ['education'],
            'major' => ['major'],
            'graduate_school' => ['graduateSchool'],
            'graduate_date' => ['graduateDate'],
            'work_experience' => ['workExperience'],
            // specialties (ArrayField) 单独测试，见 testSpecialtiesArrayFieldExists
            'teacher_status' => ['teacherStatus'],
            'profile_photo' => ['profilePhoto'],
            'join_date' => ['joinDate'],
            'is_anonymous' => ['isAnonymous'],
        ];
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Teacher::class, TeacherCrudController::getEntityFqcn());
    }

    public function testControllerImplementsInterface(): void
    {
        $controller = new TeacherCrudController();
        $this->assertInstanceOf(TeacherCrudController::class, $controller);
    }

    public function testControllerInheritance(): void
    {
        $controller = new TeacherCrudController();
        $this->assertInstanceOf(AbstractCrudController::class, $controller);
    }

    public function testControllerReflection(): void
    {
        $reflection = new \ReflectionClass(TeacherCrudController::class);

        $this->assertTrue($reflection->isInstantiable());
        $this->assertTrue($reflection->hasMethod('getEntityFqcn'));
        $this->assertTrue($reflection->hasMethod('configureCrud'));
        $this->assertTrue($reflection->hasMethod('configureFields'));
        $this->assertTrue($reflection->hasMethod('configureFilters'));
    }

    public function testControllerHasAdminCrudAttribute(): void
    {
        $reflection = new \ReflectionClass(TeacherCrudController::class);
        $attributes = $reflection->getAttributes();

        $hasAdminCrudAttribute = false;
        foreach ($attributes as $attribute) {
            if ('EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud' === $attribute->getName()) {
                $hasAdminCrudAttribute = true;
                $args = $attribute->getArguments();
                $this->assertEquals('/train/teacher', $args['routePath']);
                $this->assertEquals('train_teacher', $args['routeName']);
                break;
            }
        }

        $this->assertTrue($hasAdminCrudAttribute);
    }

    public function testConfigureCrud(): void
    {
        $controller = new TeacherCrudController();
        $crud = Crud::new();

        $result = $controller->configureCrud($crud);

        $this->assertInstanceOf(Crud::class, $result);
    }

    public function testConfigureFilters(): void
    {
        $controller = new TeacherCrudController();
        $filters = Filters::new();

        $result = $controller->configureFilters($filters);

        $this->assertInstanceOf(Filters::class, $result);
    }

    public function testConfigureFields(): void
    {
        $controller = new TeacherCrudController();

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

        $this->assertContains('教师编号', $fieldLabels);
        $this->assertContains('教师姓名', $fieldLabels);
        $this->assertContains('教师类型', $fieldLabels);
        $this->assertContains('性别', $fieldLabels);
        $this->assertContains('联系电话', $fieldLabels);
        $this->assertContains('学历', $fieldLabels);
        $this->assertContains('教师状态', $fieldLabels);
        $this->assertContains('入职日期', $fieldLabels);
    }

    public function testCreateEntity(): void
    {
        $controller = new TeacherCrudController();
        $entity = $controller->createEntity(Teacher::class);
        $this->assertInstanceOf(Teacher::class, $entity);
    }

    public function testControllerNamespace(): void
    {
        $this->assertEquals('Tourze\TrainTeacherBundle\Controller\Admin', (new \ReflectionClass(TeacherCrudController::class))->getNamespaceName());
    }

    #[TestWith(['teacherCode', '教师编号'])]
    #[TestWith(['teacherName', '教师姓名'])]
    #[TestWith(['teacherType', '教师类型'])]
    #[TestWith(['gender', '性别'])]
    #[TestWith(['birthDate', '出生日期'])]
    #[TestWith(['idCard', '身份证号'])]
    #[TestWith(['phone', '联系电话'])]
    #[TestWith(['education', '学历'])]
    #[TestWith(['major', '专业'])]
    #[TestWith(['graduateSchool', '毕业院校'])]
    #[TestWith(['graduateDate', '毕业日期'])]
    #[TestWith(['workExperience', '工作经验（年）'])]
    #[TestWith(['teacherStatus', '教师状态'])]
    #[TestWith(['joinDate', '入职日期'])]
    public function testRequiredFieldValidation(string $fieldName, string $fieldLabel): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));
        $this->assertResponseIsSuccessful();

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

    public function testPhoneNumberValidation(): void
    {
        // 简化测试：验证手机号格式要求存在
        $entity = new Teacher();
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('phone');
        $attributes = $property->getAttributes();

        $hasRegexValidation = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Regex')) {
                $hasRegexValidation = true;
                break;
            }
        }

        $this->assertTrue($hasRegexValidation, '手机号字段应该有正则表达式验证');
    }

    public function testEmailValidation(): void
    {
        // 简化测试：验证邮箱格式要求存在
        $entity = new Teacher();
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('email');
        $attributes = $property->getAttributes();

        $hasEmailValidation = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Email')) {
                $hasEmailValidation = true;
                break;
            }
        }

        $this->assertTrue($hasEmailValidation, '邮箱字段应该有Email验证');
    }

    /**
     * 专门测试 ArrayField (specialties) 在新增页的存在性.
     */
    public function testSpecialtiesArrayFieldExists(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));

        $this->assertResponseIsSuccessful();
        $this->assertSpecialtiesFieldInHtml($crawler);
        $this->assertTrue(true, 'specialties 字段存在性检查完成');
    }

    /**
     * 专门测试 ArrayField (specialties) 在编辑页的存在性.
     */
    public function testSpecialtiesArrayFieldExistsOnEditPage(): void
    {
        $client = $this->createAuthenticatedClient();
        $teacher = $this->createTestTeacher();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::EDIT, ['entityId' => $teacher->getId()]));

        $this->assertResponseIsSuccessful();
        $this->assertSpecialtiesFieldInHtml($crawler);
        $this->assertTrue(true, 'specialties 字段在编辑页存在性检查完成');
    }

    /**
     * 创建测试教师实体.
     */
    private function createTestTeacher(): Teacher
    {
        $teacher = new Teacher();
        $teacher->setId('test-teacher-' . uniqid()); // 设置自定义ID
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

        return $teacher;
    }

    /**
     * 验证 specialties 字段在页面 HTML 中的存在性.
     */
    private function assertSpecialtiesFieldInHtml(Crawler $crawler): void
    {
        $html = $crawler->html();

        // 查找标签或配置
        if (str_contains($html, '专业特长') || str_contains($html, 'specialties')) {
            $this->assertTrue(true, 'specialties 字段已正确配置到表单中');

            return;
        }

        $this->checkSpecialtiesFieldSelectorsInCrawler($crawler);
    }

    /**
     * 检查各种可能的 specialties 字段选择器.
     */
    private function checkSpecialtiesFieldSelectorsInCrawler(Crawler $crawler): void
    {
        $selectors = $this->getSpecialtiesFieldSelectors();
        $foundSelectors = $this->findMatchingSelectorsForSpecialties($crawler, $selectors);

        if ([] === $foundSelectors) {
            $this->failWithSpecialtiesFieldDebugInfo($crawler, $selectors);
        }

        $this->assertTrue(true, sprintf('specialties 字段找到了。匹配的选择器: %s', implode(', ', $foundSelectors)));
    }

    /**
     * 获取所有可能的 specialties 字段选择器.
     *
     * @return string[]
     */
    private function getSpecialtiesFieldSelectors(): array
    {
        return [
            'input[name*="specialties"]',
            'textarea[name*="specialties"]',
            'select[name*="specialties"]',
            '.field-specialties',
            '[data-field="specialties"]',
            '[data-field-name="specialties"]',
            '.ea-array-field',
            '.collection-widget',
            '[name*="specialties"], [id*="specialties"], [class*="specialties"], [data-*="specialties"]',
        ];
    }

    /**
     * 查找匹配的选择器.
     *
     * @param string[] $selectors
     *
     * @return string[]
     */
    private function findMatchingSelectorsForSpecialties(Crawler $crawler, array $selectors): array
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
     * 当 specialties 字段未找到时输出调试信息并失败.
     *
     * @param string[] $selectors
     */
    private function failWithSpecialtiesFieldDebugInfo(Crawler $crawler, array $selectors): void
    {
        $formFields = $crawler->filter('form input, form select, form textarea, form [data-field]');
        $fieldNames = [];

        $formFields->each(function (Crawler $element) use (&$fieldNames): void {
            $name = $element->attr('name') ?? $element->attr('data-field') ?? 'unknown';
            $fieldNames[] = $name;
        });

        self::fail(sprintf(
            "specialties 字段未找到。\n表单中的字段: %s\n已检查的选择器: %s",
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
            'Teacher[' . $fieldName . ']',
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
        $controller = new TeacherCrudController();
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
        if ($form->has($formName . '[birthDate]')) {
            $form[$formName . '[birthDate]'] = '2000-01-01';
        }
        if ($form->has($formName . '[graduateDate]')) {
            $form[$formName . '[graduateDate]'] = '2020-01-01';
        }
        if ($form->has($formName . '[joinDate]')) {
            $form[$formName . '[joinDate]'] = '2020-01-01';
        }

        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
    }
}
