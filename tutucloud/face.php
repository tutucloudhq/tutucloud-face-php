<?php

/**
 * TUTUCLOUD 人脸服务API调用示例类
 *
 * @author TUTUCLOUD
 */
class Face {

    /**
     * API 服务地址 
     */
    const API_URL = 'https://api.tutucloud.com/v1/face/';

    /**
     * 公有 key
     */
    public $api_key = API_KEY;

    /**
     *  私有 key
     */
    public $api_secret = API_SECRET;

    /**
     * 请求参数
     * 
     * @var array
     */
    protected $params;

    public function __construct($file = null) {
        // 初始化参数列表, 设置公有 key
        $this->params = array(
            'api_key' => $this->api_key,
        );

        // 图片文件参数
        if (!is_null($file)) {
            $this->setFile($file);
        }
    }

    public function setFile($file) {

        if (filter_var($file, FILTER_VALIDATE_URL)) {
            $fileField = $this->params['img'] = $file;
        } elseif (is_file($file)) {
            $fileField = $this->params['img'] = $this->curl_file_create(realpath($file));
        } else {
            throw new Exception('file does not exist');
        }
        return $fileField;
    }

    /**
     * 请求接口
     * 
     * @param string $method 接口方法
     * @param array $params 请求参数
     * @return array
     */
    public function request($method, $params = null) {
        if (empty($this->api_secret) || empty($this->api_key)) {
            throw new Exception('api_secret and api_key is requried');
        }

        if (!isset($this->params['img'])) {
            throw new Exception('parameter img is requried');
        }

        $apiUrl = self::API_URL . $method;

        $postFields = is_array($params) ? array_merge($this->params, $params) : $this->params;

        //设置时间戳参数
        $postFields['t'] = time();

        //设置签名参数
        $postFields['sign'] = $this->signature($postFields);

        $response = $this->curlPost($apiUrl, $postFields);
        return json_decode($response, true);
    }

    /**
     * 参数签名
     * 
     * @param array $params
     * @return string
     */
    protected function signature($params) {
        //图片参数 pic 不参与签名
        if (!filter_var($params['img'], FILTER_VALIDATE_URL)) {
            unset($params['img']);
        }
        //参数名排序
        ksort($params);

        $signStr = '';

        //连接 参数名.参数值
        foreach ($params as $para => $value) {
            //参数名转为小写
            $signStr .= strtolower($para) . $value;
        }

        //排序后的字符串接上 私有 key
        $signStr .= $this->api_secret;

        //返回 md5 字符串
        return md5($signStr);
    }

    /**
     * curl post
     * 
     * @param string $url
     * @param array $postFields
     * @return string
     * @throws Exception
     */
    protected function curlPost($url, $postFields) {
        if (!function_exists('curl_init')) {
            throw new Exception('Does not support CURL function.');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 18600);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            throw new Exception($error);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 200 && $httpCode < 300) {
            return $response;
        }
        throw new Exception('curl http code: ' . $httpCode);
    }

    /**
     * 生成 CURLFile
     * 
     * @param type $filename
     * @param type $mimetype
     * @param type $postname
     * @return type
     */
    protected function curl_file_create($filename, $mimetype = '', $postname = '') {
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename, $mimetype, $postname);
        }
        //兼容处理
        return "@$filename;filename="
                . ($postname ? : basename($filename))
                . ($mimetype ? ";type=$mimetype" : '');
    }

}
