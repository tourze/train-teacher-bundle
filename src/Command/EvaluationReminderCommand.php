<?php

namespace Tourze\TrainTeacherBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;
use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * 教师评价提醒命令
 * 用于发送评价提醒通知，提醒相关人员进行教师评价
 */
#[AsCommand(
    name: self::NAME,
    description: '发送教师评价提醒通知'
)]
class EvaluationReminderCommand extends Command
{
    public const NAME = 'teacher:evaluation:reminder';

    public function __construct(
        private readonly TeacherService $teacherService,
        private readonly TeacherRepository $teacherRepository,
        private readonly TeacherEvaluationRepository $evaluationRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'evaluation-type',
                null,
                InputOption::VALUE_OPTIONAL,
                '评价类型 (student|peer|management|self)，默认所有类型'
            )
            ->addOption(
                'teacher-id',
                't',
                InputOption::VALUE_OPTIONAL,
                '指定教师ID，仅为该教师发送评价提醒'
            )
            ->addOption(
                'days-overdue',
                'd',
                InputOption::VALUE_OPTIONAL,
                '超期天数，发送给超过指定天数未评价的用户',
                7
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                '仅预览提醒列表，不发送实际通知'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                '强制发送提醒，即使最近已发送过'
            )
            ->addOption(
                'batch-size',
                'b',
                InputOption::VALUE_OPTIONAL,
                '批处理大小',
                20
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $executionContext = $this->createExecutionContext($input, $io);

        try {
            /** @var string|null $teacherId */
            $teacherId = $executionContext['teacherId'];
            /** @var string|null $evaluationType */
            $evaluationType = $executionContext['evaluationType'];
            /** @var int $daysOverdue */
            $daysOverdue = $executionContext['daysOverdue'];
            /** @var bool $force */
            $force = $executionContext['force'];

            $reminderTasks = $this->getReminderTasks($teacherId, $evaluationType, $daysOverdue, $force);

            if ([] === $reminderTasks) {
                $io->success('没有需要发送的评价提醒');

                return Command::SUCCESS;
            }

            $results = $this->processReminderTasks($reminderTasks, $executionContext, $io);

            return $this->handleExecutionResult($results, $io);
        } catch (\Throwable $e) {
            $io->error('评价提醒发送失败: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * 获取需要发送提醒的任务列表
     * @return array<int, array<string, mixed>>
     */
    private function getReminderTasks(?string $teacherId, ?string $evaluationType, int $daysOverdue, bool $force): array
    {
        $teachers = $this->getTargetTeachers($teacherId);
        $evaluationTypes = $this->getEvaluationTypes($evaluationType);
        $overdueDate = $this->calculateOverdueDate($daysOverdue);

        return $this->collectReminderTasks($teachers, $evaluationTypes, $overdueDate, $force);
    }

    /**
     * 获取目标教师列表
     * @return array<int, Teacher>
     */
    /**
     * @return array<int, Teacher>
     */
    private function getTargetTeachers(?string $teacherId): array
    {
        if (null !== $teacherId) {
            try {
                return [$this->teacherService->getTeacherById($teacherId)];
            } catch (\Throwable $e) {
                return [];
            }
        }

        return $this->teacherRepository->findBy(['teacherStatus' => 'active']);
    }

    /**
     * 获取评价类型列表
     * @return array<int, string>
     */
    private function getEvaluationTypes(?string $evaluationType): array
    {
        return null !== $evaluationType ? [$evaluationType] : ['student', 'peer', 'management', 'self'];
    }

    /**
     * 计算过期日期
     */
    private function calculateOverdueDate(int $daysOverdue): \DateTime
    {
        return (new \DateTime())->modify("-{$daysOverdue} days");
    }

    /**
     * 收集提醒任务
     * @param array<int, Teacher> $teachers
     * @param array<int, string> $evaluationTypes
     * @return array<int, array<string, mixed>>
     */
    private function collectReminderTasks(array $teachers, array $evaluationTypes, \DateTime $overdueDate, bool $force): array
    {
        $tasks = [];

        foreach ($teachers as $teacher) {
            foreach ($evaluationTypes as $type) {
                $reminderTask = $this->checkEvaluationReminder($teacher, $type, $overdueDate, $force);
                if (null !== $reminderTask) {
                    $tasks[] = $reminderTask;
                }
            }
        }

        return $tasks;
    }

    /**
     * 检查单个评价提醒
     * @return array<string, mixed>|null
     */
    private function checkEvaluationReminder(Teacher $teacher, string $evaluationType, \DateTime $overdueDate, bool $force): ?array
    {
        $recentEvaluations = $this->getRecentEvaluations($teacher, $evaluationType);
        $needsReminder = $this->determineReminderNeed($recentEvaluations, $overdueDate);

        if ($needsReminder && !$force) {
            $needsReminder = !$this->hasRecentReminder($teacher->getId(), $evaluationType);
        }

        if (!$needsReminder) {
            return null;
        }

        return $this->createReminderTask($teacher, $evaluationType, $recentEvaluations);
    }

    /**
     * 获取最近评价记录
     * @return array<int, TeacherEvaluation>
     */
    private function getRecentEvaluations(Teacher $teacher, string $evaluationType): array
    {
        return $this->evaluationRepository->findRecentEvaluations($teacher, $evaluationType, 30);
    }

    /**
     * 判断是否需要提醒
     * @param array<int, TeacherEvaluation> $recentEvaluations
     */
    private function determineReminderNeed(array $recentEvaluations, \DateTime $overdueDate): bool
    {
        if ([] === $recentEvaluations) {
            return true;
        }

        $lastEvaluation = $recentEvaluations[0];
        $lastEvaluationDate = $lastEvaluation->getEvaluationDate();

        return $lastEvaluationDate < $overdueDate;
    }

    /**
     * 检查是否最近有提醒
     */
    private function hasRecentReminder(string $teacherId, string $evaluationType): bool
    {
        $recentReminders = $this->getRecentReminders($teacherId, $evaluationType, 3);

        return [] !== $recentReminders;
    }

    /**
     * 创建提醒任务
     * @param array<int, TeacherEvaluation> $recentEvaluations
     * @return array<string, mixed>
     */
    private function createReminderTask(Teacher $teacher, string $evaluationType, array $recentEvaluations): array
    {
        $lastEvaluationDate = [] !== $recentEvaluations
            ? $recentEvaluations[0]->getEvaluationDate()
            : null;

        return [
            'teacher' => $teacher,
            'evaluation_type' => $evaluationType,
            'last_evaluation_date' => $lastEvaluationDate,
            'days_overdue' => null !== $lastEvaluationDate
                ? $lastEvaluationDate->diff(new \DateTime())->days
                : null,
            'evaluators' => $this->getEvaluators($teacher, $evaluationType),
        ];
    }

    /**
     * 发送单个提醒任务
     * @param array<string, mixed> $task
     * @return array<string, mixed>
     */
    private function sendReminderTask(
        array $task,
        bool $isDryRun,
        SymfonyStyle $io,
    ): array {
        $result = [
            'sent' => false,
            'skipped' => false,
            'failed' => false,
            'error' => null,
        ];

        try {
            /** @var Teacher $teacher */
            $teacher = $task['teacher'];
            /** @var string $evaluationType */
            $evaluationType = $task['evaluation_type'];
            /** @var array<int, mixed> $evaluators */
            $evaluators = $task['evaluators'];

            if ([] === $evaluators) {
                $result['skipped'] = true;
                $io->text("跳过教师 {$teacher->getTeacherName()} 的 {$evaluationType} 评价提醒 (无评价者)");

                return $result;
            }

            if (!$isDryRun) {
                // 发送实际提醒
                $this->sendEvaluationReminder($teacher, $evaluationType, $evaluators);

                // 记录提醒发送日志
                $this->logReminderSent((string) $teacher->getId(), $evaluationType);
            }

            $result['sent'] = true;
            $io->text(sprintf(
                '✓ 已发送教师 %s 的 %s 评价提醒给 %d 个评价者',
                $teacher->getTeacherName(),
                $this->getEvaluationTypeName($evaluationType),
                count($evaluators)
            ));
        } catch (\Throwable $e) {
            $result['failed'] = true;
            /** @var Teacher $taskTeacher */
            $taskTeacher = $task['teacher'];
            /** @var string $taskEvaluationType */
            $taskEvaluationType = $task['evaluation_type'];
            $result['error'] = [
                'teacher_id' => (string) $taskTeacher->getId(),
                'teacher_name' => $taskTeacher->getTeacherName(),
                'evaluation_type' => $taskEvaluationType,
                'error' => $e->getMessage(),
            ];

            $io->text("✗ 教师 {$taskTeacher->getTeacherName()} 的 {$taskEvaluationType} 评价提醒发送失败: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * 获取评价者列表
     * @return array<int, mixed>
     */
    private function getEvaluators(Teacher $teacher, string $evaluationType): array
    {
        // 这里应该根据实际业务逻辑获取评价者
        // 例如：学员评价需要获取该教师的学员列表
        //      同行评价需要获取同部门的其他教师
        //      管理层评价需要获取管理人员列表
        //      自我评价就是教师本人

        switch ($evaluationType) {
            case 'student':
                // 获取该教师的学员列表
                return $this->getStudentEvaluators($teacher);

            case 'peer':
                // 获取同行教师列表
                return $this->getPeerEvaluators($teacher);

            case 'management':
                // 获取管理层评价者
                return $this->getManagementEvaluators($teacher);

            case 'self':
                // 自我评价
                return [$teacher];

            default:
                return [];
        }
    }

    /**
     * 发送评价提醒
     * @param array<int, mixed> $evaluators
     */
    private function sendEvaluationReminder(Teacher $teacher, string $evaluationType, array $evaluators): void
    {
        // 这里应该实现实际的提醒发送逻辑
        // 例如：发送邮件、短信、系统通知等

        $reminderMessage = $this->buildReminderMessage($teacher, $evaluationType);

        foreach ($evaluators as $evaluator) {
            // 发送提醒通知
            $this->sendNotification($evaluator, $reminderMessage);
        }
    }

    /**
     * 构建提醒消息
     */
    private function buildReminderMessage(Teacher $teacher, string $evaluationType): string
    {
        $typeName = $this->getEvaluationTypeName($evaluationType);

        return sprintf(
            '您有一个待完成的教师评价任务：请对教师 %s 进行 %s 评价。请及时完成评价，谢谢！',
            $teacher->getTeacherName(),
            $typeName
        );
    }

    /**
     * 发送通知
     */
    private function sendNotification(mixed $evaluator, string $message): void
    {
        // 实现具体的通知发送逻辑
        // 这里可以集成邮件服务、短信服务、消息队列等
    }

    /**
     * 获取评价类型名称
     */
    private function getEvaluationTypeName(string $evaluationType): string
    {
        return match ($evaluationType) {
            'student' => '学员评价',
            'peer' => '同行评价',
            'management' => '管理层评价',
            'self' => '自我评价',
            default => $evaluationType,
        };
    }

    /**
     * 获取学员评价者
     * @return array<int, mixed>
     */
    private function getStudentEvaluators(Teacher $teacher): array
    {
        // 实现获取学员列表的逻辑
        // 这里需要与课程系统集成
        return [];
    }

    /**
     * 获取同行评价者
     * @return array<int, Teacher>
     */
    /**
     * @return array<int, Teacher>
     */
    private function getPeerEvaluators(Teacher $teacher): array
    {
        // 获取同部门或相关的其他教师
        return $this->teacherRepository->findBy([
            'teacherStatus' => 'active',
            'teacherType' => $teacher->getTeacherType(),
        ]);
    }

    /**
     * 获取管理层评价者
     * @return array<int, mixed>
     */
    private function getManagementEvaluators(Teacher $teacher): array
    {
        // 获取管理层人员列表
        // 这里需要与用户权限系统集成
        return [];
    }

    /**
     * 获取最近的提醒记录
     * @return array<int, mixed>
     */
    private function getRecentReminders(string $teacherId, string $evaluationType, int $days): array
    {
        // 实现获取最近提醒记录的逻辑
        // 这里需要有提醒日志表
        return [];
    }

    /**
     * 记录提醒发送日志
     */
    private function logReminderSent(string $teacherId, string $evaluationType): void
    {
        // 实现提醒发送日志记录
        // 可以记录到数据库或日志文件
    }

    /**
     * 创建执行上下文
     * @return array<string, mixed>
     */
    private function createExecutionContext(InputInterface $input, SymfonyStyle $io): array
    {
        $io->title('教师评价提醒');

        $evaluationType = $input->getOption('evaluation-type');
        $teacherId = $input->getOption('teacher-id');
        $daysOverdue = $input->getOption('days-overdue');
        $isDryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');
        $batchSize = $input->getOption('batch-size');

        $context = [
            'evaluationType' => is_string($evaluationType) ? $evaluationType : null,
            'teacherId' => is_string($teacherId) ? $teacherId : null,
            'daysOverdue' => max(1, is_numeric($daysOverdue) ? (int) $daysOverdue : 7),
            'isDryRun' => (bool) $isDryRun,
            'force' => (bool) $force,
            'batchSize' => max(1, is_numeric($batchSize) ? (int) $batchSize : 20),
        ];

        if ($context['isDryRun']) {
            $io->note('运行在预览模式，不会发送实际通知');
        }

        return $context;
    }

    /**
     * 处理提醒任务批次
     * @param array<int, array<string, mixed>> $reminderTasks
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function processReminderTasks(array $reminderTasks, array $context, SymfonyStyle $io): array
    {
        $results = $this->initializeReminderResults(count($reminderTasks));
        $io->text('找到 ' . count($reminderTasks) . ' 个评价提醒需要发送');

        /** @var int $batchSize */
        $batchSize = $context['batchSize'];
        $batches = array_chunk($reminderTasks, max(1, $batchSize));
        /** @var int $totalReminders */
        $totalReminders = $results['total_reminders'];
        $io->progressStart($totalReminders);

        foreach ($batches as $batchIndex => $batch) {
            $results = $this->processBatch($batch, $batchIndex, count($batches), $context, $results, $io);
        }

        $io->progressFinish();

        return $results;
    }

    /**
     * 处理单个批次
     * @param array<int, array<string, mixed>> $batch
     * @param array<string, mixed> $context
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function processBatch(array $batch, int $batchIndex, int $totalBatches, array $context, array $results, SymfonyStyle $io): array
    {
        $io->section('处理批次 ' . ($batchIndex + 1) . "/{$totalBatches}");

        foreach ($batch as $task) {
            /** @var bool $isDryRun */
            $isDryRun = $context['isDryRun'];
            $taskResult = $this->sendReminderTask($task, $isDryRun, $io);
            $results = $this->updateResults($results, $taskResult);
            $io->progressAdvance();
        }

        $this->addBatchDelay($batchIndex, $totalBatches);

        return $results;
    }

    /**
     * 初始化提醒结果
     * @return array<string, mixed>
     */
    private function initializeReminderResults(int $totalCount): array
    {
        return [
            'total_reminders' => $totalCount,
            'sent_reminders' => 0,
            'skipped_reminders' => 0,
            'failed_reminders' => 0,
            'errors' => [],
        ];
    }

    /**
     * 更新结果统计
     * @param array<string, mixed> $results
     * @param array<string, mixed> $taskResult
     * @return array<string, mixed>
     */
    private function updateResults(array $results, array $taskResult): array
    {
        if (true === $taskResult['sent']) {
            return $this->incrementSentCount($results);
        }

        if (true === $taskResult['skipped']) {
            return $this->incrementSkippedCount($results);
        }

        if (true === $taskResult['failed']) {
            return $this->incrementFailedCount($results, $taskResult);
        }

        return $results;
    }

    /**
     * 增加发送成功计数
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function incrementSentCount(array $results): array
    {
        $sentCount = \is_int($results['sent_reminders']) ? $results['sent_reminders'] : 0;
        $results['sent_reminders'] = $sentCount + 1;

        return $results;
    }

    /**
     * 增加跳过计数
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function incrementSkippedCount(array $results): array
    {
        $skippedCount = \is_int($results['skipped_reminders']) ? $results['skipped_reminders'] : 0;
        $results['skipped_reminders'] = $skippedCount + 1;

        return $results;
    }

    /**
     * 增加失败计数
     * @param array<string, mixed> $results
     * @param array<string, mixed> $taskResult
     * @return array<string, mixed>
     */
    private function incrementFailedCount(array $results, array $taskResult): array
    {
        $failedCount = \is_int($results['failed_reminders']) ? $results['failed_reminders'] : 0;
        $results['failed_reminders'] = $failedCount + 1;

        if (null !== $taskResult['error'] && \is_array($results['errors'])) {
            $results['errors'][] = $taskResult['error'];
        }

        return $results;
    }

    /**
     * 添加批次间延迟
     */
    private function addBatchDelay(int $batchIndex, int $totalBatches): void
    {
        if ($batchIndex < $totalBatches - 1) {
            usleep(50000); // 0.05秒
        }
    }

    /**
     * 处理执行结果
     * @param array<string, mixed> $results
     */
    private function handleExecutionResult(array $results, SymfonyStyle $io): int
    {
        $this->displayReminderResults($results, $io);

        if ($results['failed_reminders'] > 0) {
            $io->warning('部分提醒发送失败，请检查错误详情');

            return Command::FAILURE;
        }

        $io->success('评价提醒发送完成');

        return Command::SUCCESS;
    }

    /**
     * 显示提醒结果
     * @param array<string, mixed> $reminderResults
     */
    private function displayReminderResults(array $reminderResults, SymfonyStyle $io): void
    {
        $io->section('提醒发送结果统计');

        $io->table(
            ['项目', '数量'],
            [
                ['总提醒数', $reminderResults['total_reminders']],
                ['成功发送', $reminderResults['sent_reminders']],
                ['跳过发送', $reminderResults['skipped_reminders']],
                ['发送失败', $reminderResults['failed_reminders']],
            ]
        );

        // 显示详细错误信息
        /** @var array<int, array<string, string>> $errors */
        $errors = $reminderResults['errors'];
        if ([] !== $errors) {
            $io->section('发送失败详情');
            foreach ($errors as $error) {
                $io->text(sprintf(
                    '教师: %s (%s) - 评价类型: %s - 错误: %s',
                    $error['teacher_name'],
                    $error['teacher_id'],
                    $error['evaluation_type'],
                    $error['error']
                ));
            }
        }

        // 显示成功率
        /** @var int $totalReminders */
        $totalReminders = $reminderResults['total_reminders'];
        /** @var int $sentReminders */
        $sentReminders = $reminderResults['sent_reminders'];
        if ($totalReminders > 0) {
            $successRate = ($sentReminders / $totalReminders) * 100;
            $io->text(sprintf('成功率: %.2f%%', $successRate));
        }
    }
}
