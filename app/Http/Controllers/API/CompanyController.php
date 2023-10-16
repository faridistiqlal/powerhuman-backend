<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::user()->id);
        });
        //NOTE - powerhuman.com/api/company?1d=1

        if ($id) {
            $company = $companyQuery->find($id);

            if ($company) {
                return ResponseFormatter::success($company, 'Data Berhasil Ditemukan');
            }
            return ResponseFormatter::error('Data Tidak Ditemukan', 404);
        }

        //NOTE Get multiple Data
        $companies = $companyQuery;

        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }
        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Data Berhasil Ditemukan'
        );
    }
    public function create(CreateCompanyRequest $request)
    {
        try {
            //NOTE Upload logo 
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }
            //NOTE Create company
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path,
            ]);
            if (!$company) {
                throw new Exception('Company gagal dibuat');
            }
            //NOTE Attach company for user
            $user = User::find(Auth::user()->id);
            $user->companies()->attach($company->id);
            //NOTE Load user
            $company->load('users');
            return ResponseFormatter::success($company, 'Data Berhasil Ditambahkan');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            //NOTE Get company
            $company = Company::find($id);
            if (!$company) {
                throw new Exception('Company Tidak Ditemukan');
            }
            //NOTE Upload logo
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }
            //NOTE Update company
            $company->update(
                [
                    'name' => $request->name,
                    'logo' => isset($path) ? $path : $company->icon,
                ]
            );
            return ResponseFormatter::success($company, 'Data Berhasil Diubah');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
