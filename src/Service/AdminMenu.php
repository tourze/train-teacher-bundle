<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;

/**
 * 教师管理后台菜单提供者
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('培训管理')) {
            $item->addChild('培训管理');
        }

        $trainMenu = $item->getChild('培训管理');
        if (null === $trainMenu) {
            return;
        }

        // 添加教师管理子菜单
        if (null === $trainMenu->getChild('教师管理')) {
            $trainMenu->addChild('教师管理')
                ->setAttribute('icon', 'fas fa-chalkboard-teacher')
            ;
        }

        $teacherMenu = $trainMenu->getChild('教师管理');
        if (null === $teacherMenu) {
            return;
        }

        $teacherMenu->addChild('教师管理')
            ->setUri($this->linkGenerator->getCurdListPage(Teacher::class))
            ->setAttribute('icon', 'fas fa-user-tie')
        ;

        $teacherMenu->addChild('教师评价')
            ->setUri($this->linkGenerator->getCurdListPage(TeacherEvaluation::class))
            ->setAttribute('icon', 'fas fa-star')
        ;

        $teacherMenu->addChild('教师绩效')
            ->setUri($this->linkGenerator->getCurdListPage(TeacherPerformance::class))
            ->setAttribute('icon', 'fas fa-chart-line')
        ;
    }
}
