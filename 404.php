<?php
//var_dump($_SERVER);exit;
set_time_limit(0);
$request_uri = $_SERVER['REQUEST_URI'];
$request_uri = preg_replace('/\?.*/', '', $request_uri);
$uri = $request_uri;//获取访问url地址
$org_site = "http://sz.to8to.com/";
$uri = $org_site . $uri;//访问地址没有，需要访问原来的网站的改项目的地址。
//var_dump($request_uri);
$file_save = __DIR__ . $request_uri;
if (substr($file_save, -1) == '/') {
//    @mkdir($file_save, 0777, true);//当没有文件夹时建立文件夹
    $file_save .= "index.html";
}

@mkdir(dirname($file_save), 0777, true);//当没有文件夹时建立文件夹

$header = array(
    "Pragma" => 'no-cache',
    "Connection" => 'keep-alive',
    "Accept-Encoding" => 'gzip, deflate, sdch',
    "Upgrade-Insecure-Requests" => '1',
    "Accept" => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
    'Accept-Language' => 'zh-CN,zh;q=0.8,en;q=0.6',
);

//echo $uri;
$content = http_request($uri, 6000, $header);
$content = str_replace($org_site,'',$content);
//var_dump($content);exit;
if ($content) {
    echo $content;
    file_put_contents($file_save, $content);
} else {
    $uri = $request_uri;
    $uri = $org_site . $uri;
    $content = http_request($uri, 6000, $header);
    $content = str_replace($org_site,'',$content);

    echo $content;

    if ($content) {
        file_put_contents($file_save, $content);
    }
}

function http_request($url, $timeout = 30, $header = array()) {

    return trim(file_get_contents($url));
    $cookie_file = tempnam('./temp', 'cookie');

    if (!function_exists('curl_init')) {
        throw new Exception('server not install curl');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

    if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        var_dump($header);
    }
    $data = curl_exec($ch);
    list($header, $data) = explode("\r\n\r\n", $data);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        $matches = array();
        preg_match('/Location:(.*?)\n/', $header, $matches);
        $url = trim(array_pop($matches));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $data = curl_exec($ch);
    }

    if ($http_code != 200) {
        return false;
    }

    if ($data == false) {
        curl_close($ch);
    }
    @curl_close($ch);
    return $data;
}
