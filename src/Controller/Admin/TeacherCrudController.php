<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TrainTeacherBundle\Entity\Teacher;

/**
 * 教师管理控制器
 * @extends AbstractCrudController<Teacher>
 */
#[AdminCrud(routePath: '/train/teacher', routeName: 'train_teacher')]
final class TeacherCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Teacher::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('教师')
            ->setEntityLabelInPlural('教师管理')
            ->setSearchFields(['teacherCode', 'teacherName', 'phone', 'email', 'idCard'])
            ->setDefaultSort(['joinDate' => 'DESC'])
            ->setPaginatorPageSize(30)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('teacherCode', '教师编号'))
            ->add(TextFilter::new('teacherName', '教师姓名'))
            ->add(ChoiceFilter::new('teacherType', '教师类型')
                ->setChoices([
                    '专职' => '专职',
                    '兼职' => '兼职',
                ]))
            ->add(ChoiceFilter::new('gender', '性别')
                ->setChoices([
                    '男' => '男',
                    '女' => '女',
                ]))
            ->add(ChoiceFilter::new('education', '学历')
                ->setChoices([
                    '高中' => '高中',
                    '大专' => '大专',
                    '本科' => '本科',
                    '硕士' => '硕士',
                    '博士' => '博士',
                ]))
            ->add(ChoiceFilter::new('teacherStatus', '教师状态')
                ->setChoices([
                    '在职' => '在职',
                    '离职' => '离职',
                    '休假' => '休假',
                    '停职' => '停职',
                ]))
            ->add(BooleanFilter::new('isAnonymous', '是否匿名'))
            ->add(DateTimeFilter::new('joinDate', '入职日期'))
            ->add(DateTimeFilter::new('lastActiveTime', '最后活跃时间'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->hideOnForm()
                ->hideOnIndex(),

            TextField::new('teacherCode', '教师编号')
                ->setRequired(true)
                ->setMaxLength(32)
                ->setHelp('教师的唯一编号')
                ->setColumns(6),

            TextField::new('teacherName', '教师姓名')
                ->setRequired(true)
                ->setMaxLength(50)
                ->setColumns(6),

            ChoiceField::new('teacherType', '教师类型')
                ->setRequired(true)
                ->setChoices([
                    '专职' => '专职',
                    '兼职' => '兼职',
                ])
                ->setColumns(6),

            ChoiceField::new('gender', '性别')
                ->setRequired(true)
                ->setChoices([
                    '男' => '男',
                    '女' => '女',
                ])
                ->setColumns(6),

            DateField::new('birthDate', '出生日期')
                ->setRequired(true)
                ->setFormat('yyyy-MM-dd')
                ->setColumns(6)
                ->hideOnIndex(),

            TextField::new('idCard', '身份证号')
                ->setRequired(true)
                ->setMaxLength(18)
                ->setColumns(6)
                ->hideOnIndex(),

            TextField::new('phone', '联系电话')
                ->setRequired(true)
                ->setMaxLength(20)
                ->setColumns(6),

            EmailField::new('email', '邮箱')
                ->setRequired(false)
                ->setColumns(6)
                ->hideOnIndex(),

            TextareaField::new('address', '地址')
                ->setRequired(false)
                ->setMaxLength(500)
                ->setNumOfRows(3)
                ->hideOnIndex(),

            ChoiceField::new('education', '学历')
                ->setRequired(true)
                ->setChoices([
                    '高中' => '高中',
                    '大专' => '大专',
                    '本科' => '本科',
                    '硕士' => '硕士',
                    '博士' => '博士',
                ])
                ->setColumns(6),

            TextField::new('major', '专业')
                ->setRequired(true)
                ->setMaxLength(100)
                ->setColumns(6)
                ->hideOnIndex(),

            TextField::new('graduateSchool', '毕业院校')
                ->setRequired(true)
                ->setMaxLength(100)
                ->setColumns(6)
                ->hideOnIndex(),

            DateField::new('graduateDate', '毕业日期')
                ->setRequired(true)
                ->setFormat('yyyy-MM-dd')
                ->setColumns(6)
                ->hideOnIndex(),

            IntegerField::new('workExperience', '工作经验（年）')
                ->setRequired(true)
                ->setHelp('工作年限')
                ->setFormTypeOptions(['attr' => ['min' => 0]])
                ->setColumns(6),

            ArrayField::new('specialties', '专业特长')
                ->setRequired(false)
                ->setHelp('请输入专业特长，每行一个特长')
                ->hideOnIndex(),

            ChoiceField::new('teacherStatus', '教师状态')
                ->setRequired(true)
                ->setChoices([
                    '在职' => '在职',
                    '离职' => '离职',
                    '休假' => '休假',
                    '停职' => '停职',
                ])
                ->setColumns(6),

            UrlField::new('profilePhoto', '头像')
                ->setRequired(false)
                ->setHelp('头像图片URL地址')
                ->hideOnIndex(),

            DateField::new('joinDate', '入职日期')
                ->setRequired(true)
                ->setFormat('yyyy-MM-dd')
                ->setColumns(6),

            DateTimeField::new('lastActiveTime', '最后活跃时间')
                ->setRequired(false)
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->hideOnForm()
                ->hideOnIndex(),

            BooleanField::new('isAnonymous', '是否匿名')
                ->setRequired(false)
                ->setHelp('是否为匿名教师')
                ->setColumns(6),

            DateTimeField::new('createdAt', '创建时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->hideOnIndex(),

            DateTimeField::new('updatedAt', '更新时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->hideOnIndex(),
        ];
    }
}
