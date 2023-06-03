<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setup\RoleRequest;
use App\Http\Requests\Setup\UpdateRoleRequest;
use App\Http\Resources\Setup\RoleResource;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function index()
    {
        // $roles = Role::with('permissions')->get();
        $roles = Role::all();
        return RoleResource::collection($roles);
    }

    public function store(RoleRequest $request)
    {
        $role = Role::create([
            'name' => $request->input('name')
        ]);
        $permissions = Permission::whereIn('id', $request->input('permissions'))->get();
        $role -> syncPermissions($permissions);

        return response(new RoleResource($role), Response::HTTP_CREATED); 
    }

    public function show(string $id)
    {
        $role = Role::findOrFail($id);
        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, string $id)
    {
        $role = Role::findOrFail($id);
        $role -> update($request->only('name'));
        $role -> syncPermissions(Permission::whereIn('id', $request->input('permissions', []))->get());
        return response(new RoleResource($role), Response::HTTP_ACCEPTED);
    }
    public function destroy(string $id)
    {
        $rol = Role::findOrFail($id);
        if ($rol->users()->exists()) {
            return response()->json(['message' => 'No se puede eliminar el rol porque tiene usuarios asignados.'], 403);
        }
        $rol->delete();
        return response()->json(['message' => 'El rol se ha eliminado exitosamente.'], 200);
    }
}
