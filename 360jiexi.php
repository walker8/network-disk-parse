<?php
//php版本>5.3 <=5.3请自行修复错误
//1.暂不支持对大文件的解析
//2.暂不支持对文件夹的解析
//如需支持大文件和文件夹的解析，需将$mycookie改成你的360云盘Cookie值
//header("Content-type: text/html; charset=utf-8");
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
			return "";
	}
	else
		return "";
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

//现在360云盘对大文件的下载需登录自己的账号才能下载，$mycookie请登录你的云盘账号后手动获取
//如需使用，推荐使用自己的小号，避免账号注销导致Cookie失效
//示例(本Cookie已失效，仅供参考)：$mycookie="Q=u%sfddhl2012%26n%3Dnvonml%26le%3DAmxmZQLkBQN0WGDjpKRhL29g%26m%3D%26qid%3D176381070%26im%3D1_t01fa0209b27c9b22ae%26src%3Dpcw_cloud%26t%3D1; T=s%3D5db4e54169017be2264fc1866708cce7%26t%3D1458217971%26lm%3D%26lf%3D1%26sk%3D4896635565b3c2e94c446e00433f047f%26mt%3D1458217971%26rc%3D%26v%3D2.0%26a%3D1";
$mycookie="";
$errmsg='';//失败原因
$errno=55;//错误代号
$cookie='';//cookie值
//$url="http://yunpan.cn/cFhEJLIGPa2PW";
$url='';
$password='';
if(!empty($_GET["url"]))
{
	$aurl=trim($_GET["url"]);
	$aurl=urldecode($aurl);
	$urlarr=explode(' ', $aurl);
	$url=$urlarr[0];
	$url=str_replace("https","http",$url);
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
$options = array(
		CURLOPT_FOLLOWLOCATION => TRUE,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0',
		CURLOPT_HEADER => TRUE,//将头文件的信息作为数据流输出
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => TRUE, //接收服务端范围的html代码而不是直接浏览器输出
		CURLOPT_TIMEOUT => 30,
);
curl_setopt_array($ch, $options);
$content = curl_exec($ch); //执行curl并赋值给$content
$errmsg=curl_error($ch);
$content=str_replace(" ","",$content);
curl_close($ch);
if(strpos($content,"分享者已取消此分享，或删除了分享的文件"))
{
	$errmsg='Oh,NO!该链接已经失效了...';
	$res=array("errno"=>$errno,"errmsg"=>$errmsg);
	echo json_encode($res);
	return;
}
if(strpos($content,"请输入提取码"))//需要访问密码
{
	//echo '进入提取码识别<br>';
	$vurl = "http://c43.yunpan.360.cn/share/verifyPassword";
	$curlObj = curl_init();
	$strarr = explode("/",$url);
	$shorturl=end($strarr);
	$data=array(
			'shorturl'      =>$shorturl,
			'linkpassword'  =>$password,
	);
	$options = array(
			CURLOPT_HEADER => TRUE,//将头文件的信息作为数据流输出
			CURLOPT_URL => $vurl,
			CURLOPT_REFERER => $vurl,
			CURLOPT_POST => TRUE, //使用post提交
			CURLOPT_RETURNTRANSFER => TRUE, //接收服务端范围的html代码而不是直接浏览器输出
			CURLOPT_TIMEOUT => 4,
			CURLOPT_POSTFIELDS => http_build_query($data), //post的数据
	);
	curl_setopt_array($curlObj, $options);
	$response = curl_exec($curlObj);
	$errmsg=curl_error($curlObj);
	curl_close($curlObj);
	if(strstr($response,"\"errno\":0"))//POST成功了
	{
		$content=str_replace(' ', '', $response);//删除所有空格
		$cookie=Search_String($response, 'Set-Cookie:', '{');
		//这里的Cookie重新设置一下是为了兼容SAE
		$cookie=explode(';', $cookie)[0].';';
		$ch = curl_init(); //初始化
		$options = array(
				CURLOPT_HEADER => TRUE,//将头文件的信息作为数据流输出
				CURLOPT_FOLLOWLOCATION => TRUE,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0',
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => TRUE, //接收服务端范围的html代码而不是直接浏览器输出
				CURLOPT_TIMEOUT => 30,
				CURLOPT_COOKIE =>$cookie //带Cookie提交数据
		);
		curl_setopt_array($ch, $options);
		$content = curl_exec($ch); //执行curl并赋值给$content
		$errmsg=curl_error($ch);
		$content=str_replace(" ","",$content);
	}
	else if(strstr($response,"\"errno\":")){
		echo '{'.Search_String($response, '{', '}').'}';
		return;
	}
	else{
		$res=array('errno'=>55,'errmsg'=>$errmsg);
		echo json_encode($res);
		return;
	}
}
//成功获取分享页面信息后继续分析
if(strstr($content,"varrootFileList"))//文件夹
{
	//echo '进入文件夹解析<br>';
	if(empty($mycookie)){
		$res=array('errno'=>'5','errmsg'=>'暂不支持对文件夹的解析');
		echo json_encode($res);
		return;
	}
	$nid = Search_string($content, "data:[{\"nid\":\"", "\"");
	$name = Search_string($content, "name:'", "'");
	$surl=Search_string($content, "surl:'", "'");
	//echo $nid.'<br>'.$surl;
	//这里的Cookie必须重新设置一下，否则下面的Header无法设置成功
	$cookie=explode(';', $cookie)[0].';';//这里的Cookie不对
	$cookie=$cookie.$mycookie;
	$downurl='http://c11.yunpan.360.cn/share/downloadfile?shorturl='.$surl.'&nid='.$nid.'&download_permit_token=';
    $headers = array(
	    'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 Firefox/3.0',
	    'Referer: http://f1bd66.l11.yunpan.cn/lk/cFhEJLIGPa2PW',
		'Content-type:application/x-www-form-urlencoded',
	);
	$ch = curl_init($downurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);//Header无法改变和$cookie取值有关，晕死了。。。
	//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	$response = curl_exec($ch);
	$errmsg=curl_error($ch);
	curl_close($ch);
	if(strstr($response,"errno")){//终于成功了
		$size=0;
		$res=array();
		$add=array('filename'=>$name,'size'=>$size);//补充信息
		$res=object_array(json_decode($response));
		$res=array_merge($res,$add);
		echo json_encode($res);
	}
	else{
		$res=array('errno'=>$errno,'errmsg'=>$errmsg);
		echo json_encode($res);
	}
}
else if(strstr($content,"varSYS_CONF"))//普通文件 文件夹
{
	//echo '进入普通文件解析<br>';
	$nid = Search_string($content, "nid:'", "'");
    $email = Search_string($content, "email:'", "'");
    $name = Search_string($content, "name:'", "'");
    $surl=Search_string($content, "surl:'", "'");
    $download_permit_token=Search_string($content, "download_permit_token:'", "'");
    if($download_permit_token==""&&empty($mycookie))
    {
    	$res=array('errno'=>'5','errmsg'=>'暂不支持对大文件的解析');
		echo json_encode($res);
		return;
    }
    //echo $nid.'<br>'.$email.'<br>'.$surl;
    //$cookie='user_visit_token_cHA8QjbyxkN5e=83850b9685cb9faef7337055d928f88c.1449369079; path=/; domain=yunpan.cn';
    //这里的Cookie必须重新设置一下，否则下面的Header无法设置成功
    $cookie=explode(';', $cookie)[0].';';
    $cookie=$cookie.$mycookie;
    $downurl='http://c11.yunpan.360.cn/share/downloadfile?shorturl='.$surl.'&nid='.$nid.'&download_permit_token='.$download_permit_token;
    $headers = array(
	    'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 Firefox/3.0',
	    'Referer: http://c43.yunpan.360.cn/share/downloadfile/',
		'Content-type:application/x-www-form-urlencoded',
	);
	$ch = curl_init($downurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);//Header无法改变和$cookie取值有关，晕死了。。。
	$response = curl_exec($ch);
	$errmsg=curl_error($ch);
	curl_close($ch);
    if(strstr($response,"errno")){//终于成功了
    	//echo '终于成功了<br>';
	    $size=Search_String($response, '&fsize=', '&');
	    $res=array();
	    $add=array('filename'=>$name,'email'=>$email,'size'=>(int)$size);//补充信息
	    $res=object_array(json_decode($response));
	    $res=array_merge($res,$add);
	    echo json_encode($res);
    }
    else{
    	$res=array('errno'=>$errno,'errmsg'=>$errmsg);
    	echo json_encode($res);
    }
}
else {
	$res=array('errno'=>$errno,'errmsg'=>$errmsg);
	echo json_encode($res);
}
?>