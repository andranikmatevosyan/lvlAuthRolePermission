<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Spatie\Permission\Models\Permission;

class Can
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
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
            return $next($request);
        }

        abort(403, 'Unauthorized action.');

    }
}
