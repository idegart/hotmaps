<?php

namespace HotMaps\Generators;

use HotMaps\Models\Permission;

class PermissionGenerator
{
    public static function fromArray(array $arrayData): Permission
    {
        $permission = new Permission();

        if (isset($arrayData['id'])) {
            $permission->setId($arrayData['id']);
        }

        if (isset($arrayData['permission'])) {
            $permission->setName($arrayData['permission']);
        }

        return $permission;
    }

    public static function toArray(Permission $permission): array
    {
        return [
            'id' => $permission->getId(),
            'permission' => $permission->getName(),
        ];
    }
}