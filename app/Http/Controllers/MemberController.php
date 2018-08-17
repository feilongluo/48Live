<?php

namespace App\Http\Controllers;

use App\Models\Member;

class MemberController extends Controller{
	/**
	 * 获取成员列表
	 */
	public function all(){
		$members = Member::all();
		return $this->success($members);
	}
}
