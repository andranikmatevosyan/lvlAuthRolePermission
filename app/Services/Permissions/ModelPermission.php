<?php

namespace App\Services\Permissions;

use DB;
use Spatie\Permission\Models\Permission;
use App\ItemPermission;
use App\User;

class ModelPermission
{
    public function handle($permission, $itemPermissions) {
        $authId = auth()->user()->id;
        $modelHasRoles = DB::table('model_has_roles')->where(['model_id' => $authId, 'model_type' => 'App\User'])->first();

        $roleHasPermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$modelHasRoles->role_id)
            ->get();
        $permissionsList = array();

        foreach($roleHasPermissions as $hasPermission) {
            $permissionsList[] = $hasPermission->name;
        }

        if(in_array($permission, $permissionsList)) {
            return true;
        }

        $roleHasItemPermissions = ItemPermission::join("role_has_item_permissions", "role_has_item_permissions.item_permission_id", "=", "item_permissions.id")
            ->where("role_has_item_permissions.role_id", $modelHasRoles->role_id)
            ->get();

        foreach($roleHasItemPermissions as $roleHasItemPermission) {
            if($roleHasItemPermission->type == $itemPermissions['type'] && $roleHasItemPermission->model_name == $itemPermissions['model_name'] && $roleHasItemPermission->model_id == $itemPermissions['model_id']) {
                return true;
            }
        }

        abort(403, 'Unauthorized action.');
    }

    public function authUsers($model, $modelId) {
        $roleHasPermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("permissions.name","project-list")
            ->orWhere("permissions.name","project-create")
            ->orWhere("permissions.name","project-edit")
            ->orWhere("permissions.name","project-delete")
            ->get();

        $roleList = array();

        foreach($roleHasPermissions as $roleHasPermission) {
            if(!in_array($roleHasPermission->role_id, $roleList)) {
                $roleList[] = $roleHasPermission->role_id;
            }
        }
        $usersWithRoles = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->where(['model_type' => 'App\User'])->whereIn('model_id', $roleList)->get();

        $userIds = [];
        foreach($usersWithRoles as $userWithRoles) {
            $userHasPermission = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
                ->where(["permissions.name" => "project-edit", "role_has_permissions.role_id" => $userWithRoles->role_id])
                ->first();

            $userIds[] = $userWithRoles->id;

            $userWithRoles->isAdmin = "";
            if($userHasPermission) {
                $userWithRoles->isAdmin = "Администратор";
            }
        }

        $roleHasItemPermissions = ItemPermission::join("role_has_item_permissions", "role_has_item_permissions.item_permission_id", "=", "item_permissions.id")
            ->where(["item_permissions.type" => 'show', "item_permissions.model_name" => $model, "item_permissions.model_id" => $modelId])
            ->orWhere(["item_permissions.type" => 'edit', "item_permissions.model_name" => $model, "item_permissions.model_id" => $modelId])
            ->orWhere(["item_permissions.type" => 'delete', "item_permissions.model_name" => $model, "item_permissions.model_id" => $modelId])
            ->get();

        $roleItemList = array();

        foreach($roleHasItemPermissions as $roleHasItemPermission) {
            if(!in_array($roleHasItemPermission->role_id, $roleItemList)) {
                $roleItemList[] = $roleHasItemPermission->role_id;
            }
        }


        $usersWithItemRoles = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')->where(['model_has_roles.model_type' => 'App\User'])->whereIn('model_has_roles.model_id', $roleItemList)->get();

        foreach($usersWithItemRoles as $userWithItemRoles) {
            $userHasItemPermission = ItemPermission::join("role_has_item_permissions","role_has_item_permissions.item_permission_id","=","item_permissions.id")
                ->where(["item_permissions.type" => "edit", "item_permissions.model_name" => $model, "item_permissions.model_id" => $modelId, "role_has_item_permissions.role_id" => $userWithItemRoles->role_id])
                ->first();

            $userWithItemRoles->isAdmin = "";
            if($userHasItemPermission) {
                $userWithItemRoles->isAdmin = "Администратор";

                if(in_array($userWithItemRoles->id, $userIds)) {
                    foreach($usersWithRoles as $userWithRoles) {
                        if($userWithRoles->id == $userWithItemRoles->id) {
                            $userWithRoles->isAdmin = "Администратор";
                        }
                    }
                }
            }
        }



        $users = array('usersWithRoles' => $usersWithRoles, 'userWithItemRoles' => $usersWithItemRoles);

        return $usersWithRoles;
    }
}