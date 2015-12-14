<?php
/***配置要求：php版本>5.3
*作者：Walker 
*暂只支持不加密单文件
*长短链接均支持，但长链接需要&改成*才能正常解析
*默认以空格作为短址和密码的分隔符
*但实际使用时空格会被URL编码为%20
*可以将分隔符改为|、#等其他符号
*调用方式：
*[无密码]http://www.yourdomain.com/baidujiexi.php?url=http://pan.baidu.com/s/1o6idssq
*[无密码]http://www.yourdomain.com/baidujiexi.php?url=http://pan.baidu.com/share/link?shareid=623475790*uk=1074070979*fid=428520128265569
*[有密码]暂不支持
***/
header('Content-type: application/json');
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
	/** 
    * 返回一定位数的时间戳，多少位由参数决定 
    * @param type 多少位的时间戳 
    * @return 时间戳 
    */  
    function getTimestamp($digits = false) {  
        $digits = $digits > 10 ? $digits : 10;  
        $digits = $digits - 10;  
        if ((!$digits) || ($digits == 10))  
        {  
            return time();  
        }  
        else  
        {  
            return number_format(microtime(true),$digits,'','');  
        }  
    }  
 
$errmsg='';//失败原因
$errno=55;//错误代号
$cookie='';//cookie值
$url='';
$password='';
if(!empty($_GET["url"]))
{
	$aurl=trim($_GET["url"]);
	$aurl=str_replace("*", "&", $aurl);//将链接中的*换成&
	$aurl=urldecode($aurl);
	$urlarr=explode(' ', $aurl);//默认以空格作为短址和密码的分隔符
	$url=$urlarr[0];
	if(count($urlarr)>=2)
	{
		$password=end($urlarr);
	}
}
else {
	$errmsg='URL地址不能为空！';
	$res=array("errno"=>$errno,"errmsg"=>$errmsg);
	echo json_encode($res);
	return;
}

$ch = curl_init($url); //初始化
$cookie='BAIDUID=E1A86A79C89DFC76D38899822435B3D5:FG=1; PANWEB=1;';//字符串Cookie 可选项  
$options = array(
		CURLOPT_FOLLOWLOCATION => TRUE,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0',
		CURLOPT_HEADER => TRUE,//将头文件的信息作为数据流输出
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => TRUE, //接收服务端范围的html代码而不是直接浏览器输出
		CURLOPT_TIMEOUT => 30,
		CURLOPT_COOKIE =>$cookie //带Cookie提交数据
);
curl_setopt_array($ch, $options);
$content = curl_exec($ch); //执行curl并赋值给$content
$errmsg=curl_error($ch);
$content=str_replace(" ","",$content);
$header=curl_getinfo($ch);
curl_close($ch);
//echo $content;
if(strpos($content,"网盘-链接不存在"))
{
	$errmsg='此链接分享内容可能因为涉及侵权、色情、反动、低俗等信息，无法访问!';
	$res=array("errno"=>$errno,"errmsg"=>$errmsg);
	echo json_encode($res);
	return;
}
if(strpos($content,"给您加密分享了文件"))//需要密码
{
	$errmsg='目前暂时不支持对加密文件的解析！';
	$res=array("errno"=>$errno,"errmsg"=>$errmsg);
	echo json_encode($res);
	return;
	/*******加密部分未成功***********
	$tmp=$header['url'];
	if(strstr($tmp,'share/init?shareid')){
		$tmp=$tmp."#";
		$mm=Search_String($tmp, 'baidu.com/share/init?', '#');
	}else{
		$errmsg='目前暂时不支持对该类型链接的解析！';
		$res=array("errno"=>$errno,"errmsg"=>$errmsg);
		echo json_encode($res);
		return;
	}
	//echo $content;return;
	$nurl='http://pan.baidu.com/share/verify?'.$mm.'&t='.getTimestamp(13).'&channel=chunlei&clienttype=0&web=1';
	$data = array(
			'pwd'=>$password,//密码
			'vcode'=>''//验证码
	);
	$ch = curl_init($nurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
	//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);//Header无法改变和$cookie取值有关，晕死了。。。
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	$response = curl_exec($ch);
	$errmsg=curl_error($ch);
	curl_close($ch);
	if(strstr($response,'errno'))
	{
		if(strstr($response,'{"errno":0'))//终于成功了
		{
			$response=str_replace(' ', '', $response);
			$ncookie=Search_String($response, 'Set-Cookie:', 'Cache-Control');
			$ncookie=explode(';', $ncookie)[0].';';
			$cookie=$cookie.$ncookie;
			$ch=curl_init();
			$options = array(
					CURLOPT_FOLLOWLOCATION => TRUE,
					CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0',
					CURLOPT_HEADER => false,
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => TRUE, //接收服务端范围的html代码而不是直接浏览器输出
					CURLOPT_TIMEOUT => 30,
					CURLOPT_COOKIE =>$cookie //带Cookie提交数据
			);
			curl_setopt_array($ch, $options);
			$content = curl_exec($ch); //执行curl并赋值给$content
			$errmsg=curl_error($ch);
			$content=str_replace(" ","",$content);
			//$header=curl_getinfo($ch);
			curl_close($ch);
		}
		else {
			echo $response;
			$errmsg='请检查输入的密码是否正确！您当前输入的密码为：'.$password;
			$res=array("errno"=>$errno,"errmsg"=>$errmsg);
			echo json_encode($res);
			return;
		}
	}
	else{
		$res=array("errno"=>$errno,"errmsg"=>$errmsg);
		echo json_encode($res);
		return;
	}
	**************/
}

