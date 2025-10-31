<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;

/**
 * 教师绩效管理控制器
 * @extends AbstractCrudController<TeacherPerformance>
 */
#[AdminCrud(
    routePath: '/train/teacher-performance',
    routeName: 'train_teacher_performance'
)]
final class TeacherPerformanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TeacherPerformance::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('教师绩效')
            ->setEntityLabelInPlural('教师绩效管理')
            ->setPageTitle(Crud::PAGE_INDEX, '绩效列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建绩效')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑绩效')
            ->setPageTitle(Crud::PAGE_DETAIL, '绩效详情')
            ->setDefaultSort(['performancePeriod' => 'DESC'])
            ->setSearchFields(['teacher.teacherName', 'performanceLevel', 'remarks'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $performanceLevelChoices = [
            '优秀' => '优秀',
            '良好' => '良好',
            '合格' => '合格',
            '待改进' => '待改进',
            '不合格' => '不合格',
        ];

        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('teacher', '教师')
            ->setFormTypeOption('choice_label', 'teacherName')
            ->setFormTypeOption('placeholder', '请选择教师')
            ->setRequired(true)
        ;

        yield DateField::new('performancePeriod', '绩效周期')
            ->setFormTypeOption('widget', 'single_text')
            ->setRequired(true)
        ;

        yield NumberField::new('averageEvaluation', '平均评价分数')
            ->setNumDecimals(1)
            ->setHelp('范围：0-10分')
        ;

        yield ArrayField::new('performanceMetrics', '绩效指标')
            ->onlyOnDetail()
            ->setHelp('JSON格式的绩效指标数据')
        ;

        yield NumberField::new('performanceScore', '绩效分数')
            ->setNumDecimals(2)
            ->setHelp('范围：0-100分')
        ;

        yield ChoiceField::new('performanceLevel', '绩效等级')
            ->setChoices($performanceLevelChoices)
            ->renderAsBadges([
                '优秀' => 'success',
                '良好' => 'info',
                '合格' => 'primary',
                '待改进' => 'warning',
                '不合格' => 'danger',
            ])
            ->setRequired(true)
        ;

        yield NumberField::new('totalCourses', '总课程数')
            ->hideOnIndex()
            ->setHelp('负责的课程总数')
        ;

        yield NumberField::new('totalHours', '总课时数')
            ->hideOnIndex()
            ->setHelp('授课总课时')
        ;

        yield NumberField::new('studentCount', '学生总数')
            ->hideOnIndex()
            ->setHelp('教授的学生总数')
        ;

        yield NumberField::new('averageScore', '平均分数')
            ->setNumDecimals(2)
            ->hideOnIndex()
            ->setHelp('学生平均成绩')
        ;

        yield PercentField::new('completionRate', '完成率')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setHelp('课程完成率百分比')
        ;

        yield PercentField::new('satisfactionRate', '满意度')
            ->setNumDecimals(2)
            ->setStoredAsFractional(false)
            ->setHelp('学生满意度百分比')
        ;

        yield TextareaField::new('remarks', '备注')
            ->hideOnIndex()
            ->setNumOfRows(3)
        ;

        yield ArrayField::new('achievements', '成就')
            ->onlyOnDetail()
            ->setHelp('获得的成就和奖励')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('teacher', '教师'))
            ->add(ChoiceFilter::new('performanceLevel', '绩效等级')->setChoices([
                '优秀' => '优秀',
                '良好' => '良好',
                '合格' => '合格',
                '待改进' => '待改进',
                '不合格' => '不合格',
            ]))
            ->add(DateTimeFilter::new('performancePeriod', '绩效周期'))
            ->add(NumericFilter::new('performanceScore', '绩效分数'))
            ->add(NumericFilter::new('averageEvaluation', '平均评价分数'))
            ->add(NumericFilter::new('completionRate', '完成率'))
            ->add(NumericFilter::new('satisfactionRate', '满意度'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
