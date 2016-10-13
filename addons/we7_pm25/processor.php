<?php
/**
 * 天气查询模块处理程序
 *
 * @author 微擎团队
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class We7_pm25ModuleProcessor extends WeModuleProcessor {
	public $token = '5j1znBVAsnSf5xQyNQyq';
	public $city = '0351';
	public $apiurl = 'http://www.pm25.in/api/querys/aqi_details.json';
	
	public function respond() {
		global $_W;
		
		$history = pdo_fetch("SELECT * FROM ".tablename('we7_pm25')." WHERE rid = :rid", array(':rid' => $this->rule));
		if (empty($history)) {
			pdo_insert('we7_pm25', array(
				'rid' => $this->rule,
				'content' => '',
				'lasterror' => '',
				'lastupdate' => '0',
			));
		}
		if (!empty($history) && date('YmdH') <= date('YmdH', $history['lastupdate'])) {
			$data = iunserializer($history['content']);
			return $this->formatData($data);
		}
		
		load()->func('communication');
		$content = $this->message['content'];
		$response = ihttp_request($this->apiurl . "?city={$this->city}&token={$this->token}");
		//$response['content'] = '[{"aqi":26,"area":"太原","co":2.543,"co_24h":2.525,"no2":19,"no2_24h":25,"o3":45,"o3_24h":45,"o3_8h":30,"o3_8h_24h":39,"pm10":25,"pm10_24h":41,"pm2_5":10,"pm2_5_24h":22,"position_name":"尖草坪","primary_pollutant":"—","quality":"优","so2":51,"so2_24h":68,"station_code":"1081A","time_point":"2015-02-05T14:00:00Z"},{"aqi":23,"area":"太原","co":0.421,"co_24h":0.469,"no2":26,"no2_24h":28,"o3":32,"o3_24h":35,"o3_8h":29,"o3_8h_24h":34,"pm10":21,"pm10_24h":41,"pm2_5":10,"pm2_5_24h":29,"position_name":"涧河","primary_pollutant":"—","quality":"优","so2":68,"so2_24h":51,"station_code":"1082A","time_point":"2015-02-05T14:00:00Z"},{"aqi":22,"area":"太原","co":1.033,"co_24h":1.011,"no2":14,"no2_24h":21,"o3":22,"o3_24h":24,"o3_8h":18,"o3_8h_24h":22,"pm10":22,"pm10_24h":23,"pm2_5":13,"pm2_5_24h":9,"position_name":"上兰","primary_pollutant":"—","quality":"优","so2":43,"so2_24h":51,"station_code":"1083A","time_point":"2015-02-05T14:00:00Z"},{"aqi":49,"area":"太原","co":2.026,"co_24h":1.858,"no2":29,"no2_24h":27,"o3":39,"o3_24h":47,"o3_8h":25,"o3_8h_24h":40,"pm10":47,"pm10_24h":36,"pm2_5":34,"pm2_5_24h":24,"position_name":"晋源","primary_pollutant":"—","quality":"优","so2":77,"so2_24h":53,"station_code":"1084A","time_point":"2015-02-05T14:00:00Z"},{"aqi":40,"area":"太原","co":1.199,"co_24h":1.151,"no2":25,"no2_24h":25,"o3":48,"o3_24h":74,"o3_8h":40,"o3_8h_24h":61,"pm10":36,"pm10_24h":31,"pm2_5":28,"pm2_5_24h":19,"position_name":"小店","primary_pollutant":"—","quality":"优","so2":36,"so2_24h":32,"station_code":"1085A","time_point":"2015-02-05T14:00:00Z"},{"aqi":35,"area":"太原","co":3.401,"co_24h":3.315,"no2":24,"no2_24h":34,"o3":18,"o3_24h":19,"o3_8h":11,"o3_8h_24h":16,"pm10":24,"pm10_24h":32,"pm2_5":11,"pm2_5_24h":17,"position_name":"桃园","primary_pollutant":"—","quality":"优","so2":62,"so2_24h":69,"station_code":"1086A","time_point":"2015-02-05T14:00:00Z"},{"aqi":51,"area":"太原","co":2.244,"co_24h":2.145,"no2":22,"no2_24h":23,"o3":26,"o3_24h":26,"o3_8h":22,"o3_8h_24h":24,"pm10":43,"pm10_24h":50,"pm2_5":23,"pm2_5_24h":29,"position_name":"坞城","primary_pollutant":"二氧化硫","quality":"良","so2":153,"so2_24h":142,"station_code":"1087A","time_point":"2015-02-05T14:00:00Z"},{"aqi":26,"area":"太原","co":0.602,"co_24h":0.671,"no2":12,"no2_24h":21,"o3":80,"o3_24h":81,"o3_8h":58,"o3_8h_24h":75,"pm10":26,"pm10_24h":41,"pm2_5":16,"pm2_5_24h":24,"position_name":"南寨","primary_pollutant":"—","quality":"优","so2":37,"so2_24h":38,"station_code":"1088A","time_point":"2015-02-05T14:00:00Z"},{"aqi":61,"area":"太原","co":1.063,"co_24h":0.74,"no2":56,"no2_24h":44,"o3":42,"o3_24h":71,"o3_8h":32,"o3_8h_24h":56,"pm10":72,"pm10_24h":67,"pm2_5":42,"pm2_5_24h":39,"position_name":"金胜","primary_pollutant":"颗粒物(PM10)","quality":"良","so2":213,"so2_24h":143,"station_code":"1089A","time_point":"2015-02-05T14:00:00Z"},{"aqi":36,"area":"太原","co":1.615,"co_24h":1.543,"no2":25,"no2_24h":27,"o3":39,"o3_24h":46,"o3_8h":29,"o3_8h_24h":40,"pm10":35,"pm10_24h":40,"pm2_5":20,"pm2_5_24h":23,"position_name":null,"primary_pollutant":"","quality":"优","so2":82,"so2_24h":71,"station_code":null,"time_point":"2015-02-05T14:00:00Z"}]';
		if (is_error($response)) {
			return $this->formatData($history['content']);
		}
		$response = json_decode($response['content'], true);
		if (!empty($response['error'])) {
			pdo_update('we7_pm25', array('lasterror' => $response['error']), array('rid' => $this->rule));
			return $this->formatData($history['content']);
		}
		if (!is_array($response)) {
			return $this->formatData($history['content']);
		}
		pdo_update('we7_pm25', array('content' => iserializer($response), 'lastupdate' => TIMESTAMP), array('rid' => $this->rule));
		return $this->formatData($response);
	}
	
	public function formatData($content) {
		$city = array_pop($content);
		$level = $this->getLevel($city['aqi']);
		$news = array();
		$news[] = array(
			'title' => $city['quality'] . '，' . $level['description'],
			'description' => $city['quality'] . '，' . $level['description'] . '更新时间：' . $city['time_point'] . '(来源：http://pm25.in)',
			'picurl' => MODULE_URL . 'images/thumb.png',
			'url' => 'http://www.pm25.in/taiyuan',
		);
		foreach($content as $c) {
			if (empty($c['position_name'])) {
				continue;
			}
			$level = $this->getLevel($c['aqi']);
			$row = array();
			$row['title'] = $c['position_name'] . '，首要污染物：' . $c['primary_pollutant'] . '，PM2.5：'.$c['pm2_5'];
			$row['picurl'] = MODULE_URL . 'images/level'.$level['level'].'.png';
			$row['url'] = 'http://www.pm25.in/taiyuan';
			$news[] = $row;
		}
		return $this->respNews($news);
	}
	
	public function getLevel($aqi) {
		$result = array('level' => 0, 'description' => '');
		if ($aqi <= 50) {
			$result['level'] = 1;
			$result['description'] = '空气很好，可以外出活动，呼吸新鲜空气。';
		} elseif ($aqi <= 51) {
			$result['level'] = 2;
			$result['description'] = '可以正常进行室外活动。';
		} elseif ($aqi <= 101) {
			$result['level'] = 3;
			$result['description'] = '敏感人群（老人、小孩、呼吸道疾病患者等）减少体力消耗大的户外活动。';
		} elseif ($aqi <= 151) {
			$result['level'] = 4;
				$result['description'] = '对敏感人（老人、小孩、呼吸道疾病患者等）群影响较大。';
		} elseif ($aqi <= 201) {
			$result['level'] = 5;
				$result['description'] = '所有人应适当减少室外活动。';
		} else {
			$result['level'] = 6;
			$result['description'] = '尽量不要留在室外。';
		}
		return $result;
	}
}