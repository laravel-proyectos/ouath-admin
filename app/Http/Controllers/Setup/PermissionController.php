<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setup\PermissionRequest;
use App\Http\Requests\Setup\UpdatePermissionRequest;
use App\Http\Resources\Setup\PermissionResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return PermissionResource::collection($permissions);
    }

    public function store(PermissionRequest $request)
    {
        $permission = Permission::create([
            'name' => $request->input('name')
        ]);

        return response(new PermissionResource($permission), Response::HTTP_CREATED); 
    }


    public function show(string $id)
    {
        $permission = Permission::findOrFail($id);
        return new PermissionResource($permission);
    }

    public function update(UpdatePermissionRequest $request, string $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->name = $request->validated()['name'];
        $permission->save();
        return response(new PermissionResource($permission), Response::HTTP_ACCEPTED);
    }

    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);
        if ($permission->roles()->count() > 0) {
            return response()->json(['message' => 'No se puede eliminar el permiso, está asignado a uno o más roles.'], 422);
        }
        $permission->delete();
        return response()->json(['message' => 'Permiso eliminado correctamente.']);
    }
}
