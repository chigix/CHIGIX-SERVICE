<?php
function chijiJsCompress($src){
	import('ORG.Chiji.Jsxs');
	import('ORG.Chiji.PregFile');
	$jsxs = new Jsxs();
	$jsxs->setRegexDirectory(dirname(__FILE__).'/preg');
	$jsxs->setCompatibility(true);
	$jsxs->setReduce(true);
	$jsxs->setShrink(true);
	$jsxs->setConcatString(true);
	$startTime = microtime(true);
	$srcxs = $jsxs->exec($src);
	$endTime = microtime(true);
	/*
	$result = array(
	  'time' => ($endTime - $startTime),
	  'original lenght' => strlen($src),
	  'compact lenght' => strlen($srcxs),
	  'diff' => strlen($srcxs) - strlen($src),
	  'ratio' => strlen($srcxs) / strlen($src),
	);
	*/
	return ($srcxs);
}
?>