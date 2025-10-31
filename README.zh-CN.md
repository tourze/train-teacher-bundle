# TrainTeacherBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/train-teacher-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/train-teacher-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/train-teacher-bundle?style=flat-square)]
(https://packagist.org/packages/tourze/train-teacher-bundle)
[![License](https://img.shields.io/packagist/l/tourze/train-teacher-bundle?style=flat-square)]
(https://packagist.org/packages/tourze/train-teacher-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)]
(https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)]
(https://codecov.io/gh/tourze/php-monorepo)

安全生产培训教师管理包，为安全生产培训系统提供教师队伍的全生命周期管理功能。

## 目录

- [功能特性](#功能特性)
  - [核心功能](#核心功能)
  - [技术特性](#技术特性)
- [依赖关系](#依赖关系)
- [安装](#安装)
  - [使用 Composer 安装](#使用-composer-安装)
  - [注册 Bundle](#注册-bundle)
  - [数据库迁移](#数据库迁移)
- [配置](#配置)
- [使用方法](#使用方法)
  - [教师管理](#教师管理)
  - [教师评价](#教师评价)
  - [绩效管理](#绩效管理)
- [高级用法](#高级用法)
- [数据模型](#数据模型)
- [控制台命令](#控制台命令)
- [API 文档](#api-文档)
- [测试](#测试)
- [贡献](#贡献)
- [许可证](#许可证)
- [更新日志](#更新日志)

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

## 依赖关系

本包需要以下依赖：

### PHP 要求
- PHP 8.1 或更高版本
- ext-filter
- ext-json

### Symfony 要求
- symfony/config ^7.3
- symfony/console ^7.3
- symfony/dependency-injection ^7.3
- symfony/doctrine-bridge ^7.3
- symfony/framework-bundle ^7.3
- symfony/http-kernel ^7.3
- symfony/routing ^7.3
- symfony/yaml ^7.3

### Doctrine 要求
- doctrine/collections ^2.3
- doctrine/dbal ^4.0
- doctrine/doctrine-bundle ^2.13
- doctrine/orm ^3.0
- doctrine/persistence ^4.1

### Tourze 要求
- tourze/bundle-dependency 0.0.*

### 开发要求
- phpstan/phpstan ^2.1
- phpunit/phpunit ^11.5

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

## 高级用法

### 自定义评价类型

您可以通过扩展基础评价逻辑来定义自定义评价类型：

```php
use Tourze\TrainTeacherBundle\Service\EvaluationService;

class CustomEvaluationService extends EvaluationService
{
    public function processCustomEvaluation(array $criteria): array
    {
        // 自定义评价逻辑
        return $this->processEvaluation($criteria);
    }
}
```

### 绩效指标自定义

通过实现自定义指标来自定义绩效计算：

```php
use Tourze\TrainTeacherBundle\Service\PerformanceService;

// 向绩效计算添加自定义指标
$customMetrics = [
    'innovation_score' => 0.15,     // 创新分数
    'collaboration_score' => 0.10,  // 协作分数
    'technical_skills' => 0.25      // 技术技能
];

$performance = $this->performanceService->calculatePerformanceWithCustomMetrics(
    $teacherId,
    $period,
    $customMetrics
);
```

### 数据导出与导入

导出教师数据用于外部分析：

```php
use Tourze\TrainTeacherBundle\Service\TeacherService;

// 导出教师数据为 CSV
$csvData = $this->teacherService->exportToCSV($filters);

// 从外部数据源导入教师数据
$importResult = $this->teacherService->importFromArray($teacherData);
```

### 事件驱动架构

Bundle 支持事件监听器来实现自定义业务逻辑：

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tourze\TrainTeacherBundle\Event\TeacherEvaluatedEvent;

class TeacherEvaluationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TeacherEvaluatedEvent::class => 'onTeacherEvaluated',
        ];
    }

    public function onTeacherEvaluated(TeacherEvaluatedEvent $event): void
    {
        // 教师评价后的自定义逻辑
        $teacher = $event->getTeacher();
        $evaluation = $event->getEvaluation();
        
        // 发送通知、更新外部系统等
    }
}
```

## 控制台命令

本包提供了多个用于教师管理操作的控制台命令。

### teacher:evaluation:reminder
发送教师评价提醒通知，提醒相关人员进行教师评价。

```bash
# 发送所有超期7天的评价提醒
php bin/console teacher:evaluation:reminder

# 发送特定类型的评价提醒
php bin/console teacher:evaluation:reminder --evaluation-type=student

# 为特定教师发送提醒
php bin/console teacher:evaluation:reminder --teacher-id=123

# 预览模式（不实际发送）
php bin/console teacher:evaluation:reminder --dry-run
```

### teacher:performance:calculate
定期计算教师绩效，支持批量计算和单个教师计算。

```bash
# 计算当前月份所有教师的绩效
php bin/console teacher:performance:calculate

# 计算指定月份的绩效
php bin/console teacher:performance:calculate 2024-01

# 计算特定教师的绩效
php bin/console teacher:performance:calculate --teacher-id=123

# 强制重新计算
php bin/console teacher:performance:calculate --force
```

### teacher:data:sync
同步教师数据，检查数据一致性和完整性。

```bash
# 同步所有教师数据
php bin/console teacher:data:sync

# 仅检查不执行
php bin/console teacher:data:sync --dry-run

# 自动修复数据问题
php bin/console teacher:data:sync --fix-data

# 检查重复数据
php bin/console teacher:data:sync --check-duplicates
```

### teacher:report:generate
生成各种教师相关的报告。

```bash
# 生成绩效报告
php bin/console teacher:report:generate performance

# 生成指定月份的评价报告
php bin/console teacher:report:generate evaluation --period=2024-01

# 生成统计报告并输出为CSV
php bin/console teacher:report:generate statistics --output-format=csv --output-file=report.csv

# 生成前10名教师的汇总报告
php bin/console teacher:report:generate summary --top-n=10 --include-details
```

## API 文档

请参考各服务类中的方法注释以获取详细的 API 文档。

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
