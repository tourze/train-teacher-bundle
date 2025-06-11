<?php

namespace Tourze\TrainTeacherBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\EvaluationService;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * 教师评价提醒命令
 * 用于发送评价提醒通知，提醒相关人员进行教师评价
 */
#[AsCommand(
    name: 'teacher:evaluation:reminder',
    description: '发送教师评价提醒通知'
)]
class EvaluationReminderCommand extends Command
{
    public function __construct(
        private readonly EvaluationService $evaluationService,
        private readonly TeacherService $teacherService,
        private readonly TeacherRepository $teacherRepository,
        private readonly TeacherEvaluationRepository $evaluationRepository
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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // 解析参数
        $evaluationType = $input->getOption('evaluation-type');
        $teacherId = $input->getOption('teacher-id');
        $daysOverdue = (int) $input->getOption('days-overdue');
        $isDryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');
        $batchSize = (int) $input->getOption('batch-size');

        $io->title('教师评价提醒');

        if ($isDryRun) {
            $io->note('运行在预览模式，不会发送实际通知');
        }

        $reminderResults = [
            'total_reminders' => 0,
            'sent_reminders' => 0,
            'skipped_reminders' => 0,
            'failed_reminders' => 0,
            'errors' => []
        ];

        try {
            // 获取需要发送提醒的评价任务
            $reminderTasks = $this->getReminderTasks($teacherId, $evaluationType, $daysOverdue, $force);
            $reminderResults['total_reminders'] = count($reminderTasks);

            if (empty($reminderTasks)) {
                $io->success('没有需要发送的评价提醒');
                return Command::SUCCESS;
            }

            $io->text('找到 ' . count($reminderTasks) . ' 个评价提醒需要发送');

            // 分批处理提醒发送
            $batches = array_chunk($reminderTasks, $batchSize);
            $totalBatches = count($batches);

            $io->progressStart($reminderResults['total_reminders']);

            foreach ($batches as $batchIndex => $batch) {
                $io->section("处理批次 " . ($batchIndex + 1) . "/$totalBatches");
                
                foreach ($batch as $task) {
                    $this->sendReminderTask(
                        $task,
                        $isDryRun,
                        $reminderResults,
                        $io
                    );
                    $io->progressAdvance();
                }

                // 批次间短暂休息
                if ($batchIndex < $totalBatches - 1) {
                    usleep(50000); // 0.05秒
                }
            }

            $io->progressFinish();

            // 显示发送结果
            $this->displayReminderResults($reminderResults, $io);

            if ($reminderResults['failed_reminders'] > 0) {
                $io->warning('部分提醒发送失败，请检查错误详情');
                return Command::FAILURE;
            }

            $io->success('评价提醒发送完成');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error('评价提醒发送失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 获取需要发送提醒的任务列表
     */
    private function getReminderTasks(?string $teacherId, ?string $evaluationType, int $daysOverdue, bool $force): array
    {
        $tasks = [];
        $currentDate = new \DateTime();
        $overdueDate = (clone $currentDate)->modify("-{$daysOverdue} days");

        // 获取目标教师列表
        if ($teacherId) {
            try {
                $teachers = [$this->teacherService->getTeacherById($teacherId)];
            } catch (\Throwable $e) {
                return [];
            }
        } else {
            $teachers = $this->teacherRepository->findBy(['teacherStatus' => 'active']);
        }

        foreach ($teachers as $teacher) {
            // 检查各种类型的评价提醒
            $evaluationTypes = $evaluationType ? [$evaluationType] : ['student', 'peer', 'management', 'self'];
            
            foreach ($evaluationTypes as $type) {
                $reminderTask = $this->checkEvaluationReminder($teacher, $type, $overdueDate, $force);
                if ($reminderTask) {
                    $tasks[] = $reminderTask;
                }
            }
        }

        return $tasks;
    }

    /**
     * 检查单个评价提醒
     */
    private function checkEvaluationReminder($teacher, string $evaluationType, \DateTime $overdueDate, bool $force): ?array
    {
        // 获取最近的评价记录
        $recentEvaluations = $this->evaluationRepository->findRecentEvaluations(
            $teacher,
            $evaluationType,
            30 // 最近30天
        );

        // 检查是否需要发送提醒
        $needsReminder = false;
        $lastEvaluationDate = null;

        if (empty($recentEvaluations)) {
            // 没有评价记录，需要提醒
            $needsReminder = true;
        } else {
            // 检查最后评价时间
            $lastEvaluation = $recentEvaluations[0];
            $lastEvaluationDate = $lastEvaluation->getEvaluationDate();
            
            if ($lastEvaluationDate < $overdueDate) {
                $needsReminder = true;
            }
        }

        // 检查是否最近已发送过提醒（除非强制发送）
        if ($needsReminder && !$force) {
            $recentReminders = $this->getRecentReminders($teacher->getId(), $evaluationType, 3); // 最近3天
            if (!empty($recentReminders)) {
                $needsReminder = false;
            }
        }

        if (!$needsReminder) {
            return null;
        }

        return [
            'teacher' => $teacher,
            'evaluation_type' => $evaluationType,
            'last_evaluation_date' => $lastEvaluationDate,
            'days_overdue' => $lastEvaluationDate ? 
                $lastEvaluationDate->diff(new \DateTime())->days : 
                null,
            'evaluators' => $this->getEvaluators($teacher, $evaluationType)
        ];
    }

    /**
     * 发送单个提醒任务
     */
    private function sendReminderTask(
        array $task,
        bool $isDryRun,
        array &$reminderResults,
        SymfonyStyle $io
    ): void {
        try {
            $teacher = $task['teacher'];
            $evaluationType = $task['evaluation_type'];
            $evaluators = $task['evaluators'];

            if (empty($evaluators)) {
                $reminderResults['skipped_reminders']++;
                $io->text("跳过教师 {$teacher->getTeacherName()} 的 {$evaluationType} 评价提醒 (无评价者)");
                return;
            }

            if (!$isDryRun) {
                // 发送实际提醒
                $this->sendEvaluationReminder($teacher, $evaluationType, $evaluators);
                
                // 记录提醒发送日志
                $this->logReminderSent($teacher->getId(), $evaluationType);
            }

            $reminderResults['sent_reminders']++;
            $io->text(sprintf(
                "✓ 已发送教师 %s 的 %s 评价提醒给 %d 个评价者",
                $teacher->getTeacherName(),
                $this->getEvaluationTypeName($evaluationType),
                count($evaluators)
            ));

        } catch (\Throwable $e) {
            $reminderResults['failed_reminders']++;
            $reminderResults['errors'][] = [
                'teacher_id' => $task['teacher']->getId(),
                'teacher_name' => $task['teacher']->getTeacherName(),
                'evaluation_type' => $task['evaluation_type'],
                'error' => $e->getMessage()
            ];
            
            $io->text("✗ 教师 {$task['teacher']->getTeacherName()} 的 {$task['evaluation_type']} 评价提醒发送失败: " . $e->getMessage());
        }
    }

    /**
     * 获取评价者列表
     */
    private function getEvaluators($teacher, string $evaluationType): array
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
     */
    private function sendEvaluationReminder($teacher, string $evaluationType, array $evaluators): void
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
    private function buildReminderMessage($teacher, string $evaluationType): string
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
    private function sendNotification($evaluator, string $message): void
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
            default => $evaluationType
        };
    }

    /**
     * 获取学员评价者
     */
    private function getStudentEvaluators($teacher): array
    {
        // 实现获取学员列表的逻辑
        // 这里需要与课程系统集成
        return [];
    }

    /**
     * 获取同行评价者
     */
    private function getPeerEvaluators($teacher): array
    {
        // 获取同部门或相关的其他教师
        return $this->teacherRepository->findBy([
            'teacherStatus' => 'active',
            'teacherType' => $teacher->getTeacherType()
        ]);
    }

    /**
     * 获取管理层评价者
     */
    private function getManagementEvaluators($teacher): array
    {
        // 获取管理层人员列表
        // 这里需要与用户权限系统集成
        return [];
    }

    /**
     * 获取最近的提醒记录
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
     * 显示提醒结果
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
                ['发送失败', $reminderResults['failed_reminders']]
            ]
        );

        // 显示详细错误信息
        if (!empty($reminderResults['errors'])) {
            $io->section('发送失败详情');
            foreach ($reminderResults['errors'] as $error) {
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
        if ($reminderResults['total_reminders'] > 0) {
            $successRate = ($reminderResults['sent_reminders'] / $reminderResults['total_reminders']) * 100;
            $io->text(sprintf('成功率: %.2f%%', $successRate));
        }
    }
} 