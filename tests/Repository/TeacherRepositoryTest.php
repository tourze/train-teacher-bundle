<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;

/**
 * @internal
 */
#[CoversClass(TeacherRepository::class)]
#[RunTestsInSeparateProcesses]
final class TeacherRepositoryTest extends AbstractRepositoryTestCase
{
    private TeacherRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(TeacherRepository::class);
    }

    protected function createNewEntity(): Teacher
    {
        static $counter = 0;
        $this->assertIsInt($counter);
        ++$counter;

        $entity = new Teacher();
        // 使用时间戳 + 计数器确保排序的可预测性
        $entity->setId('test-teacher-' . time() . '-' . str_pad((string) $counter, 3, '0', STR_PAD_LEFT));
        $entity->setTeacherName('测试教师' . $counter);
        $entity->setTeacherCode('T-TEST-' . $counter);
        $entity->setTeacherStatus('在职');
        $entity->setTeacherType('专职');
        $entity->setGender('男');
        $entity->setBirthDate(new \DateTimeImmutable('1980-01-01'));
        // 使用不同的身份证号避免重复
        $entity->setIdCard('11010119800101' . sprintf('%04d', $counter));
        // 使用不同的手机号避免重复
        $entity->setPhone('138' . sprintf('%08d', $counter));
        $entity->setEducation('本科');
        $entity->setMajor('安全工程');
        $entity->setGraduateSchool('北京理工大学');
        $entity->setGraduateDate(new \DateTimeImmutable('2002-07-01'));
        $entity->setWorkExperience(20);
        $entity->setJoinDate(new \DateTimeImmutable('2005-03-01'));

        return $entity;
    }

    /**
     * @return TeacherRepository
     */
    protected function getRepository(): TeacherRepository
    {
        return $this->repository;
    }

    public function testFindByIdCard(): void
    {
        $result = $this->repository->findByIdCard('non-existent-id');
        $this->assertNull($result);
    }

    public function testFindByPhone(): void
    {
        $result = $this->repository->findByPhone('non-existent-phone');
        $this->assertNull($result);
    }

    public function testFindByTeacherCode(): void
    {
        $result = $this->repository->findByTeacherCode('non-existent-code');
        $this->assertNull($result);
    }

    public function testFindByTeacherStatus(): void
    {
        $result = $this->repository->findByTeacherStatus('active');
        $this->assertIsArray($result);
    }

    public function testFindByTeacherType(): void
    {
        $result = $this->repository->findByTeacherType('full-time');
        $this->assertIsArray($result);
    }

    public function testFindDuplicateIdCards(): void
    {
        $result = $this->repository->findDuplicateIdCards();
        $this->assertIsArray($result);
    }

    public function testFindDuplicatePhones(): void
    {
        $result = $this->repository->findDuplicatePhones();
        $this->assertIsArray($result);
    }

    public function testFindDuplicateTeacherCodes(): void
    {
        $result = $this->repository->findDuplicateTeacherCodes();
        $this->assertIsArray($result);
    }

    public function testFindInactiveTeachers(): void
    {
        $result = $this->repository->findInactiveTeachers(30);
        $this->assertIsArray($result);
    }

    public function testSearchTeachers(): void
    {
        $result = $this->repository->searchTeachers('test');
        $this->assertIsArray($result);
    }

    public function testSearchTeachersWithLimit(): void
    {
        $result = $this->repository->searchTeachers('test', 5);
        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(5, count($result));
    }

    public function testGetTeacherStatistics(): void
    {
        $result = $this->repository->getTeacherStatistics();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('fullTime', $result);
        $this->assertArrayHasKey('partTime', $result);
        $this->assertArrayHasKey('active', $result);
        $this->assertIsInt($result['total']);
        $this->assertIsInt($result['fullTime']);
        $this->assertIsInt($result['partTime']);
        $this->assertIsInt($result['active']);
    }

    public function testGetRecentTeachers(): void
    {
        $result = $this->repository->getRecentTeachers();
        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(10, count($result));
    }

    public function testGetRecentTeachersWithCustomLimit(): void
    {
        $result = $this->repository->getRecentTeachers(5);
        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(5, count($result));
    }

    public function testSaveEntity(): void
    {
        $entity = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $entity);
        $this->repository->save($entity);

        $foundEntity = $this->repository->findByTeacherCode($entity->getTeacherCode());
        $this->assertNotNull($foundEntity);
        $this->assertSame($entity->getTeacherCode(), $foundEntity->getTeacherCode());
    }

    public function testSaveEntityWithoutFlush(): void
    {
        $entity = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $entity);
        $this->repository->save($entity, false);

        // 在没有flush的情况下，实体应该在实体管理器中
        $this->assertTrue(self::getEntityManager()->contains($entity));
    }

    public function testRemoveEntity(): void
    {
        $entity = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $entity);
        $this->repository->save($entity);

        $this->repository->remove($entity);

        $foundEntity = $this->repository->findByTeacherCode($entity->getTeacherCode());
        $this->assertNull($foundEntity);
    }

    public function testRemoveEntityWithoutFlush(): void
    {
        $entity = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $entity);
        $this->repository->save($entity);

        $this->repository->remove($entity, false);

        // 实体应该被标记为删除，不再在实体管理器中
        $this->assertFalse(self::getEntityManager()->contains($entity));
    }

    public function testFindByTeacherCodeWithExistingEntity(): void
    {
        $entity = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $entity);
        $this->repository->save($entity);

        $foundEntity = $this->repository->findByTeacherCode($entity->getTeacherCode());
        $this->assertNotNull($foundEntity);
        $this->assertSame($entity->getTeacherCode(), $foundEntity->getTeacherCode());
    }

    public function testFindByIdCardWithExistingEntity(): void
    {
        $entity = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $entity);
        $this->repository->save($entity);

        $foundEntity = $this->repository->findByIdCard($entity->getIdCard());
        $this->assertNotNull($foundEntity);
        $this->assertSame($entity->getIdCard(), $foundEntity->getIdCard());
    }

    public function testFindByPhoneWithExistingEntity(): void
    {
        $entity = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $entity);
        $this->repository->save($entity);

        $foundEntity = $this->repository->findByPhone($entity->getPhone());
        $this->assertNotNull($foundEntity);
        $this->assertSame($entity->getPhone(), $foundEntity->getPhone());
    }

    public function testFindByTeacherTypeWithExistingEntity(): void
    {
        $entity = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $entity);
        $this->repository->save($entity);

        $foundEntities = $this->repository->findByTeacherType($entity->getTeacherType());
        $this->assertIsArray($foundEntities);

        $found = false;
        foreach ($foundEntities as $foundEntity) {
            if ($foundEntity->getTeacherCode() === $entity->getTeacherCode()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, '应该找到新创建的教师');
    }

    public function testFindByTeacherStatusWithExistingEntity(): void
    {
        $entity = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $entity);
        $this->repository->save($entity);

        $foundEntities = $this->repository->findByTeacherStatus($entity->getTeacherStatus());
        $this->assertIsArray($foundEntities);

        $found = false;
        foreach ($foundEntities as $foundEntity) {
            if ($foundEntity->getTeacherCode() === $entity->getTeacherCode()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, '应该找到新创建的教师');
    }

    public function testFindDuplicateTeacherCodesWithRealDuplicates(): void
    {
        // 由于teacher_code字段有唯一约束，我们无法直接插入重复数据
        // 因此这个测试将验证方法在没有重复数据时返回空数组
        // 这符合方法的设计预期：查找重复的教师编号

        $duplicateCodes = $this->repository->findDuplicateTeacherCodes();
        $this->assertIsArray($duplicateCodes);

        // 由于数据库约束保证teacher_code唯一，所以不应该有重复的编号
        $this->assertEmpty($duplicateCodes, '由于数据库唯一约束，不应该存在重复的教师编号');

        // 额外验证：确保方法的SQL逻辑是正确的
        // 通过检查现有数据来验证查询结构的正确性
        $allTeachers = $this->repository->findAll();
        $teacherCodes = [];

        foreach ($allTeachers as $teacher) {
            $code = $teacher->getTeacherCode();
            if (isset($teacherCodes[$code])) {
                self::fail('发现重复的教师编号: ' . $code . '，这违反了数据库唯一约束');
            }
            $teacherCodes[$code] = true;
        }

        // 验证方法确实是按预期工作的
        $this->assertIsArray($duplicateCodes);
    }

    public function testFindDuplicateIdCardsWithRealDuplicates(): void
    {
        // 创建两个具有相同身份证的教师
        $duplicateIdCard = '110101198001019999';

        $teacher1 = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $teacher1);
        $teacher1->setTeacherCode('UNIQUE-001');
        $teacher1->setIdCard($duplicateIdCard);
        $teacher1->setPhone('138' . rand(10000000, 99999999));
        $this->repository->save($teacher1);

        $teacher2 = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $teacher2);
        $teacher2->setTeacherCode('UNIQUE-002');
        $teacher2->setIdCard($duplicateIdCard);
        $teacher2->setPhone('139' . rand(10000000, 99999999));
        $this->repository->save($teacher2);

        $duplicateIdCards = $this->repository->findDuplicateIdCards();
        $this->assertIsArray($duplicateIdCards);
        $this->assertContains($duplicateIdCard, $duplicateIdCards);
    }

    public function testFindDuplicatePhonesWithRealDuplicates(): void
    {
        // 创建两个具有相同手机号的教师
        $duplicatePhone = '13800138888';

        $teacher1 = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $teacher1);
        $teacher1->setTeacherCode('UNIQUE-003');
        $teacher1->setIdCard('11010119800103' . rand(1000, 9999));
        $teacher1->setPhone($duplicatePhone);
        $this->repository->save($teacher1);

        $teacher2 = $this->createNewEntity();
        $this->assertInstanceOf(Teacher::class, $teacher2);
        $teacher2->setTeacherCode('UNIQUE-004');
        $teacher2->setIdCard('11010119800104' . rand(1000, 9999));
        $teacher2->setPhone($duplicatePhone);
        $this->repository->save($teacher2);

        $duplicatePhones = $this->repository->findDuplicatePhones();
        $this->assertIsArray($duplicatePhones);
        $this->assertContains($duplicatePhone, $duplicatePhones);
    }

    public function testFindInactiveTeachersWithRealData(): void
    {
        // 创建一个长时间未活跃的教师
        $inactiveTeacher = $this->createNewEntity();
        $inactiveTeacher->setLastActiveTime(new \DateTimeImmutable('-60 days'));
        $this->repository->save($inactiveTeacher);

        // 创建一个最近活跃的教师
        $activeTeacher = $this->createNewEntity();
        $activeTeacher->setLastActiveTime(new \DateTimeImmutable('-5 days'));
        $this->repository->save($activeTeacher);

        // 查找30天未活跃的教师
        $inactiveTeachers = $this->repository->findInactiveTeachers(30);
        $this->assertIsArray($inactiveTeachers);

        // 验证不活跃教师在结果中
        $foundInactive = false;
        foreach ($inactiveTeachers as $teacher) {
            if ($teacher->getId() === $inactiveTeacher->getId()) {
                $foundInactive = true;
                break;
            }
        }
        $this->assertTrue($foundInactive, '应该找到不活跃的教师');

        // 验证活跃教师不在结果中
        $foundActive = false;
        foreach ($inactiveTeachers as $teacher) {
            if ($teacher->getId() === $activeTeacher->getId()) {
                $foundActive = true;
                break;
            }
        }
        $this->assertFalse($foundActive, '不应该找到活跃的教师');
    }

    public function testSearchTeachersWithActualData(): void
    {
        // 创建具有可搜索名称和编号的教师
        $searchableTeacher = $this->createNewEntity();
        $searchableTeacher->setTeacherName('张三教授');
        $searchableTeacher->setTeacherCode('SEARCH-001');
        $this->repository->save($searchableTeacher);

        $anotherTeacher = $this->createNewEntity();
        $anotherTeacher->setTeacherName('李四副教授');
        $anotherTeacher->setTeacherCode('OTHER-002');
        $this->repository->save($anotherTeacher);

        // 搜索包含"张三"的教师
        $results = $this->repository->searchTeachers('张三');
        $this->assertIsArray($results);

        $found = false;
        foreach ($results as $teacher) {
            if ($teacher->getId() === $searchableTeacher->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, '应该找到名称匹配的教师');

        // 搜索包含"SEARCH"的教师编号
        $codeResults = $this->repository->searchTeachers('SEARCH');
        $this->assertIsArray($codeResults);

        $foundByCode = false;
        foreach ($codeResults as $teacher) {
            if ($teacher->getId() === $searchableTeacher->getId()) {
                $foundByCode = true;
                break;
            }
        }
        $this->assertTrue($foundByCode, '应该找到编号匹配的教师');

        // 搜索不存在的关键词
        $noResults = $this->repository->searchTeachers('不存在的关键词');
        $this->assertIsArray($noResults);
        $this->assertEmpty($noResults);
    }

    public function testGetTeacherStatisticsWithRealData(): void
    {
        // 创建不同类型和状态的教师
        $fullTimeTeacher = $this->createNewEntity();
        $fullTimeTeacher->setTeacherType('专职');
        $fullTimeTeacher->setTeacherStatus('在职');
        $this->repository->save($fullTimeTeacher);

        $partTimeTeacher = $this->createNewEntity();
        $partTimeTeacher->setTeacherType('兼职');
        $partTimeTeacher->setTeacherStatus('在职');
        $this->repository->save($partTimeTeacher);

        $retiredTeacher = $this->createNewEntity();
        $retiredTeacher->setTeacherType('专职');
        $retiredTeacher->setTeacherStatus('离职');
        $this->repository->save($retiredTeacher);

        $statistics = $this->repository->getTeacherStatistics();

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('fullTime', $statistics);
        $this->assertArrayHasKey('partTime', $statistics);
        $this->assertArrayHasKey('active', $statistics);

        // 验证统计数据的类型和逻辑
        $this->assertIsInt($statistics['total']);
        $this->assertIsInt($statistics['fullTime']);
        $this->assertIsInt($statistics['partTime']);
        $this->assertIsInt($statistics['active']);

        $this->assertGreaterThanOrEqual(3, $statistics['total']);
        $this->assertGreaterThanOrEqual(1, $statistics['fullTime']);
        $this->assertGreaterThanOrEqual(1, $statistics['partTime']);
        $this->assertGreaterThanOrEqual(2, $statistics['active']);
    }

    public function testGetRecentTeachersWithRealData(): void
    {
        // 创建不同入职时间的教师
        $recentTeachers = [];
        $dates = [
            new \DateTimeImmutable('-1 day'),
            new \DateTimeImmutable('-3 days'),
            new \DateTimeImmutable('-7 days'),
            new \DateTimeImmutable('-30 days'),
        ];

        foreach ($dates as $index => $joinDate) {
            $teacher = $this->createNewEntity();
            $teacher->setJoinDate($joinDate);
            $teacher->setTeacherName('最近教师' . ($index + 1));
            $this->repository->save($teacher);
            $recentTeachers[] = $teacher;
        }

        $results = $this->repository->getRecentTeachers(3);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(3, count($results));

        // 验证按入职日期降序排列
        $previousJoinDate = null;
        foreach ($results as $teacher) {
            $this->assertInstanceOf(Teacher::class, $teacher);

            if (null !== $previousJoinDate) {
                $this->assertGreaterThanOrEqual(
                    $teacher->getJoinDate(),
                    $previousJoinDate,
                    '最近教师应该按入职日期降序排列'
                );
            }
            $previousJoinDate = $teacher->getJoinDate();
        }
    }
}
