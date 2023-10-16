<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\role;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;



class RoleController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $with_responsibilities = $request->input('with_responsibilities', 0);
        $roleQuery = role::query();
        //NOTE - powerhuman.com/api/role?1d=1

        if ($id) {

            $role = $roleQuery->with('responsibilities')->find($id);

            if ($role) {
                return ResponseFormatter::success($role, 'Role Berhasil Ditemukan');
            }
            return ResponseFormatter::error('Role Tidak Ditemukan', 404);
        }

        //NOTE Get multiple Data
        $roles = $roleQuery->where('company_id', $request->company_id);

        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }
        if ($with_responsibilities) {
            $roles->with('responsibilities');
        }
        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Role Berhasil Ditemukan'
        );
    }
    public function create(CreateRoleRequest $request)
    {
        try {
            //NOTE Create role
            $role = role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);
            if (!$role) {
                throw new Exception('Role gagal dibuat');
            }
            return ResponseFormatter::success($role, 'Role Berhasil Ditambahkan');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            //NOTE Get role
            $role = role::find($id);
            if (!$role) {
                throw new Exception('role Tidak Ditemukan');
            }

            //NOTE Update role
            $role->update(
                [
                    'name' => $request->name,
                    'company_id' => $request->company_id,
                ]
            );
            return ResponseFormatter::success($role, 'role Berhasil Diubah');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }


    public function destroy($id)
    {
        try {
            //NOTE - Get role
            $role = role::find($id);
            //TODO - Check if role owner by user

            //NOTE - Check if role exist
            if (!$role) {
                throw new Exception('Role Tidak Ditemukan');
            }
            //NOTE - Delete role
            $role->delete();
            return ResponseFormatter::success('Role Berhasil Dihapus');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
