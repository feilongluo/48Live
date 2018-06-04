<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class LiveController extends Controller{
	const HEADER = [
		'Content-type' => 'application/json',
		'version' => '5.0.1',
		'os' => 'Android'
	];
	const METHOD_POST = 'POST';

	const URL_MEMBER_LIVE_PAGE = 'https://plive.48.cn/livesystem/api/live/v1/memberLivePage';
	const URL_MEMBER_LIVE_SHOW = 'https://plive.48.cn/livesystem/api/live/v1/getLiveOne';

	public function list(){
		$body = [
			'lastTime' => '0',
			'groupId' => '0',
			'type' => '0',
			'memberId' => $this->request->memberId ?? '0',
			'giftUpdTime' => '1498211389003',
			'limit' => $this->request->limit ?? 800
		];

		$result = $this->send(self::URL_MEMBER_LIVE_PAGE, $body);
		if(!$result){
			return $this->failed('请求失败');
		}

		$data = [
			'liveList' => $result['content']['liveList'],
			'reviewList' => $result['content']['reviewList'],
		];

		return $this->success($data);
	}

	public function show($liveId){
		$body = [
			'type' => '1',
			'userId' => '0',
			'liveId' => $liveId
		];

		$result = $this->send(self::URL_MEMBER_LIVE_SHOW, $body);

		if(!$result){
			return $this->failed('请求失败');
		}

		$data = $result['content'];

		return $this->success($data);
	}

	public function barrage(){
		$url = $this->request->barrageUrl;
		if(!$url){
			return $this->failed('url不能为空');
		}
		$content = file_get_contents($url);

		$array = explode("\r\n", $content);
		$barrages = [];
		foreach($array as $item){
			$arr = explode(']', $item);
			if(!is_array($arr) || empty($arr) || count($arr) < 2){
				continue;
			}
			$time = str_replace('[', '', $arr[0]);
			$arr = explode("\t", $arr[1]);
			$barrages[] = [
				'time' => $time,
				'username' => $arr[0],
				'content' => $arr[1]
			];
		}

		return $this->success([
			'barrages' => $barrages
		]);
	}

	/**
	 * @param $url
	 * @param $body
	 * @return bool|Response
	 */
	private function send($url, $body){
		$request = new Request(self::METHOD_POST, $url, self::HEADER, json_encode($body));
		$client = new Client();
		try{
			$response = $client->send($request);
			$body = json_decode($response->getBody(), true);
			if($body['status'] == 200){
				return $body;
			}else{
				\Log::debug($body['message']);
				return false;
			}
		}catch(GuzzleException $e){
			\Log::debug($e->getMessage());
			return false;
		}
	}
}
