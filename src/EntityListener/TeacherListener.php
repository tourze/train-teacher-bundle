<?php

namespace Tourze\TrainTeacherBundle\EntityListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Tourze\TrainTeacherBundle\Entity\Teacher;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Teacher::class)]
class TeacherListener
{
    public function preUpdate(Teacher $teacher): void
    {
        $teacher->setUpdateTime(new \DateTimeImmutable());
    }
}