网盘直链解析 2015/12/11 更新
==========================

###用途：
如果你做资源站，或者需要用户下载自己写的程序，程序较大远远超出服务器的能力，这时你需要借助一些网盘来分享自己的资源，但你又不想麻烦用户到网盘分享页面下载文件，这时你就需要网盘直链解析了！网盘直链解析可以在服务端就直接解析出文件的真实下载地址，这样用户无需转跳就可以下载文件了，在用户看来，这跟直链没有任何区别！

###使用方法：
将360jiexi.php文件上传到自己的服务器(支持虚拟主机,VPS,SAE,其它的如BAE、ACE理论上应该都支持！)
如果你的域名是www.yourdomain.com，你将360jiexi.php上传到根目录，那么你可以这样调用它
注意%20是空格的URL编码，程序默认以空格作为分隔符！目前仅支持360云盘的直链解析
[有密码]分享地址为：http://yunpan.cn/Q4p3uUkmchPhI 提取码 5de2 
[有密码]http://www.yourdomain.com/360jiexi.php?url=http://yunpan.cn/Q4p3uUkmchPhI%205de2
[无密码]分享地址为：http://yunpan.cn/cFhEJLIGPa2PW 
[无密码]http://www.yourdomain.com/360jiexi.php?url=http://yunpan.cn/cFhEJLIGPa2PW
程序解析成功后会返回json数据

{"errno":0,"errmsg":"\u64cd\u4f5c\u6210\u529f","data":{"downloadurl":"http:\/\/sdl38.yunpan.cn\/share.php?method=Share.download&cqid=1a55c9cdb8022afee7cc763c68eadcf7&dt=38.106e46296eef7d54b808a53f0f019b06&e=1449975105&fhash=87314b228f55d409ea03dc5fe32a1161c951ad2a&fname=%25E5%2582%25AC%25E7%259C%25A0%25E6%2596%25B9%25E6%25B3%2595.doc&fsize=32768&nid=14211393804923662&st=233d0b79155128c31113deb1fc4f6e6f&xqid=176381070","params":[]},"filename":"\u50ac\u7720\u65b9\u6cd5.doc","email":"aibazy2012","size":32768}

参数说明：
errno：错误编号，0为无错误，即成功获取该文件的直链地址，其它则说明解析出错
errmsg：错误信息 utf-8编码
downloadurl：直链地址
filename：文件名
email：用户名
size：文件大小，单位为B

到这里，你已经成功了99%，接下来，无非就是对直链地址的提取
这里我给大家提供了一个demo程序down.php，你可以根据实际情况修改
参数可以如下：
http://www.yourdomain.com/down.php?url=Q4p3uUkmchPhI_5de2 (有密码)
http://www.yourdomain.com/down.php?url=cFhEJLIGPa2PW  (无密码)
这就是最后生成的外链地址了！

当然你也可以把它改成这样，让它变得更像直链！(仅供演示)
[http://so.ygyhg.com/down/Q4p3uUkmchPhI_5de2](http://so.ygyhg.com/down/Q4p3uUkmchPhI_5de2) 
[http://so.ygyhg.com/down/Q4p3uUkmchPhI_5de2.zip](http://so.ygyhg.com/down/Q4p3uUkmchPhI_5de2.zip) 
[http://so.ygyhg.com/down/cFhEJLIGPa2PW](http://so.ygyhg.com/down/cFhEJLIGPa2PW)
[http://so.ygyhg.com/down/cFhEJLIGPa2PW.doc](http://so.ygyhg.com/down/cFhEJLIGPa2PW.doc) 

然后你可以用下载工具(如迅雷)下载文件，甚至可以离线到自己的网盘(如百度云)

###演示站点：
网盘在线解析：[http://so.ygyhg.com/jiexi](http://so.ygyhg.com/jiexi)
api调用接口：[http://so.ygyhg.com/360jiexi.php?url=http://yunpan.cn/cFhEJLIGPa2PW](http://so.ygyhg.com/360jiexi.php?url=http://yunpan.cn/cFhEJLIGPa2PW)
以上站点仅供演示与测试，如需使用请将源码上传到自己的服务器！！！

###其他：
博客地址：[http://www.ygyhg.com/107.html](http://www.ygyhg.com/107.html)