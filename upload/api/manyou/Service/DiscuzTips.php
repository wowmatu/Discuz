<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: DiscuzTips.php 28950 2012-03-20 08:59:51Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_DiscuzTips {

	protected static $_instance;

	public static function getInstance() {
		global $_G;

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function show() {
		global $_G;
		if ($_G['adminid'] != 1) {
			return false;
		}
		$util = Cloud::loadclass('Service_Util');
		include_once DISCUZ_ROOT . '/source/discuz_version.php';
		$release = DISCUZ_RELEASE;
		$fix = DISCUZ_FIXBUG;
		$cloudApi = $util->getApiVersion();
		$isfounder = $this->isfounder($_G['member']);
		$sId = $_G['setting']['my_siteid'];
		$version = $_G['setting']['version'];
		$ts = TIMESTAMP;
		$sig = '';
		if ($sId) {
			$params = array(
				's_id' => $sId,
				'product_version' => $version,
				'product_release' => $release,
				'fix_bug' => $fix,
				'is_founder' => $isfounder,
				's_url' => $_G[siteurl],
				'last_send_time' => $_COOKIE['dctips'],
			);
			ksort($params);

			$str = $util->httpBuildQuery($params, '', '&');
			$sig = md5(sprintf('%s|%s|%s', $str, $_G['setting']['my_sitekey'], $ts));
		}

		$jsCode = <<< EOF
			<div id="discuz_tips" style="display:none;"></div>
			<script type="text/javascript">
				var discuzSId = '$sId';
				var discuzVersion = '$version';
				var discuzRelease = '$release';
				var discuzApi = '$cloudApi';
				var discuzIsFounder = '$isfounder';
				var discuzFixbug = '$fix';
				var ts = '$ts';
				var sig = '$sig';
			</script>
			<script src="http://discuz.gtimg.cn/cloud/scripts/discuz_tips.js?v=1" type="text/javascript" charset="UTF-8"></script>
EOF;
		echo $jsCode;
	}

	private function isfounder($user) {
		global $_G;
		$founders = str_replace(' ', '', $_G['config']['admincp']['founder']);
		if(!$user['uid'] || $user['groupid'] != 1 || $user['adminid'] != 1) {
			return false;
		} elseif(empty($founders)) {
			return false;
		} elseif(strexists(",$founders,", ",$user[uid],")) {
			return true;
		} elseif(!is_numeric($user['username']) && strexists(",$founders,", ",$user[username],")) {
			return true;
		} else {
			return FALSE;
		}
	}
}