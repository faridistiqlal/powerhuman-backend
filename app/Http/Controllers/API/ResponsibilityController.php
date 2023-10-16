<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Responsibility;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateResponsibilityRequest;





class ResponsibilityController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $responsibilityQuery = Responsibility::query();
        //NOTE - powerhuman.com/api/Responsibility?1d=1

        if ($id) {
            $responsibility = $responsibilityQuery->find($id);

            if ($responsibility) {
                return ResponseFormatter::success($responsibility, 'Responsibility Berhasil Ditemukan');
            }
            return ResponseFormatter::error('Responsibility Tidak Ditemukan', 404);
        }

        //NOTE Get multiple Data
        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        if ($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%');
        }
        return ResponseFormatter::success(
            $responsibilities->paginate($limit),
            'Responsibility Berhasil Ditemukan'
        );
    }
    public function create(CreateResponsibilityRequest $request)
    {
        try {
            //NOTE Create Responsibility
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);
            if (!$responsibility) {
                throw new Exception('Responsibility gagal dibuat');
            }
            return ResponseFormatter::success($responsibility, 'Responsibility Berhasil Ditambahkan');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            //NOTE - Get Responsibility
            $responsibility = Responsibility::find($id);
            //TODO - Check if Responsibility owner by user

            //NOTE - Check if Responsibility exist
            if (!$responsibility) {
                throw new Exception('Responsibility Tidak Ditemukan');
            }
            //NOTE - Delete Responsibility
            $responsibility->delete();
            return ResponseFormatter::success('Responsibility Berhasil Dihapus');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
