<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	protected $request;

	public function __construct(Request $request){
		$this->request = $request;
	}

	/**
	 * @param string $msg
	 * @param array $data
	 * @return array
	 */
	protected function success($data = [], $msg = ''){
		return [
			'errorCode' => 0,
			'msg' => $msg,
			'data' => $data
		];
	}

	/**
	 * @param int $errorCode
	 * @param string $msg
	 * @return array
	 */
	protected function failed($msg = ''){
		return [
			'errorCode' => 1,
			'msg' => $msg,
		];
	}
}
