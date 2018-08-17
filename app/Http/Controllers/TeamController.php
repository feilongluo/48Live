<?php

namespace App\Http\Controllers;

use App\Models\Team;

class TeamController extends Controller{
	/**
	 * 获取队伍列表
	 */
	public function all(){
		$teams = Team::all();
		return $this->success($teams);
	}
}
