<?php
/**
 * 获取当前页面完整URL地址
 */
function get_url()
{
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
}

/**
 * 通过url取json数据
 */
function getAPI($url='http://www.baidu.com/') {
    // https://github.com/Mashape/unirest-php
    vendor('Unirest.Unirest');
    $resp = Request::get($url);
    return $resp->raw_body;
}
// 来自微博客户端
function is_weibo()
{
    $userAgent = strtolower($_SERVER["HTTP_USER_AGENT"]);
    if (strpos($userAgent, 'weibo') !== false) {
        return true;
    }
    return false;
}
// 是否来自微信客户端
function is_wechat()
{
    $userAgent = strtolower($_SERVER["HTTP_USER_AGENT"]);
    if (strpos($userAgent, 'micromessenger') !== false) {
        return true;
    }
    return false;
}
//是否手机
function is_mobile()
{
    if(!isset($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = Array("240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi", "android", "anywhereyougo.com", "applewebkit/525", "applewebkit/532", "asus", "audio", "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu", "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ", "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi", "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "ipod", "jbrowser", "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo", "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-", "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia", "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-", "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo", "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank", "sony", "spice", "sprint", "spv", "symbian", "tablet", "talkabout", "tcl-", "teleca", "telit", "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin", "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce", "wireless", "xda", "xde", "zte");
    $is_mobile = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}

if(!function_exists('get_real_ip')) {
    function get_real_ip() {
        //网速CDN客户端IP
        if(isset($_SERVER["HTTP_CDN_SRC_IP"])) {
            $ip = $_SERVER["HTTP_CDN_SRC_IP"];
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        return $ip;
    }
}


//验证是否是手机号
function is_phone_number($number){
    //$pattern = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';
    $pattern = '/^1[3|4|5|7|8][0-9]{9}$/';
    return preg_match($pattern,$number);
}

//验证是否为邮箱账号
function is_email($email){
    //$pattern = '/^[a-z]([a-z0-9]*[-_]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?$/i';
    $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    return preg_match($pattern,$email);
}

//发送手机验证码
function send_mms(){
    return rand(1111,9999);
}

// 格式化字符串
function format() {
	$args = func_get_args();
	if (count($args) == 0) {
		return;
	}
	if (count($args) == 1) {
		return $args[0];
	}
	$str = array_shift($args);
	$str = preg_replace_callback('/\\{(0|[1-9]\\d*)\\}/', create_function('$match', '$args = '.var_export($args, true).'; return isset($args[$match[1]]) ? $args[$match[1]] : $match[0];'), $str);
	return $str;
}

// 生成guid
function guid(){
	if (function_exists('com_create_guid')){
		return com_create_guid();
	}else{
		mt_srand((double)microtime()*10000);
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = ''
		.substr($charid, 0, 8);//.$hyphen
//		.substr($charid, 8, 4).$hyphen
//		.substr($charid,12, 4).$hyphen
//		.substr($charid,16, 4).$hyphen
//		.substr($charid,20,12);
		return $uuid;
	}
}

function generate_unique_time_string() {
	$time = explode(' ', microtime());

	$local = localtime($time[1], true);
	return str_pad(format('{0}{1}{2}{3}{4}{5}{6}', 1900 + $local['tm_year'], str_pad(($local['tm_mon'] + 1), 2, '0', STR_PAD_LEFT), str_pad($local['tm_mday'], 2, '0', STR_PAD_LEFT), str_pad($local['tm_hour'], 2, '0', STR_PAD_LEFT), str_pad($local['tm_min'], 2, '0', STR_PAD_LEFT), str_pad($local['tm_sec'], 2, '0', STR_PAD_LEFT), round($time[0] * 1000000)), 20, '0', STR_PAD_RIGHT);
}

function getservtime() {
	date_default_timezone_set('Asia/Shanghai');
	return date('Y-m-d H:i:s');
}
/**
 * 取字符串首字母
 * @param  string $str
 * @return string|NULL
 */
function getFirstCharter($str){

    if(empty($str)){return '';}

    $fchar=ord($str{0});

    if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});

    $s1=iconv('UTF-8','gb2312',$str);

    $s2=iconv('gb2312','UTF-8',$s1);

    $s=$s2==$str?$s1:$str;

    $asc=ord($s{0})*256+ord($s{1})-65536;

    if($asc>=-20319&&$asc<=-20284) return 'A';

    if($asc>=-20283&&$asc<=-19776) return 'B';

    if($asc>=-19775&&$asc<=-19219) return 'C';

    if($asc>=-19218&&$asc<=-18711) return 'D';

    if($asc>=-18710&&$asc<=-18527) return 'E';

    if($asc>=-18526&&$asc<=-18240) return 'F';

    if($asc>=-18239&&$asc<=-17923) return 'G';

    if($asc>=-17922&&$asc<=-17418) return 'H';

    if($asc>=-17417&&$asc<=-16475) return 'J';

    if($asc>=-16474&&$asc<=-16213) return 'K';

    if($asc>=-16212&&$asc<=-15641) return 'L';

    if($asc>=-15640&&$asc<=-15166) return 'M';

    if($asc>=-15165&&$asc<=-14923) return 'N';

    if($asc>=-14922&&$asc<=-14915) return 'O';

    if($asc>=-14914&&$asc<=-14631) return 'P';

    if($asc>=-14630&&$asc<=-14150) return 'Q';

    if($asc>=-14149&&$asc<=-14091) return 'R';

    if($asc>=-14090&&$asc<=-13319) return 'S';

    if($asc>=-13318&&$asc<=-12839) return 'T';

    if($asc>=-12838&&$asc<=-12557) return 'W';

    if($asc>=-12556&&$asc<=-11848) return 'X';

    if($asc>=-11847&&$asc<=-11056) return 'Y';

    if($asc>=-11055&&$asc<=-10247) return 'Z';

    return null;

}
/**
 * 底部常用分页模块，样式由pager.css?1控制，记得引入到页面
 * @author lly
 * @param $page，当前页
 * @param $count，总数
 * @param int $size
 * @param array $params, GET以外的自定义参数
 */
function genCommonPager($page, $count, $size=10, $params=array()) {
    $page = intval($page);
    $count = intval($count);
    $size = intval($size);
    $pageCount = ceil($count/$size); // 总页数
    if ($pageCount <= 1) return false;

    parse_str($_SERVER['QUERY_STRING'], $get); // 序列化参数转存为数组
    unset($get['page']);
    $get = array_merge($get, $params);

    $base_url = $_SERVER['PHP_SELF'] .'?'. http_build_query($get) . '&page=';

    $show_size = 7;

    $str = '';
    // 上一页
    if($page>1) {
        $prev_url = $base_url . ($page-1);
        $str .= "<a class='a_pre' href='{$prev_url}'>上一页</a>";
    } else {
        $str .= "<span class='disable'>上一页</span>";
    }

    if($pageCount < $show_size) {
        for($i=1; $i<=$pageCount; $i++) {
            if($page===$i) {
                $str .= "<span class='on'>{$i}</span>";
            } else {
                $page_url = $base_url . $i;
                $str .= "<a class='a_href' href='{$page_url}'>{$i}</a>";
            }
        }
    } else {

        // 首页
        if($page===1) {
            $str .= "<span class='on'>1</span>";
        } else {
            $page_url = $base_url . 1;
            $str .= "<a class='a_first' href='{$page_url}'>1</a>";
        }
        if($page > 5) {
            $str .= "<span class='dot'>...</span>";
        }

        if($page < 6) {
            $start = 1;
        } else {
            $start = $page - 3;
        }
        if($page > ($pageCount-5)) {
            $end = $pageCount;
        } else {
            $end = $page + 4;
        }
        for($j=$start; $j<$end; $j++) {
            if($j!==1 && $j!==$pageCount) {//避免重复输出1和最后一页
                if($j===$page) {
                    $str .= "<span class='on'>{$j}</span>";
                } else {
                    $page_url = $base_url . $j;
                    $str .= "<a class='a_href' href='{$page_url}'>{$j}</a>";
                }
            }
        }

        if($page < ($pageCount-5)) {
            $str .= "<span class='dot'>...</span>";
        }
        // 尾页
        if($page===$pageCount) {
            $str .= "<span class='on'>{$pageCount}</span>";
        } else {
            $page_url = $base_url . $pageCount;
            $str .= "<a class='a_last' href='{$page_url}'>{$pageCount}</a>";
        }
    }

    if($page >= $pageCount) {
        $str .= "<span class='disable'>下一页</span>";
    } else {
        $next_url = $base_url . ($page+1);
        $str .= "<a class='a_next' href='{$next_url}'>下一页</a>";
    }
    // 直接跳转页面
    $str .= "<form class='href' action='{$_SERVER['PHP_SELF']}'><label for='pageText'>到第</label>";
    foreach ($get as $name=>$val) {
        $str .= "<input type='hidden' class='a_text' name='{$name}' value='{$val}'>";
    }
    $str .= "<input autocomplete='off' type='text' class='a_text' name='page' value='{$page}'>" .
        "<label for='pageText'>页</label><input type='submit' value='转到' class='a_button'></form>";
    return $str;
}

/**
 * 简易分页模块，样式由pager.css?1控制，记得引入到页面
 * @author lly
 * @param $page，当前页
 * @param $count，总数
 * @param int $size
 * @param array $params, GET以外的自定义参数
 */
function getSimplePager($page, $count, $size=10, $params=array()) {
    $page = intval($page);
    $count = intval($count);
    $size = intval($size);
    $pageCount = ceil($count/$size); // 总页数
    if ($pageCount <= 1) return false;

    parse_str($_SERVER['QUERY_STRING'], $get); // 序列化参数转存为数组
    unset($get['page']);
    $get = array_merge($get, $params);

    $base_url = $_SERVER['PHP_SELF'] .'?'. http_build_query($get) . '&page=';

    $str = "共{$count}条<span>{$page}/{$pageCount}</span>";
    if($page>1) {
        $prev_url = $base_url . ($page-1);
        $str .= "<a class='a_pre' href='{$prev_url}'>上一页</a>";
    } else {
        $str .= "<span class='disable'>上一页</span>";
    }
    if($page >= $pageCount) {
        $str .= "<span class='disable'>下一页</span>";
    } else {
        $next_url = $base_url . ($page+1);
        $str .= "<a class='a_next' href='{$next_url}'>下一页</a>";
    }
    return $str;
}

function int_to_string(&$data,$map=array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用'))) {
    if($data === false || $data === null ){
        return $data;
    }
    $data = (array)$data;
    foreach ($data as $key => $row){
        foreach ($map as $col=>$pair){
            if(isset($row[$col]) && isset($pair[$row[$col]])){
                $data[$key][$col.'_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}


/**
 * 功能：邮件发送函数
 * @param string $to 目标邮箱
 * @param string $subject 邮件主题（标题）
 * @param string $to 邮件内容
 * @return bool true
 */
function sendMail($to, $subject, $content,$file=array()) {
    $mail_config = array(
        'MAIL_SMTP'         =>  TRUE,
        'MAIL_HOST'         =>  'smtp.126.com',           //邮件发送SMTP服务器
        'MAIL_SMTPAUTH'     =>  TRUE,
        'MAIL_USERNAME'     =>  'liulinyan521@126.com', //SMTP服务器登陆用户名
        'MAIL_PASSWORD'     =>  'liulinyan',              //SMTP服务器登陆密码
        'MAIL_SECURE'       =>  'tls',
        'MAIL_CHARSET'      =>  'utf-8',
        'MAIL_ISHTML'       =>  TRUE,
        'MAIL_FROMNAME'     =>  '公司内部邮件',
    );

    //注意这里的大小写哦，不然会出现找不到类，PHPMailer是文件夹名字，class#phpmailer就代表class.phpmailer.php文件名
    vendor('PHPMailer.class#smtp');
    vendor('PHPMailer.class#phpmailer');
    $mail = new PHPMailer();
    // 装配邮件服务器
    if ($mail_config['MAIL_SMTP']) {
        $mail->IsSMTP();
    }
    $mail->Host = $mail_config['MAIL_HOST'];  //这里的参数解释见下面的配置信息注释
    $mail->SMTPAuth = $mail_config['MAIL_SMTPAUTH'];
    $mail->Username = $mail_config['MAIL_USERNAME'];
    $mail->Password = $mail_config['MAIL_PASSWORD'];
    $mail->SMTPSecure = $mail_config['MAIL_SECURE'];
    $mail->CharSet = $mail_config['MAIL_CHARSET'];
    // 装配邮件头信息
    $mail->From = $mail_config['MAIL_USERNAME'];
    $mail->AddAddress($to);
    if(!empty($file)){
    	foreach($file as $filepath)
    	{
    		$mail->AddAttachment($filepath); // 添加附件(注意：路径不能有中文)
    	}
    }
    $mail->FromName = $mail_config['MAIL_FROMNAME'];
    $mail->IsHTML($mail_config['MAIL_ISHTML']);
    // 装配邮件正文信息
    $mail->Subject = $subject;
    $mail->Body = $content;


    // 发送邮件
    if (!$mail->Send()) {
        return FALSE;
    } else {
        return TRUE;
    }
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = NULL,$format='Y-m-d H:i'){
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

function curlData($url,$data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    $body = json_decode($data, true);
    return $body;
}

//裸钻价格bossapi中curl接口,其实和上面一样
function httpCurl($url, $post = '') {
	$url = trim($url);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if (!empty($post)) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	return curl_exec($ch);
}

//时间转换
function time_tran($the_time) {  

    $dur = time() - $the_time;  
    if ($dur < 0) {  
        return date('Y-m-d',$the_time);  
    } else {  
        if ($dur < 60) {
            $dur = $dur<1 ? 1 : $dur;
            return $dur . '秒前';  
        } else {  
            if ($dur < 3600) {  
                return floor($dur / 60) . '分钟前';  
            } else {  
                if ($dur < 86400) {  
                    return floor($dur / 3600) . '小时前';  
                } else {  
                    if ($dur < 259200) {//3天内  
                        return floor($dur / 86400) . '天前';  
                    } else {  
                        return date('Y-m-d',$the_time);  
                    }  
                }  
            }  
        }  
    }  
}
function trimall($str)//删除空格
{
    $qian=array(" ","　","\t","\n","\r","&nbsp;","   ");
    $hou=array("","","","","","","");
    return str_replace($qian,$hou,$str);
}
//文字截取
function subtext($text, $length)
{
    if(mb_strlen($text, 'utf8') > $length)
        return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}

function timediff($begin_time,$end_time)
{
      if($begin_time < $end_time){
         $starttime = $begin_time;
         $endtime = $end_time;
      }else{
         $starttime = $end_time;
         $endtime = $begin_time;
      }
      //计算天数
      $timediff = $endtime-$starttime;
      $days = intval($timediff/86400);
      //计算小时数
      $remain = $timediff%86400;
      $hours = intval($remain/3600);
      //计算分钟数
      $remain = $remain%3600;
      $mins = intval($remain/60);
      //计算秒数
      $secs = $remain%60;
      $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
      return $res;
}


/* 
 * 递归重组节点信息
 * @param $node 要重组的节点数组
 * @param $pid 父级ID
 * @return
 */
function node_regroup($node, $pid = 0, $access = null) {
    $arr = array();
    foreach($node as $v) {
        if(is_array($access)) {
            $v['access'] = in_array($v['id'], $access) ?  1 : 0;//判断是否已经拥有权限
        }
        if($v['pid'] == $pid) {
            $v['child'] = node_regroup($node, $v['id'], $access);
            $arr[] = $v;
        }
    }
    return $arr;
}
?>