//成功进入文件分享页面，接下来一个POST就可以了
if(strpos($content, 'fs_id')){
	$isdir = Search_string($content, "\"isdir\":", ",");
	if ($isdir == "1")
	{
		$errmsg = "目前暂不支持对文件夹的解析！";
		$res=array("errno"=>$errno,"errmsg"=>$errmsg);
		echo json_encode($res);
		return;
	}
	$fs_id = Search_string($content, "yunData.FS_ID=\"", "\"");
	$uk = Search_string($content, "yunData.SHARE_UK=\"", "\"");
	$shareid = Search_string($content, "\"shareid\":", ",");
	$sign = Search_string($content, "sign\":\"", "\"");
	$timestamp = Search_string($content, "\"timestamp\":", ",");
	//$durl = "http://pan.baidu.com/api/sharedownload?sign=".$sign."&tamp=".$timestamp."&bdstoken=&channel=chunlei&clienttype=0&web=1&app_id=250528";
	$durl="http://pan.baidu.com/api/sharedownload?http://pan.baidu.com/api/sharedownload?uk=".$uk."&shareid=".$shareid."&timestamp=".$timestamp."&sign=".$sign."&fid_list=[".$fs_id."]";
	$postdata='encrypt=0&extra=%7B%22sekey%22%3A%22null%22%7D&product=share&primaryid='.$shareid.'&shareid='.$shareid.'&uk='.$uk.'&fid_list=%5B'.$fs_id.'%5D&type=dlink';
	//echo $postdata;
	$data = array(
			'encrypt'=>'0',
			'fid_list'=>"[".(string)$fs_id."]",
			'primaryid'=>$shareid,
			'product'=>'share',
			'uk'=>$uk,
			'extra'=>'{"sekey":"null"}',
			'shareid'=>$shareid,
			'type'=>'dlink',
	);
	$headers = array(
			'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 Firefox/3.0',
			'Content-type:application/x-www-form-urlencoded; charset=UTF-8',
			'X-Requested-With:XMLHttpRequest'
	);
	$ch = curl_init($durl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);//Header无法改变和$cookie取值有关，晕死了。。。
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	$response = curl_exec($ch);
	$errmsg=curl_error($ch);
	curl_close($ch);
 	if(strstr($response,"\"errno\":")){//POST成功，但不一定会获取到真实地址
 		if(strstr($response,"\"errno\":-20")){
 			$res=array('errno'=>-20,'errmsg'=>'百度云限制短时间内解析不得超过三次！');
 			echo json_encode($res);
 		}
 		echo $response;
		return;
	}
	else{
		$res=array('errno'=>55,'errmsg'=>$errmsg);
		echo json_encode($res);
		return;
	}
}
else {
	$res=array('errno'=>$errno,'errmsg'=>$errmsg);
	echo json_encode($res);
}
?>