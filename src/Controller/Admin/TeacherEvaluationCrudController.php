<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;

/**
 * 教师评价管理控制器
 * @extends AbstractCrudController<TeacherEvaluation>
 */
#[AdminCrud(routePath: '/train/teacher-evaluation', routeName: 'train_teacher_evaluation')]
final class TeacherEvaluationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TeacherEvaluation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('教师评价')
            ->setEntityLabelInPlural('教师评价管理')
            ->setPageTitle(Crud::PAGE_INDEX, '教师评价列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建教师评价')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑教师评价')
            ->setPageTitle(Crud::PAGE_DETAIL, '教师评价详情')
            ->setDefaultSort(['evaluationDate' => 'DESC', 'createTime' => 'DESC'])
            ->setSearchFields(['evaluatorId', 'evaluationType', 'evaluationComments'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // 评价者类型选项
        $evaluatorTypeChoices = [
            '学员' => 'student',
            '同行' => 'peer',
            '管理层' => 'manager',
            '自我评价' => 'self',
        ];

        // 评价状态选项
        $evaluationStatusChoices = [
            '草稿' => 'draft',
            '已提交' => 'submitted',
            '已审核' => 'reviewed',
            '已完成' => 'completed',
        ];

        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('teacher', '教师')
            ->setRequired(true)
            ->formatValue(function ($value, $entity) {
                if ($value instanceof Teacher) {
                    $teacherName = $value->getTeacherName();

                    return '' !== $teacherName ? $teacherName : $value->getId();
                }

                return $value;
            })
            ->setHelp('选择被评价的教师')
        ;

        yield ChoiceField::new('evaluatorType', '评价者类型')
            ->setChoices($evaluatorTypeChoices)
            ->setRequired(true)
            ->renderAsBadges([
                'student' => 'primary',
                'peer' => 'info',
                'manager' => 'warning',
                'self' => 'secondary',
            ])
            ->setHelp('选择评价者的类型')
        ;

        yield TextField::new('evaluatorId', '评价者ID')
            ->setRequired(true)
            ->setMaxLength(36)
            ->setHelp('评价者的唯一标识符')
            ->hideOnIndex()
        ;

        yield TextField::new('evaluationType', '评价类型')
            ->setRequired(true)
            ->setMaxLength(50)
            ->setHelp('例如：课程评价、年度评价、同行评议等')
        ;

        yield DateField::new('evaluationDate', '评价日期')
            ->setRequired(true)
            ->setHelp('评价进行的日期')
        ;

        yield NumberField::new('overallScore', '总体评分')
            ->setRequired(true)
            ->setNumDecimals(1)
            ->setHelp('总体评分，范围0-10分')
            ->setFormTypeOptions([
                'attr' => [
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                ],
            ])
        ;

        // 仅在表单页面显示的复杂字段
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield ArrayField::new('evaluationItems', '评价项目')
                ->setHelp('评价的具体项目列表，JSON格式')
                ->hideOnIndex()
            ;

            yield ArrayField::new('evaluationScores', '评价分数')
                ->setHelp('各项目的具体分数，JSON格式')
                ->hideOnIndex()
            ;

            yield ArrayField::new('suggestions', '建议')
                ->setHelp('改进建议列表，JSON格式')
                ->hideOnIndex()
            ;
        }

        yield TextareaField::new('evaluationComments', '评价意见')
            ->setMaxLength(2000)
            ->setNumOfRows(4)
            ->setHelp('详细的评价意见和反馈')
            ->hideOnIndex()
        ;

        yield BooleanField::new('isAnonymous', '匿名评价')
            ->setHelp('是否为匿名评价')
            ->renderAsSwitch()
        ;

        yield ChoiceField::new('evaluationStatus', '评价状态')
            ->setChoices($evaluationStatusChoices)
            ->setRequired(true)
            ->renderAsBadges([
                'draft' => 'secondary',
                'submitted' => 'info',
                'reviewed' => 'warning',
                'completed' => 'success',
            ])
            ->setHelp('评价的当前状态')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        // 详情页面显示更多信息
        if (Crud::PAGE_DETAIL === $pageName) {
            yield ArrayField::new('evaluationItems', '评价项目')
                ->setHelp('评价的具体项目列表')
            ;

            yield ArrayField::new('evaluationScores', '评价分数')
                ->setHelp('各项目的具体分数')
            ;

            yield ArrayField::new('suggestions', '建议')
                ->setHelp('改进建议列表')
            ;
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('teacher', '教师'))
            ->add(ChoiceFilter::new('evaluatorType', '评价者类型')->setChoices([
                '学员' => 'student',
                '同行' => 'peer',
                '管理层' => 'manager',
                '自我评价' => 'self',
            ]))
            ->add('evaluationType')
            ->add('evaluationDate')
            ->add(NumericFilter::new('overallScore', '总体评分'))
            ->add(BooleanFilter::new('isAnonymous', '匿名评价'))
            ->add(ChoiceFilter::new('evaluationStatus', '评价状态')->setChoices([
                '草稿' => 'draft',
                '已提交' => 'submitted',
                '已审核' => 'reviewed',
                '已完成' => 'completed',
            ]))
            ->add('createTime')
        ;
    }
}
