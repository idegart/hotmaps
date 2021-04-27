<?php

namespace HotMaps\Generators;

use HotMaps\Models\User;

class UserGenerator
{
    public static function fromJson(string $jsonData): User
    {
        $arrayData = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($arrayData);
    }

    public static function fromArray(array $arrayData): User
    {
        $user = new User();

        if (isset($arrayData['active'])) {
            $user->setActive($arrayData['active']);
        }

        if (isset($arrayData['blocked'])) {
            $user->setBlocked($arrayData['blocked']);
        }

        if (isset($arrayData['created_at'])) {
            $user->setCreatedAt($arrayData['created_at']);
        }

        if (isset($arrayData['id'])) {
            $user->setId($arrayData['id']);
        }

        if (isset($arrayData['name'])) {
            $user->setName($arrayData['name']);
        }

        if (isset($arrayData['permissions'])) {
            foreach ($arrayData['permissions'] as $permissionData) {
                $user->addPermission(PermissionGenerator::fromArray($permissionData));
            }
        }

        return $user;
    }

    public static function toJson(User $user): string
    {
        return json_encode(self::toArray($user), JSON_THROW_ON_ERROR);
    }

    public static function toArray(User $user): array
    {
        $permissions = [];

        foreach ($user->getPermissions() as $permission) {
            $permissions[] = PermissionGenerator::toArray($permission);
        }

        return [
            'id' => $user->getId(),
            'active' => $user->getActive(),
            'blocked' => $user->isBlocked(),
            'created_at' => $user->getCreatedAt(),
            'name' => $user->getName(),
            'permissions' => $permissions,
        ];
    }
}