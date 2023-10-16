<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;




class EmployeeController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $email = $request->input('email');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');
        $limit = $request->input('limit', 10);
        $employeeQuery = Employee::query();

        //NOTE - Get single data
        if ($id) {
            $employee = $employeeQuery->with(['team', 'role'])->find($id);

            if ($employee) {
                return ResponseFormatter::success($employee, 'Employee Berhasil Ditemukan');
            }
            return ResponseFormatter::error('Employee Tidak Ditemukan', 404);
        }

        //NOTE Get multiple Data
        $employees = $employeeQuery;

        if ($name) {
            $employees->where('name', 'like', '%' . $name . '%');
        }

        if ($email) {
            $employees->where('email', $email);
        }

        if ($age) {
            $employees->where('age', $age);
        }

        if ($phone) {
            $employees->where('phone', 'like', '%' . $phone . '%');
        }

        if ($role_id) {
            $employees->where('role_id', $role_id);
        }

        if ($team_id) {
            $employees->where('team_id', $team_id);
        }

        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Employee Berhasil Ditemukan'
        );
    }

    public function create(CreateEmployeeRequest $request)
    {
        try {
            //NOTE - Buat employee 
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }
            //NOTE Create Employee
            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => $path,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id,
            ]);
            if (!$employee) {
                throw new Exception('Employee gagal dibuat');
            }
            return ResponseFormatter::success($employee, 'Data Berhasil Ditambahkan');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        try {
            //NOTE Get employee
            $employee = Employee::find($id);
            if (!$employee) {
                throw new Exception('Employee Tidak Ditemukan');
            }
            //NOTE Upload photo
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }
            //NOTE Update employee
            $employee->update(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'gender' => $request->gender,
                    'age' => $request->age,
                    'phone' => $request->phone,
                    'photo' => $path,
                    'team_id' => $request->team_id,
                    'role_id' => $request->role_id,

                ]
            );
            return ResponseFormatter::success($employee, 'Employee Berhasil Diubah');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }



    public function destroy($id)
    {
        try {
            //NOTE - Get employee
            $employee = Employee::find($id);
            //TODO - Check if employee owner by user

            //NOTE - Check if employee exist
            if (!$employee) {
                throw new Exception('Employee Tidak Ditemukan');
            }
            //NOTE - Delete employee
            $employee->delete();
            return ResponseFormatter::success('Employee Berhasil Dihapus');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
