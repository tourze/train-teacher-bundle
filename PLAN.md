# train-teacher-bundle 开发计划

## 1. 功能描述

教师管理包，负责安全生产培训教师的基本信息管理和评价管理功能。包括教师基本信息管理、教师评价管理、专职/兼职教师管理、教师绩效评估等核心功能。实现教师队伍的基础管理。

## 2. 完整能力要求

### 2.1 现有能力

- ✅ 基础Bundle结构 - TrainTeacherBundle类
- ✅ 依赖注入配置 - DependencyInjection支持
- ✅ 资源配置 - Resources目录结构
- ✅ 基础框架搭建完成

### 2.2 需要增强的能力

#### 2.2.1 教师基本信息管理

- [ ] 教师个人信息管理
- [ ] 教师联系方式管理
- [ ] 教师身份证信息管理
- [ ] 教师照片管理
- [ ] 教师简历管理
- [ ] 教师档案管理
- [ ] 专职/兼职教师管理

#### 2.2.2 教师评价管理

- [ ] 学员对教师评价
- [ ] 同行对教师评价
- [ ] 管理层对教师评价
- [ ] 教师自我评价
- [ ] 评价结果统计分析
- [ ] 评价反馈机制

#### 2.2.3 教师绩效评估

- [ ] 教师绩效指标统计
- [ ] 绩效分数计算
- [ ] 绩效等级评定
- [ ] 绩效报告生成
- [ ] 绩效排名统计

## 3. 实体设计

### 3.1 需要新增的实体

#### Teacher（教师）

```php
class Teacher
{
    private string $id;
    private string $teacherCode;  // 教师编号
    private string $teacherName;  // 教师姓名
    private string $teacherType;  // 教师类型（专职、兼职）
    private string $gender;  // 性别
    private \DateTimeInterface $birthDate;  // 出生日期
    private string $idCard;  // 身份证号
    private string $phone;  // 联系电话
    private string $email;  // 邮箱
    private string $address;  // 地址
    private string $education;  // 学历
    private string $major;  // 专业
    private string $graduateSchool;  // 毕业院校
    private \DateTimeInterface $graduateDate;  // 毕业日期
    private int $workExperience;  // 工作经验（年）
    private array $specialties;  // 专业特长
    private string $teacherStatus;  // 教师状态
    private string $profilePhoto;  // 头像
    private \DateTimeInterface $joinDate;  // 入职日期
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
}
```

#### TeacherEvaluation（教师评价）

```php
class TeacherEvaluation
{
    private string $id;
    private Teacher $teacher;
    private string $evaluatorType;  // 评价者类型（学员、同行、管理层）
    private string $evaluatorId;  // 评价者ID
    private string $evaluationType;  // 评价类型
    private \DateTimeInterface $evaluationDate;  // 评价日期
    private array $evaluationItems;  // 评价项目
    private array $evaluationScores;  // 评价分数
    private float $overallScore;  // 总体评分
    private string $evaluationComments;  // 评价意见
    private array $suggestions;  // 建议
    private bool $isAnonymous;  // 是否匿名
    private string $evaluationStatus;  // 评价状态
    private \DateTimeInterface $createTime;
}
```

#### TeacherPerformance（教师绩效）

```php
class TeacherPerformance
{
    private string $id;
    private Teacher $teacher;
    private \DateTimeInterface $performancePeriod;  // 绩效周期
    private float $averageEvaluation;  // 平均评价分数
    private array $performanceMetrics;  // 绩效指标
    private float $performanceScore;  // 绩效分数
    private string $performanceLevel;  // 绩效等级
    private array $achievements;  // 成就
    private \DateTimeInterface $createTime;
}
```

## 4. 服务设计

### 4.1 核心服务

#### TeacherService

```php
class TeacherService
{
    public function createTeacher(array $teacherData): Teacher;
    public function updateTeacher(string $teacherId, array $teacherData): Teacher;
    public function getTeacherById(string $teacherId): ?Teacher;
    public function getTeachersByType(string $type): array;
    public function getTeachersByStatus(string $status): array;
    public function changeTeacherStatus(string $teacherId, string $status, string $reason): Teacher;
}
```

#### EvaluationService

```php
class EvaluationService
{
    public function submitEvaluation(string $teacherId, string $evaluatorId, array $evaluationData): TeacherEvaluation;
    public function calculateAverageEvaluation(string $teacherId): float;
    public function getEvaluationStatistics(string $teacherId): array;
    public function generateEvaluationReport(string $teacherId): array;
    public function getTopRatedTeachers(int $limit): array;
}
```

#### PerformanceService

```php
class PerformanceService
{
    public function calculatePerformance(string $teacherId, \DateTimeInterface $period): TeacherPerformance;
    public function updatePerformanceMetrics(string $performanceId, array $metrics): TeacherPerformance;
    public function getPerformanceHistory(string $teacherId): array;
    public function compareTeacherPerformance(array $teacherIds): array;
    public function generatePerformanceReport(string $teacherId): array;
    public function getPerformanceRanking(): array;
}
```

## 5. Command设计

### 5.1 教师管理命令

#### TeacherDataSyncCommand

```php
class TeacherDataSyncCommand extends Command
{
    protected static $defaultName = 'teacher:data:sync';
    
    // 同步教师数据（每日执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### TeacherStatusCheckCommand

```php
class TeacherStatusCheckCommand extends Command
{
    protected static $defaultName = 'teacher:status:check';
    
    // 检查教师状态
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.2 绩效评估命令

#### PerformanceCalculateCommand

```php
class PerformanceCalculateCommand extends Command
{
    protected static $defaultName = 'teacher:performance:calculate';
    
    // 计算教师绩效（每月执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### EvaluationReminderCommand

```php
class EvaluationReminderCommand extends Command
{
    protected static $defaultName = 'teacher:evaluation:reminder';
    
    // 发送评价提醒
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.3 报告生成命令

#### TeacherReportCommand

```php
class TeacherReportCommand extends Command
{
    protected static $defaultName = 'teacher:report:generate';
    
    // 生成教师报告（每月执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### PerformanceReportCommand

```php
class PerformanceReportCommand extends Command
{
    protected static $defaultName = 'teacher:performance:report';
    
    // 生成绩效报告
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

## 6. 依赖包

- `train-institution-bundle` - 培训机构管理
- `train-course-bundle` - 课程管理
- `real-name-authentication-bundle` - 实名认证
- `doctrine-entity-checker-bundle` - 实体检查
- `doctrine-timestamp-bundle` - 时间戳管理

## 7. 测试计划

### 7.1 单元测试

- [ ] Teacher实体测试
- [ ] TeacherEvaluation实体测试
- [ ] TeacherService测试
- [ ] EvaluationService测试
- [ ] PerformanceService测试

### 7.2 集成测试

- [ ] 教师注册流程测试
- [ ] 评价管理流程测试
- [ ] 绩效评估流程测试

### 7.3 性能测试

- [ ] 大量教师数据处理测试
- [ ] 绩效计算性能测试

## 8. 部署和运维

### 8.1 部署要求

- PHP 8.2+
- MySQL 8.0+ / PostgreSQL 14+
- Redis（缓存）
- 足够的存储空间（文档和报告）
- 定时任务支持

### 8.2 监控指标

- 教师注册成功率
- 评价完成率
- 绩效评估完成率

### 8.3 安全要求

- [ ] 教师数据访问控制
- [ ] 敏感信息加密存储
- [ ] 操作审计日志
- [ ] 评价数据匿名化

---

**文档版本**: v1.0
**创建日期**: 2024年12月
**负责人**: 开发团队
