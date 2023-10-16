<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;



class TeamController extends Controller
{
    public function create(CreateTeamRequest $request)
    {
        try {
            //NOTE Upload logo 
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }
            //NOTE Create Team
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id,
            ]);
            if (!$team) {
                throw new Exception('Team gagal dibuat');
            }
            return ResponseFormatter::success($team, 'Data Berhasil Ditambahkan');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {
            //NOTE Get team
            $team = Team::find($id);
            if (!$team) {
                throw new Exception('Team Tidak Ditemukan');
            }
            //NOTE Upload logo
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }
            //NOTE Update team
            $team->update(
                [
                    'name' => $request->name,
                    'icon' => isset($path) ? $path : $team->icon,
                    'company_id' => $request->company_id,

                ]
            );
            return ResponseFormatter::success($team, 'Team Berhasil Diubah');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $teamQuery = Team::query();
        //NOTE - powerhuman.com/api/team?1d=1

        if ($id) {
            $team = $teamQuery->find($id);

            if ($team) {
                return ResponseFormatter::success($team, 'Team Berhasil Ditemukan');
            }
            return ResponseFormatter::error('Team Tidak Ditemukan', 404);
        }

        //NOTE Get multiple Data
        $teams = $teamQuery->where('company_id', $request->company_id);

        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }
        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Team Berhasil Ditemukan'
        );
    }

    public function destroy($id)
    {
        try {
            //NOTE - Get team
            $team = Team::find($id);
            //TODO - Check if team owner by user

            //NOTE - Check if team exist
            if (!$team) {
                throw new Exception('Team Tidak Ditemukan');
            }
            //NOTE - Delete team
            $team->delete();
            return ResponseFormatter::success('Team Berhasil Dihapus');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
