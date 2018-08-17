<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\Team;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;

class UpdateMembers extends Command{
	const URL = 'https://psync.48.cn/syncsystem/api/cache/v1/update/overview';


	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'members:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle(){
		$header = [
			'Content-type' => 'application/json',
			'version' => '5.0.1',
			'os' => 'Android'
		];

		$body = [
			'videoTypeUtime' => '2013-03-24 15:59:11',
			'musicAlbumUtime' => '2013-04-18 14:45:37',
			'functionUtime' => '2013-10-17 15:00:00',
			'groupUtime' => '2013-10-17 17:27:00',
			'memberInfoUtime' => '2013-10-20 11:55:09',
			'talkUtime' => '2013-05-05 18:04:52',
			'videoUtime' => '2013-05-17 18:36:32',
			'musicUtime' => '2013-05-05 15:56:11',
			'urlUtime' => '2013-07-19 12:10:59',
			'teamUtime' => '2013-10-20 10:39:00',
			'memberPropertyUtime' => '2013-02-20 18:57:48',
			'periodUtime' => '2013-10-14 14:45:00'
		];

		$request = new Request('POST', self::URL, $header, json_encode($body));
		$client = new Client();

		try{
			$response = $client->send($request);
			$result = json_decode($response->getBody(), true);

			$members = $result['content']['memberInfo'];
			if(is_array($members) && !empty($members)){
				$insertData = [];
				foreach($members as $member){
					$insertData[] = [
						'member_id' => $member['member_id'],
						'real_name' => $member['real_name'],
						'team_id' => $member['team'],
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s')
					];
				}
				Member::query()->truncate();
				Member::query()->insert($insertData);
			}

			$teams = $result['content']['team'];
			if(is_array($teams) && !empty($teams)){
				$insertData = [];
				foreach($teams as $team){
					$insertData[] = [
						'team_id' => $team['team_id'],
						'group_id' => $team['group_id'],
						'team_name' => $team['team_name'],
						'color' => $team['color'],
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s')
					];
				}
				Team::query()->truncate();
				Team::query()->insert($insertData);
			}
		}catch(GuzzleException $e){
			\Log::debug($e->getMessage());
		}
	}
}
