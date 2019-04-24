<?php
/**
 *
 * @since   2017-11-01
 * @author  zhaoxiang <zhaoxiang051405@gmail.com>
 */

namespace app\util;


class Tools {
  /**
   * 请求成功
   * @param  string $code 状态码
   * @param  string $msg  返回信息
   * @param  array  $data 返回数据
   * @return [type]       [description]
   */
  public static function buildSuccess($code='200',$msg='操作成功',$data=[]){
    //return json_encode(array('code'=>$code,'msg'=>$msg,'data'=>$data));
    return array('code'=>$code,'msg'=>$msg,'data'=>$data);
  }

  /**
   * 请求失败
   * @param  string $code 状态码
   * @param  string $msg  返回信息
   * @param  array  $data 返回数据
   * @return [type]       [description]
   */
  public static function buildFailed($code='-1',$msg='操作失败',$data=[]){
    //return json_encode(array('code'=>$code,'msg'=>$msg,'data'=>$data));
    return array('code'=>$code,'msg'=>$msg,'data'=>$data);
  }

  public static function getDate($timestamp) {
    $now = time();
    $diff = $now - $timestamp;
    if ($diff <= 60) {
      return $diff . '秒前';
    } elseif ($diff <= 3600) {
      return floor($diff / 60) . '分钟前';
    } elseif ($diff <= 86400) {
      return floor($diff / 3600) . '小时前';
    } elseif ($diff <= 2592000) {
      return floor($diff / 86400) . '天前';
    } else {
      return '一个月前';
    }
  }

  /**
   * 求两个日期之间相差的天数
   * (针对1970年1月1日之后，求之前可以采用泰勒公式)
   * @param string $day1
   * @param string $day2
   * @return number
   */
  public static function diffBetweenTwoDays($day1, $day2)
  {
    $second1 = strtotime($day1);
    $second2 = strtotime($day2);
    
    if ($second1 < $second2) {
      $tmp = $second2;
      $second2 = $second1;
      $second1 = $tmp;
    }
    return ($second1 - $second2) / 86400;
  }

  /**
   * 单位转换
   * @param  [type] $size [description]
   * @return [type]       [description]
   */
  public static function formatBytes($size) { 
    $units = array(' B', ' KB', ' MB', ' GB', ' TB'); 
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024; 
      return round($size, 2).$units[$i]; 
  }

  /**
   * 分页类
   * @param  [type] $maxpage [description]
   * @param  [type] $page    [description]
   * @param  [type] $listnum [description]
   * @param  string $para    [description]
   * @return [type]          [description]
   */
  public static function multipage($maxpage, $page, $listnum, $para = '') {
    $multipage = '';
    //$listnum = 15;    

    if ($maxpage < 2) {
      return '';
    }else{
      $offset = 2;
      if ($maxpage <= $listnum) {
        $from = 1;
        $to = $maxpage;
      } else {
        $from = $page - $offset;
        $to = $from + $listnum - 1;
        if($from < 1) {
          $to = $page + 1 - $from;
          $from = 1;
          if($to - $from < $listnum) {
            $to = $listnum;
          }
        } elseif($to > $maxpage) {
          $from = $maxpage - $listnum + 1;
          $to = $maxpage;
        }
      }

      $multipage .= ($page - $offset > 1 && $maxpage >= $page ? '<li><a href="?1'.$para.'" >1...</a></li>' : '').
      ($page > 1 ? '<li><a href="?'.($page - 1).$para.'" >&laquo;</a></li>' : '');

      for($i = $from; $i <= $to; $i++) {
        $multipage .= $i == $page ? '<li class="active"><a href="?'.$i.$para.'" >'.$i.'</a></li>' : '<li><a href="?'.$i.$para.'" >'.$i.'</a></li>';
      }

      $multipage .= ($page < $maxpage ? '<li><a href="?'.($page + 1).$para.'" >&raquo;</a></li>' : '').
      ($to < $maxpage ? '<li><a href="?'.$maxpage.$para.'" class="last" >...'.$maxpage.'</a></li>' : '');


      $multipage = $multipage ? '<ul class="pagination pagination-sm">'.$multipage.'</ul>' : '';
    }

    return $multipage;
  }

