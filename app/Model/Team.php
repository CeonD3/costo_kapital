<?php 

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utilitarian\{FG};

class Team extends Model {

    use SoftDeletes;

	protected $table = 'teams';
    public function getTeam()
    {
        $rsp = FG::responseDefault();
        try {
            $sql_rsp = Team::where('deleted_at')->orderBy('order')->get();
            $rsp['success'] = true;
            $rsp['data'] = $sql_rsp;
            $rsp['message'] = 'successfully';
        } catch (\Throwable $th) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function getTeamById($id)
    {
        $rsp = FG::responseDefault();
        try {
            $sql_rsp = Team::where('deleted_at')->where('id',$id)->orderBy('order')->first();
            $rsp['success'] = true;
            $rsp['data'] = $sql_rsp;
            $rsp['message'] = 'successfully';
        } catch (\Throwable $th) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function addTeam($request)
    {
        extract($request->getParsedBody());       
        $rsp = FG::responseDefault();
        try {
            $team = new Team();
            $team->name = $name;
            $team->order = $order;
            $team->save();
            $rsp['success'] = true;
            $rsp['data'] = $sql_rsp;
            $rsp['message'] = 'successfully';
        } catch (\Throwable $th) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function updateTeam($request)
    {
        extract($request->getParsedBody());       
        $rsp = FG::responseDefault();
        try {
            $data_update = array(
                "name" => $name,
                "order" => $order
            );
           
            Team::where('deleted_at')
            ->where('id',$id_team)
            ->update($data_update);

            $rsp['success'] = true;
            $rsp['data'] = $data_update;
            $rsp['message'] = 'successfully';
        } catch (\Throwable $th) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function deleteTeam($request)
    {
        extract($request->getParsedBody());       
        $rsp = FG::responseDefault();
        try {

            $post = Team::find($id_team);
            $post->delete();

            $rsp['success'] = true;
            $rsp['data'] = $id_team;
            $rsp['message'] = 'successfully';
        } catch (\Throwable $th) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

}
