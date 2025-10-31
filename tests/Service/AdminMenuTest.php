<?php

namespace Tourze\TrainTeacherBundle\Tests\Service;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\TrainTeacherBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private FactoryInterface $factory;

    protected function onSetUp(): void
    {
        // 从容器获取服务
        $this->adminMenu = self::getService(AdminMenu::class);
        $this->factory = new MenuFactory();
    }

    private function createBasicMenuItem(string $name = 'test'): ItemInterface
    {
        return new MenuItem($name, $this->factory);
    }

    private function createMenuItemWithTrainSubMenu(): ItemInterface
    {
        $mainItem = new MenuItem('main', $this->factory);
        $trainMenu = $mainItem->addChild('培训管理');

        return $mainItem;
    }

    public function testServiceCreation(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->adminMenu);
    }

    public function testInvokeShouldBeCallable(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->hasMethod('__invoke'));
    }

    public function testInvokeBasicFunctionality(): void
    {
        $this->expectNotToPerformAssertions();
        $item = $this->createBasicMenuItem();

        // 调用不应该抛出异常
        $this->adminMenu->__invoke($item);
    }

    public function testInvokeReturnsEarlyWhenTrainMenuIsNull(): void
    {
        $this->expectNotToPerformAssertions();
        $item = $this->createBasicMenuItem();

        // 当没有'培训管理'子菜单时，应该提前返回
        $this->adminMenu->__invoke($item);
    }

    public function testInvokeUsesExistingMenu(): void
    {
        $this->expectNotToPerformAssertions();
        $item = $this->createMenuItemWithTrainSubMenu();

        // 当存在'培训管理'子菜单时，应该正常处理
        $this->adminMenu->__invoke($item);
    }

    public function testInvokeAddsTeacherMenuItems(): void
    {
        $item = $this->createMenuItemWithTrainSubMenu();
        $trainMenu = $item->getChild('培训管理');

        // 执行菜单构建
        $this->adminMenu->__invoke($item);

        // 验证培训管理菜单存在
        $this->assertNotNull($trainMenu);
        $this->assertSame('培训管理', $trainMenu->getName());
    }
}