    /**
     * 二次封装的密码加密
     * @param $str
     * @param string $auth_key
     * @return string
     * @author zhaoxiang <zhaoxiang051405@gmail.com>
     */
    public static function userMd5($str, $auth_key = '') {
      if (!$auth_key) {
        $auth_key = config('config.AUTH_KEY');
      }

      return '' === $str ? '' : md5(sha1($str) . $auth_key);
    }

    /**
     * 判断当前用户是否是超级管理员
     * @param string $uid
     * @return bool
     * @author zhaoxiang <zhaoxiang051405@gmail.com>
     */
    public static function isAdministrator($uid = '') {
      if (!empty($uid)) {
        $adminConf = config('apiAdmin.USER_ADMINISTRATOR');
        if (is_array($adminConf)) {
          if (is_array($uid)) {
            $m = array_intersect($adminConf, $uid);
            if (count($m)) {
              return true;
            }
          } else {
            if (in_array($uid, $adminConf)) {
              return true;
            }
          }
        } else {
          if (is_array($uid)) {
            if (in_array($adminConf, $uid)) {
              return true;
            }
          } else {
            if ($uid == $adminConf) {
              return true;
            }
          }
        }
      }

      return false;
    }

    /**
     * 将查询的二维对象转换成二维数组
     * @param array $res
     * @param string $key 允许指定索引值
     * @return array
     * @author zhaoxiang <zhaoxiang051405@gmail.com>
     */
    public static function buildArrFromObj($res, $key = '') {
      $arr = [];
      foreach ($res as $value) {
        $value = $value->toArray();
        if ($key) {
          $arr[$value[$key]] = $value;
        } else {
          $arr[] = $value;
        }
      }

      return $arr;
    }

    /**
     * 将二维数组变成指定key
     * @param $array
     * @param $keyName
     * @author zhaoxiang <zhaoxiang051405@gmail.com>
     * @return array
     */
    public static function buildArrByNewKey($array, $keyName = 'id') {
      $list = array();
      foreach ($array as $item) {
        $list[$item[$keyName]] = $item;
      }
      return $list;
    }

