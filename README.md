# TrainTeacherBundle

安全生产培训教师管理包，为安全生产培训系统提供教师队伍的全生命周期管理功能。

## 功能特性

### 核心功能
- **教师信息管理**: 教师基本信息、联系方式、身份证信息、照片、简历、档案管理
- **教师分类管理**: 专职/兼职教师分类管理
- **多维度评价体系**: 学员评价、同行评价、管理层评价、自我评价
- **绩效评估系统**: 教师绩效评估与排名统计
- **统计分析**: 评价结果统计分析与反馈机制
- **状态管理**: 教师状态管理与数据同步

### 技术特性
- 基于 Symfony Framework 开发
- 使用 Doctrine ORM 进行数据管理
- 支持 PHP 8.1+
- 遵循 PSR 标准
- 完整的单元测试覆盖

## 安装

### 使用 Composer 安装

```bash
composer require tourze/train-teacher-bundle
```

### 注册 Bundle

在 `config/bundles.php` 中添加：

```php
return [
    // ...
    Tourze\TrainTeacherBundle\TrainTeacherBundle::class => ['all' => true],
];
```

### 数据库迁移

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## 配置

Bundle 使用默认配置即可正常工作，无需额外配置。

## 使用方法

### 教师管理

```php
use Tourze\TrainTeacherBundle\Service\TeacherService;

// 注入服务
public function __construct(
    private TeacherService $teacherService
) {}

// 创建教师
$teacherData = [
    'teacherName' => '张三',
    'teacherType' => '专职',
    'gender' => '男',
    'birthDate' => new \DateTime('1980-01-01'),
    'idCard' => '110101198001011234',
    'phone' => '13800138000',
    'email' => 'zhangsan@example.com',
    'education' => '本科',
    'major' => '安全工程',
    'graduateSchool' => '北京理工大学',
    'graduateDate' => new \DateTime('2002-07-01'),
    'workExperience' => 20,
    'specialties' => ['安全管理', '风险评估'],
    'teacherStatus' => '在职',
    'joinDate' => new \DateTime('2005-03-01'),
];

$teacher = $this->teacherService->createTeacher($teacherData);

// 获取教师信息
$teacher = $this->teacherService->getTeacherById($teacherId);
$teacher = $this->teacherService->getTeacherByCode($teacherCode);

// 搜索教师
$teachers = $this->teacherService->searchTeachers('张三');

// 获取统计信息
$statistics = $this->teacherService->getTeacherStatistics();
```

### 教师评价

```php
use Tourze\TrainTeacherBundle\Service\EvaluationService;

// 注入服务
public function __construct(
    private EvaluationService $evaluationService
) {}

// 提交评价
$evaluationData = [
    'evaluatorType' => '学员',
    'evaluationType' => '课程评价',
    'evaluationItems' => ['教学态度', '专业水平', '沟通能力'],
    'evaluationScores' => [
        '教学态度' => 5,
        '专业水平' => 4.5,
        '沟通能力' => 4.8,
    ],
    'evaluationComments' => '教学认真负责，专业知识扎实',
    'suggestions' => ['建议增加实践案例'],
    'isAnonymous' => false,
];

$evaluation = $this->evaluationService->submitEvaluation(
    $teacherId, 
    $evaluatorId, 
    $evaluationData
);

// 获取评价统计
$statistics = $this->evaluationService->getEvaluationStatistics($teacherId);

// 生成评价报告
$report = $this->evaluationService->generateEvaluationReport($teacherId);
```

### 绩效管理

```php
use Tourze\TrainTeacherBundle\Service\PerformanceService;

// 注入服务
public function __construct(
    private PerformanceService $performanceService
) {}

// 计算绩效
$period = new \DateTime('2024-01-01');
$performance = $this->performanceService->calculatePerformance($teacherId, $period);

// 获取绩效排名
$ranking = $this->performanceService->getPerformanceRanking(20);

// 生成绩效报告
$report = $this->performanceService->generatePerformanceReport($teacherId);

// 比较教师绩效
$comparison = $this->performanceService->compareTeacherPerformance(
    [$teacherId1, $teacherId2], 
    $period
);
```

## 数据模型

### Teacher（教师）
- 基本信息：姓名、编号、类型、性别、出生日期
- 联系信息：电话、邮箱、地址
- 身份信息：身份证号
- 教育背景：学历、专业、毕业院校、毕业日期
- 工作信息：工作经验、专业特长、状态、入职日期

### TeacherEvaluation（教师评价）
- 评价信息：评价者类型、评价者ID、评价类型、评价日期
- 评价内容：评价项目、评价分数、总体评分、评价意见
- 建议信息：建议内容、是否匿名
- 状态信息：评价状态

### TeacherPerformance（教师绩效）
- 绩效信息：绩效周期、平均评价分数、绩效指标
- 评估结果：绩效分数、绩效等级、成就

## API 文档

详细的 API 文档请参考各服务类的方法注释。

## 测试

运行单元测试：

```bash
./vendor/bin/phpunit packages/train-teacher-bundle/tests
```

## 贡献

欢迎提交 Issue 和 Pull Request。

## 许可证

MIT License

## 更新日志

### v1.0.0
- 初始版本发布
- 实现教师管理基础功能
- 实现多维度评价体系
- 实现绩效评估系统
