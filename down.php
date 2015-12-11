<?php
/***
 * demo程序：利用360云盘进行直链下载（永久地址）
 * 作者：Walker (tech56@qq.com)
 * 本程序仅供演示,请根据实际情况修改
 * 参数可以如下：
 * down.php?url=Q4p3uUkmchPhI_5de2 (有密码)
 * down.php?url=cFhEJLIGPa2PW  (无密码)
 * 演示地址：http://so.ygyhg.com/jiexi
***/
//截取s1和s2之间的字符串
function Search_String($html,$s1,$s2)
{
	$n=strpos($html,$s1);//寻找位置
	if($n)
	{
		$n+=strlen($s1);
		$str=substr($html,$n);//删除前面的
		$n=strpos($str,$s2);
		if($n)
		{
			$str=substr($str,0,$n);
			return $str;
		}
		else
			return "字符串不匹配！";
	}
	else
		return "字符串不匹配！";
}
//PHP和JS通讯通常都用json，
//但是PHP要用json的数据，通过json_decode转出来的数组并不是标准的array，
//所以需要用这个函数进行转换。
function object_array($array){
	if(is_object($array)){
		$array = (array)$array;
	}
	if(is_array($array)){
		foreach($array as $key=>$value){
			$array[$key] = object_array($value);
		}
	}
	return $array;
}
if(!empty($_GET['url']))
{
	$url=trim($_GET['url']);
	$arr=explode('_', $url);
	$surl=$arr[0];
	$url=str_replace('_', '%20', $url);//默认分隔符为空格，所以需将_替换成%20才能正常解析
	$domain='http://so.ygyhg.com/';
	$geturl=$domain.'360jiexi.php?url='.urlencode('http://yunpan.cn/'.$url);
	$res =object_array(file_get_contents($geturl));
	if(strstr($res,'errno')){
		$errno=Search_String($res, 'errno":', ',');
		if($errno==0){
			$downurl=Search_String($res, '"downloadurl":"', '"');
			$downurl=str_replace('\\','',$downurl);
			header("Location:".$downurl);
			return;
		}
	}
	//解析失败则跳到360分享页面
	$downurl='http://yunpan.cn/'.$surl;
	header("Location:".$downurl);
}
?>