    /**
       * curl模拟提交
       * @param  [type]  $url          访问的URL
       * @param  string  $post         post数据(不填则为GET)
       * @param  string  $referer      自定义来路
       * @param  string  $cookie       提交的$cookies
       * @param  integer $returnCookie 是否返回$cookies
       * @param  string  $ua           自定义UA
       * @return [type]                [description]
       */
    public static function curl_request($url,$post='',$referer='',$cookie='', $returnCookie=0,$ua='Mozilla/5.0 (Windows NT 6.1; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0'){
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_USERAGENT, $ua);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
      curl_setopt($curl, CURLOPT_TIMEOUT, 60);
      curl_setopt($curl, CURLOPT_REFERER, $referer);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      $httpheader[] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
      $httpheader[] = "Accept-Encoding:gzip, deflate";
      $httpheader[] = "Accept-Language:zh-CN,zh;q=0.9";
      $httpheader[] = "Connection:close";
      curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      if($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
      }
      if($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
      }
      curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
      curl_setopt($curl, CURLOPT_TIMEOUT, 10);
      curl_setopt($curl, CURLOPT_ENCODING, "gzip");
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      $data = curl_exec($curl);
      if (curl_errno($curl)) {
        return curl_error($curl);
      }
      curl_close($curl);
      if($returnCookie){
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie']  = substr($matches[1][1], 1);
        $info['content'] = $body;
        return $info;
      }else{
        return $data;
      }
    }

    /**
     * 获取网页状态码
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    public static function curl_StatusCode($url){
      $is_url = Tools::is_url($url);
      if (!$is_url) {
        return false;
      }
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_HEADER, 1);
      curl_setopt($curl,CURLOPT_NOBODY,true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      $data = curl_exec($curl);
      return curl_getinfo($curl,CURLINFO_HTTP_CODE);
      curl_close($curl);
    }


    /**
     * 判断是否为域名
     * @param  [type]  $str [description]
     * @return boolean      [description]
     */
    public static function is_url($str){
      return preg_match("/^((https?|ftp|news):\/\/)?([a-z]([a-z0-9\-]*[\.。])+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|vip|xyz)|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\/[a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&]*)?)?(#[a-z][a-z0-9_]*)?$/", $str);
    }

    /**
     * 判断是否为域名
     * 加强版
     * @param  [type]  $s [description]
     * @return boolean    [description]
     */
    public static function isUrl($s)  
    {  
      return preg_match('/^http[s]?:\/\/'.  
        '(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184  
        '|'. // 允许IP和DOMAIN（域名）  
        '([0-9a-z_!~*\'()-]+\.)*'. // 三级域验证- www.  
        '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域验证  
        '[a-z]{2,6})'.  // 顶级域验证.com or .museum  
        '(:[0-9]{1,4})?'.  // 端口- :80  
        '((\/\?)|'.  // 如果含有文件对文件部分进行校验  
        '(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$/',  
        $s) == 1;  
    }

    /**
     * 判断网址后否包含http || https
     * @param  [type]  $url [description]
     * @return boolean      [description]
     */
    public static function is_http($url){
      if(preg_match("/^(http:\/\/|https:\/\/).*$/",$url)){
        return 1;
      }else{
        return 0;
      }
    }

    /**
     * 取得根域名
     * @param [type] $domain [description]
     */
    public static function GetUrlToDomain($domain) {
      $re_domain = '';
      $domain_postfix_cn_array = array("com", "net", "org", "gov", "edu", "com.cn", "cn");
      $array_domain = explode(".", $domain);
      $array_num = count($array_domain) - 1;
      if ($array_domain[$array_num] == 'cn') {
        if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
          $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        } else {
          $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        }
      } else {
        $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
      }
      return $re_domain;
    }

    /**
     * daddslashes
     * @param  [type]  $string [description]
     * @param  integer $force  [description]
     * @param  boolean $strip  [description]
     * @return [type]          [description]
     */
    public static function daddslashes($string, $force = 0, $strip = FALSE) {
      !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
      if(!MAGIC_QUOTES_GPC || $force) {
        if(is_array($string)) {
          foreach($string as $key => $val) {
            $string[$key] = daddslashes($val, $force, $strip);
          }
        } else {
          $string = addslashes($strip ? stripslashes($string) : $string);
        }
      }
      return $string;
    }

  	 /**
     * IPv4正则匹配
     * 0.0.0.0-255.255.255.255
     * @param  [type] $ip [description]
     * @return [type]     [description]
     */
     public static function funip($ip){
      $pat = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))\.){3}((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))$/";
      if(preg_match($pat,$ip)){
        $num = preg_match($pat,$ip);
        return $num;
      }else{
        return false;
      }
    }

    /**
     * 提取主域名
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    public static function getParse_url($url){
      if(Tools::is_http($url)){
        return parse_url($url)['host'];
      }else{
        return parse_url('http://'.$url)['host'];
      }
    }

    /**
     * 清除正则(剩文字字母数字)
     * @param  [type] $chars    [description]
     * @param  string $encoding [description]
     * @return [type]           [description]
     */
    public static function match_chinese($chars,$encoding='utf8')
    {
      $pattern =($encoding=='utf8')?'/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u':'/[\x80-\xFF]/';
      preg_match_all($pattern,$chars,$result);
      $temp =join('',$result[0]);
      return $temp;
    }

    public static function getIp(){
      $unknown = 'unknown';
      if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown) ) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } elseif ( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
        $ip = $_SERVER['REMOTE_ADDR'];
      }
      $ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
      if (false !== strpos($ip, ','))
        $ip = reset(explode(',', $ip));
      return $ip;
    }


  }
