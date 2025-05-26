# train-teacher-bundle 开发计划

## 1. 功能描述

教师管理包，负责安全生产培训教师的全生命周期管理功能。包括教师基本信息管理、教师资质认证、教师培训和考核、教师工作量统计、教师评价管理、专职/兼职教师管理、教师学历和经验验证、教师资格证书管理、教师培训记录、教师绩效评估、教师排课管理等功能。符合AQ8011-2023教师资质要求，实现教师队伍的规范化管理。

## 2. 完整能力要求

### 2.1 现有能力

- ✅ 基础Bundle结构 - TrainTeacherBundle类
- ✅ 依赖注入配置 - DependencyInjection支持
- ✅ 资源配置 - Resources目录结构
- ✅ 基础框架搭建完成

### 2.2 需要增强的能力

#### 2.2.1 符合AQ8011-2023教师资质要求

- [ ] 专职教师管理（不少于教师总数的1/3）
- [ ] 兼职教师管理和资质验证
- [ ] 教师学历要求验证（大专以上学历）
- [ ] 教师工作经验验证（3年以上相关工作经验）
- [ ] 教师资格证书管理
- [ ] 教师专业技能认证

#### 2.2.2 教师基本信息管理

- [ ] 教师个人信息管理
- [ ] 教师联系方式管理
- [ ] 教师身份证信息管理
- [ ] 教师照片管理
- [ ] 教师简历管理
- [ ] 教师档案管理

#### 2.2.3 教师资质认证

- [ ] 教师资格证书管理
- [ ] 专业技能证书管理
- [ ] 安全培训师资格管理
- [ ] 特种作业教师资格管理
- [ ] 证书有效期监控
- [ ] 证书续期提醒

#### 2.2.4 教师培训和考核

- [ ] 教师培训计划管理
- [ ] 教师培训记录管理
- [ ] 教师考核标准管理
- [ ] 教师考核结果管理
- [ ] 教师技能提升管理
- [ ] 教师继续教育管理

#### 2.2.5 教师工作量统计

- [ ] 教师授课时长统计
- [ ] 教师课程数量统计
- [ ] 教师学员数量统计
- [ ] 教师工作负荷分析
- [ ] 教师绩效指标统计
- [ ] 教师薪酬计算支持

#### 2.2.6 教师评价管理

- [ ] 学员对教师评价
- [ ] 同行对教师评价
- [ ] 管理层对教师评价
- [ ] 教师自我评价
- [ ] 评价结果统计分析
- [ ] 评价反馈机制

#### 2.2.7 教师排课管理

- [ ] 教师可用时间管理
- [ ] 教师课程安排
- [ ] 教师冲突检测
- [ ] 教师工作量平衡
- [ ] 教师替代安排
- [ ] 排课优化算法

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

#### TeacherQualification（教师资质）

```php
class TeacherQualification
{
    private string $id;
    private Teacher $teacher;
    private string $qualificationType;  // 资质类型
    private string $qualificationName;  // 资质名称
    private string $certificateNumber;  // 证书编号
    private string $issuingAuthority;  // 发证机关
    private \DateTimeInterface $issueDate;  // 发证日期
    private \DateTimeInterface $validFrom;  // 有效期开始
    private \DateTimeInterface $validTo;  // 有效期结束
    private array $qualificationScope;  // 资质范围
    private string $qualificationLevel;  // 资质等级
    private string $qualificationStatus;  // 资质状态
    private array $attachments;  // 附件
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
}
```

#### TeacherTraining（教师培训）

```php
class TeacherTraining
{
    private string $id;
    private Teacher $teacher;
    private string $trainingType;  // 培训类型
    private string $trainingName;  // 培训名称
    private string $trainingProvider;  // 培训机构
    private \DateTimeInterface $trainingStartDate;  // 培训开始日期
    private \DateTimeInterface $trainingEndDate;  // 培训结束日期
    private int $trainingHours;  // 培训学时
    private array $trainingContent;  // 培训内容
    private string $trainingResult;  // 培训结果
    private float $trainingScore;  // 培训成绩
    private string $certificateNumber;  // 证书编号
    private array $trainingMaterials;  // 培训资料
    private string $trainingStatus;  // 培训状态
    private \DateTimeInterface $createTime;
}
```

