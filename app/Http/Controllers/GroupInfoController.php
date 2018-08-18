<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Member;
use App\Models\Team;

class GroupInfoController extends Controller{
	public function all(){
		$members = Member::query()->select(['member_id', 'real_name', 'team_id'])->get()->toArray();
		$teams = Team::query()->select(['team_id', 'team_name', 'color', 'group_id'])->get()->toArray();
		$groups = Group::query()->select(['group_id', 'group_name'])->get()->toArray();

		foreach($members as $key => $member){
			$member['team'] = array_first($teams, function($value, $index) use ($member){
				return $value['team_id'] == $member['team_id'];
			});
			$members[$key] = $member;
		}

		foreach($teams as $key => $team){
			$team['members'] = [];
			foreach($members as $member){
				if($member['team_id'] == $team['team_id']){
					$team['members'][] = $member;
				}
			}
			$teams[$key] = $team;
		}

		foreach($groups as $key => $group){
			$group['teams'] = [];
			foreach($teams as $team){
				if($team['group_id'] == $group['group_id']){
					$group['teams'][] = $team;
				}
			}
			$groups[$key] = $group;
		}

		return $this->success([
			'groups' => $groups,
			'teams' => $teams,
			'members' => $members
		]);
	}
}
