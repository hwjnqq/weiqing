<?php
/**
 * 苹果售后查询模块处理程序
 *
 * @author WeEngine Team
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class ApplesupportModuleProcessor extends WeModuleProcessor {
	public function respond() {
		if (!$this->inContext) {
			$this->beginContext(300);
		}
		$ret = preg_match('/保修 *(?P<sn>.+)$/i', $this->message['content'], $matchs);
		if(!$ret) {
			return $this->respText('请输入合适的格式, 保修+串码, 例如: 保修 1234567890');
		}

		$url = 'https://selfsolve.apple.com/agreementWarrantyDynamic.do';
		$urlpost = 'https://selfsolve.apple.com/wcResults.do';
		$urlreg = 'https://selfsolve.apple.com/RegisterProduct.do?productRegister=Y&country=USA&id=';
		$sn = $matchs['sn'];
		load()->func('communication');
		$response = ihttp_get($url);
		preg_match('/initialize\((?P<num>[0-9]+)\)/', $response['content'], $matchs);
		$num = $matchs['num'];
		$cookiejar = $response['headers']['Set-Cookie'];
		$data = array(
			'cn' => '',
			'locale' => '',
			'caller' => '',
			'sn' => $sn,
			'num' => $num,
		);
		$response = ihttp_request($urlpost, $data, array(
			'CURLOPT_COOKIE' => implode('; ', $cookiejar), 
			'CURLOPT_REFERER' => 'https://selfsolve.apple.com/agreementWarrantyDynamic.do',
		));
		if (strexists($response['content'], 'RegisterProduct')) {
			$response = ihttp_request($urlreg.$sn, array(), array(
				'CURLOPT_COOKIE' => implode('; ', $cookiejar), 
				'CURLOPT_REFERER' => 'https://selfsolve.apple.com/agreementWarrantyDynamic.do',
			));
			preg_match("/productname\">(.*?)<\/span>/", $response['content'], $matchs);
			$content[3] = $matchs[1];
		} else {
			preg_match("/displayProductInfo\((.*?);/", $response['content'], $matchs);
			$content = explode("'", $matchs[1]);
			preg_match("/setClassAndShow\((.*?);/", $response['content'], $matchs);
			$registration = explode("'", $matchs[1]);
			if (!empty($registration)) {
				preg_match("/displayPHSupportInfo\((.*?)\)/", $response['content'], $matchs);
				$PHsupport = explode("'", $matchs[1]);
				$isPHsupport = strexists($PHsupport[0], 'true');
				if ($isPHsupport) {
					preg_match('/Estimated Expiration Date: (.*?)\</', $PHsupport[3], $matchs);
					$PHsupportdate = date('Y年m月d日', strtotime($matchs[1]));
				}
				preg_match("/displayHWSupportInfo\((.*?)\)/", $response['content'], $matchs);
				$HWsupport = explode("'", $matchs[1]);
				$isHWsupport = strexists($HWsupport[0], 'true');
				if ($isHWsupport) {
					preg_match('/Estimated Expiration Date: (.*?)\</', $HWsupport[3], $matchs);
					$HWsupportdate = date('Y年m月d日', strtotime($matchs[1]));
				}
			}
		}
		$reply = 	'设备名称：' . $content[3] . PHP_EOL . '激活状态：' . (!empty($registration) && $registration[5] == 'registration-true' ? '已激活' : '未激活') . PHP_EOL;
		if (!empty($registration[5])) {
			$reply .= 	'电话支持：' . ($isPHsupport ? '未过期 ('.$PHsupportdate.')' : '已过期') . PHP_EOL .
			'硬件保修：' . ($isHWsupport ? '未过期 ('.$HWsupportdate.')' : '已过期') . PHP_EOL;
		}
		return $this->respText($reply);
	}
}