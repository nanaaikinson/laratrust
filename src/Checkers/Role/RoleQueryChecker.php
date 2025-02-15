<?php

namespace Laratrust\Checkers\Role;

use Laratrust\Helper;

class RoleQueryChecker extends RoleChecker
{
    /**
     * Checks if the role has a permission by its name.
     */
    public function currentRoleHasPermission(string|array $permission, bool $requireAll = false):bool
    {
        if (empty($permission)) {
            return true;
        }

        $permission = Helper::standardize($permission);
        $permissionsNames = is_array($permission) ? $permission : [$permission];

        list($permissionsWildcard, $permissionsNoWildcard) =
            Helper::getPermissionWithAndWithoutWildcards($permissionsNames);

        $permissionsCount = $this->role->permissions()
            ->whereIn('name', $permissionsNoWildcard)
            ->when($permissionsWildcard, function ($query) use ($permissionsWildcard) {
                foreach ($permissionsWildcard as $permission) {
                    $query->orWhere('name', 'like', $permission);
                }

                return $query;
            })
            ->count();

        return $requireAll
            ? $permissionsCount >= count($permissionsNames)
            : $permissionsCount > 0;
    }

    /**
     * Flush the role's cache.
     */
    public function currentRoleFlushCache():void
    {
    }
}
