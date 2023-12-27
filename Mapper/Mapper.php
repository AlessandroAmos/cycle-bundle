<?php

namespace Alms\Bundle\CycleBundle\Mapper;

use Cycle\ORM\ORMInterface;

class Mapper extends \Cycle\ORM\Mapper\Mapper
{
    public function __construct(ORMInterface $orm, CachedProxyEntityFactory $entityFactory, string $role)
    {
        parent::__construct($orm, $entityFactory, $role);
    }
}