#### TeacherAssessment（教师考核）

```php
class TeacherAssessment
{
    private string $id;
    private Teacher $teacher;
    private string $assessmentType;  // 考核类型
    private string $assessmentPeriod;  // 考核周期
    private \DateTimeInterface $assessmentDate;  // 考核日期
    private array $assessmentCriteria;  // 考核标准
    private array $assessmentItems;  // 考核项目
    private array $assessmentScores;  // 考核分数
    private float $totalScore;  // 总分
    private string $assessmentLevel;  // 考核等级
    private array $assessmentComments;  // 考核意见
    private array $improvementSuggestions;  // 改进建议
    private string $assessor;  // 考核人
    private string $assessmentStatus;  // 考核状态
    private \DateTimeInterface $createTime;
}
```

#### TeacherWorkload（教师工作量）

```php
class TeacherWorkload
{
    private string $id;
    private Teacher $teacher;
    private \DateTimeInterface $workDate;  // 工作日期
    private string $workType;  // 工作类型（授课、培训、会议等）
    private string $courseId;  // 课程ID
    private string $classroomId;  // 教室ID
    private \DateTimeInterface $startTime;  // 开始时间
    private \DateTimeInterface $endTime;  // 结束时间
    private float $workHours;  // 工作时长
    private int $studentCount;  // 学员数量
    private array $workContent;  // 工作内容
    private string $workStatus;  // 工作状态
    private array $workNotes;  // 工作备注
    private \DateTimeInterface $createTime;
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

#### TeacherSchedule（教师排课）

```php
class TeacherSchedule
{
    private string $id;
    private Teacher $teacher;
    private string $courseId;  // 课程ID
    private string $classroomId;  // 教室ID
    private \DateTimeInterface $scheduleDate;  // 排课日期
    private \DateTimeInterface $startTime;  // 开始时间
    private \DateTimeInterface $endTime;  // 结束时间
    private string $scheduleType;  // 排课类型
    private string $scheduleStatus;  // 排课状态
    private array $scheduleNotes;  // 排课备注
    private string $scheduler;  // 排课人
    private \DateTimeInterface $scheduleTime;  // 排课时间
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
}
```

#### TeacherAvailability（教师可用时间）

```php
class TeacherAvailability
{
    private string $id;
    private Teacher $teacher;
    private string $dayOfWeek;  // 星期几
    private \DateTimeInterface $availableFrom;  // 可用开始时间
    private \DateTimeInterface $availableTo;  // 可用结束时间
    private string $availabilityType;  // 可用类型（固定、临时）
    private \DateTimeInterface $effectiveFrom;  // 生效开始日期
    private \DateTimeInterface $effectiveTo;  // 生效结束日期
    private bool $isActive;  // 是否启用
    private string $notes;  // 备注
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
}
```

#### TeacherPerformance（教师绩效）

```php
class TeacherPerformance
{
    private string $id;
    private Teacher $teacher;
    private \DateTimeInterface $performancePeriod;  // 绩效周期
    private float $teachingHours;  // 授课时长
    private int $courseCount;  // 课程数量
    private int $studentCount;  // 学员数量
    private float $averageEvaluation;  // 平均评价分数
    private float $passRate;  // 学员通过率
    private float $satisfactionRate;  // 满意度
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
    public function validateTeacherRequirements(array $teacherData): array;
    public function changeTeacherStatus(string $teacherId, string $status, string $reason): Teacher;
}
```

#### QualificationService

```php
class QualificationService
{
    public function addQualification(string $teacherId, array $qualificationData): TeacherQualification;
    public function updateQualification(string $qualificationId, array $qualificationData): TeacherQualification;
    public function checkQualificationExpiry(string $teacherId): array;
    public function renewQualification(string $qualificationId, array $renewalData): TeacherQualification;
    public function getExpiringQualifications(int $days): array;
    public function validateTeachingScope(string $teacherId, string $courseType): bool;
}
```

#### TrainingService

```php
class TrainingService
{
    public function createTrainingPlan(string $teacherId, array $trainingData): TeacherTraining;
    public function enrollTeacherTraining(string $teacherId, string $trainingId): TeacherTraining;
    public function recordTrainingResult(string $trainingId, array $resultData): TeacherTraining;
    public function getTrainingHistory(string $teacherId): array;
    public function getTrainingRequirements(string $teacherId): array;
    public function generateTrainingReport(string $teacherId): array;
}
```

#### AssessmentService

```php
class AssessmentService
{
    public function conductAssessment(string $teacherId, array $assessmentData): TeacherAssessment;
    public function calculateAssessmentScore(string $assessmentId): float;
    public function determineAssessmentLevel(float $score): string;
    public function generateAssessmentReport(string $assessmentId): array;
    public function getAssessmentHistory(string $teacherId): array;
    public function scheduleAssessment(string $teacherId, \DateTimeInterface $assessmentDate): TeacherAssessment;
}
```

#### WorkloadService

```php
class WorkloadService
{
    public function recordWorkload(string $teacherId, array $workloadData): TeacherWorkload;
    public function calculateWorkloadStatistics(string $teacherId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array;
    public function getTeacherWorkload(string $teacherId, \DateTimeInterface $date): array;
    public function balanceWorkload(array $teacherIds): array;
    public function generateWorkloadReport(string $teacherId): array;
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

#### ScheduleService

```php
class ScheduleService
{
    public function createSchedule(string $teacherId, array $scheduleData): TeacherSchedule;
    public function updateSchedule(string $scheduleId, array $scheduleData): TeacherSchedule;
    public function checkScheduleConflict(string $teacherId, \DateTimeInterface $startTime, \DateTimeInterface $endTime): array;
    public function getTeacherSchedule(string $teacherId, \DateTimeInterface $date): array;
    public function findAvailableTeachers(\DateTimeInterface $startTime, \DateTimeInterface $endTime, array $requirements): array;
    public function optimizeSchedule(array $scheduleRequirements): array;
}
```

#### AvailabilityService

```php
class AvailabilityService
{
    public function setAvailability(string $teacherId, array $availabilityData): TeacherAvailability;
    public function updateAvailability(string $availabilityId, array $availabilityData): TeacherAvailability;
    public function getTeacherAvailability(string $teacherId): array;
    public function checkAvailability(string $teacherId, \DateTimeInterface $startTime, \DateTimeInterface $endTime): bool;
    public function findAvailableSlots(string $teacherId, \DateTimeInterface $date): array;
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

### 5.2 资质管理命令

#### QualificationExpiryCheckCommand

```php
class QualificationExpiryCheckCommand extends Command
{
    protected static $defaultName = 'teacher:qualification:expiry-check';
    
    // 检查教师资质到期情况（每日执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### QualificationRenewalReminderCommand

```php
class QualificationRenewalReminderCommand extends Command
{
    protected static $defaultName = 'teacher:qualification:renewal-reminder';
    
    // 发送资质续期提醒
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.3 培训考核命令

#### TrainingScheduleCommand

```php
class TrainingScheduleCommand extends Command
{
    protected static $defaultName = 'teacher:training:schedule';
    
    // 安排教师培训
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### AssessmentScheduleCommand

```php
class AssessmentScheduleCommand extends Command
{
    protected static $defaultName = 'teacher:assessment:schedule';
    
    // 安排教师考核
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.4 工作量统计命令

#### WorkloadCalculateCommand

```php
class WorkloadCalculateCommand extends Command
{
    protected static $defaultName = 'teacher:workload:calculate';
    
    // 计算教师工作量（每日执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### WorkloadBalanceCommand

```php
class WorkloadBalanceCommand extends Command
{
    protected static $defaultName = 'teacher:workload:balance';
    
    // 平衡教师工作量
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.5 绩效评估命令

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

### 5.6 排课管理命令

#### ScheduleOptimizeCommand

```php
class ScheduleOptimizeCommand extends Command
{
    protected static $defaultName = 'teacher:schedule:optimize';
    
    // 优化教师排课
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### ScheduleConflictCheckCommand

```php
class ScheduleConflictCheckCommand extends Command
{
    protected static $defaultName = 'teacher:schedule:conflict-check';
    
    // 检查排课冲突
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.7 报告生成命令

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

## 6. 配置和集成

### 6.1 Bundle配置

```yaml
# config/packages/train_teacher.yaml
train_teacher:
    teacher:
        full_time_ratio: 0.33  # 专职教师比例要求
        min_education: 'college'  # 最低学历要求
        min_experience_years: 3  # 最低工作经验年数
        auto_approval: false
        
    qualification:
        expiry_warning_days: [90, 30, 7]  # 到期提醒天数
        auto_renewal_enabled: false
        required_qualifications:
            - teacher_certificate
            - safety_training_qualification
            - professional_skill_certificate
            
    training:
        annual_training_hours: 40  # 年度培训学时要求
        training_frequency: 'quarterly'
        auto_enrollment: false
        training_types:
            - professional_development
            - safety_training
            - teaching_skills
            - continuing_education
            
    assessment:
        assessment_frequency: 'annually'  # 考核频率
        scoring_system: 'percentage'
        pass_threshold: 80
        assessment_levels:
            - excellent  # 优秀 (90-100)
            - good      # 良好 (80-89)
            - qualified # 合格 (70-79)
            - unqualified # 不合格 (<70)
            
    workload:
        max_weekly_hours: 40  # 最大周工作时长
        max_daily_hours: 8   # 最大日工作时长
        workload_balance_enabled: true
        overtime_threshold: 1.2  # 超时阈值
        
    evaluation:
        evaluation_frequency: 'after_course'
        anonymous_evaluation: true
        min_evaluation_count: 5  # 最少评价数量
        evaluation_weight:
            student: 0.6    # 学员评价权重
            peer: 0.3       # 同行评价权重
            management: 0.1 # 管理层评价权重
            
    schedule:
        auto_scheduling: false
        conflict_detection: true
        schedule_optimization: true
        advance_booking_days: 7  # 提前预约天数
        
    performance:
        calculation_frequency: 'monthly'
        performance_metrics:
            - teaching_hours
            - course_count
            - student_count
            - evaluation_score
            - pass_rate
            - satisfaction_rate
            
    notifications:
        enabled: true
        email_notifications: true
        sms_notifications: false
        notification_types:
            - qualification_expiry
            - training_scheduled
            - assessment_scheduled
            - schedule_conflict
            - evaluation_reminder
            
    reporting:
        auto_generation: true
        report_formats: ['pdf', 'excel']
        report_retention_months: 36
        
    cache:
        enabled: true
        ttl: 3600  # 1小时
        qualification_ttl: 86400  # 24小时
        schedule_ttl: 1800  # 30分钟
```

### 6.2 依赖包

- `train-institution-bundle` - 培训机构管理
- `train-course-bundle` - 课程管理
- `train-classroom-bundle` - 教室管理
- `real-name-authentication-bundle` - 实名认证
- `doctrine-entity-checker-bundle` - 实体检查
- `doctrine-timestamp-bundle` - 时间戳管理

## 7. 测试计划

### 7.1 单元测试

- [ ] Teacher实体测试
- [ ] TeacherQualification实体测试
- [ ] TeacherService测试
- [ ] QualificationService测试
- [ ] TrainingService测试
- [ ] AssessmentService测试

### 7.2 集成测试

- [ ] 教师注册流程测试
- [ ] 资质管理流程测试
- [ ] 培训考核流程测试
- [ ] 排课管理流程测试
- [ ] 绩效评估流程测试

### 7.3 性能测试

- [ ] 大量教师数据处理测试
- [ ] 排课优化算法性能测试
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
- 资质到期预警率
- 培训完成率
- 考核通过率
- 排课冲突率
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
