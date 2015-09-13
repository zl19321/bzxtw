<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Keylink.function.php
// +----------------------------------------------------------------------
// | Date: 2010 09:21:13
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 加关键词链接
// +----------------------------------------------------------------------
function Keylink($txt, $replacenum = '') {
	$find = '/'.$txt.'/usi';	
	$linkdatas = F ('keylink');	
	if($linkdatas)	{		
		foreach($linkdatas as $v) {
			$find = '/'.$v['word'].'/usi';
			$url = $v['url'];
			
			$isFind = false;
			$matches = array();
			preg_match_all($find, $txt, $matches, PREG_OFFSET_CAPTURE);
			$matchData = $matches[0];
			
			$find = $v['word'];
			$noChanges = array(
				'/<h[1-6][^>]*>[^<]*'.$find.'[^<]*<\/h[1-6]>/usi',
				'/<a[^>]+>[^<]*'.$find.'[^<]*<\/a>/usi',
				'/href=("|\')[^"\']+'.$find.'(.*)[^"\']+("|\')/usi',
				'/src=("|\')[^"\']*'.$find.'[^"\']*("|\')/usi',
				'/alt=("|\')[^"\']*'.$find.'[^"\']*("|\')/usi',
				'/title=("|\')[^"\']*'.$find.'[^"\']*("|\')/usi',
				'/content=("|\')[^"\']*'.$find.'[^"\']*("|\')/usi',
				'/<script[^>]*>[^<]*'.$find.'[^<]*<\/script>/usi',
				'/<embed[^>]+>[^<]*'.$find.'[^<]*<\/embed>/usi',
				'/wmode=("|\')[^"\']*'.$find.'[^"\']*("|\')/usi'
			);
			
			foreach($noChanges as $noChange){
				$results = array();
				preg_match_all($noChange, $txt, $results, PREG_OFFSET_CAPTURE);
				$matches = $results[0];
				if(!count($matches) == 0) {
					foreach($matches as $match){
						$start = $match[1];
						$end = $match[1] + strlen($match[0]);
						foreach($matchData as $index => $data){
							if($data[1] >= $start && $data[1] <= $end){
								$matchData[$index][2] = true;
							}
						}
					}
				}		
			}
			
			foreach($matchData as $index => $match){
				if($match[2] != true){
					$isFind = $match;
					break;
				}
			}
			
			if(is_array($isFind)){
				$replacement = '<a href="'.$url.'" target="_blank" class="keylink" >'.$isFind[0].'</a>';				
				$txt = substr($txt, 0, $isFind[1]) . $replacement . substr($txt, $isFind[1] + strlen($isFind[0]));
			}
		}
	}	
	return $txt;
}