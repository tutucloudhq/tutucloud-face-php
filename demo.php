<?php
/*
 * TUTUCLOUD 人脸服务 API 调用示例
 */

// 公有 key
define('API_KEY', '');
// 私有 key
define('API_SECRET', '');

require 'tutucloud/face.php';

try {
    // 图片路径或图片 URL
    $file = 'https://files.tusdk.com/img/faces/f-dd1.jpg';
    // $file = 'test.webp';
    

    // 实例化 Face
    $face = new Face($file);

    // 人脸检测
    $faceData = $face->request('analyze/detection');
    print_r($faceData);

    // 人脸标点
    $faceMarks = $face->request('analyze/landmark', array('type' => 5));
    print_r($faceMarks);

} catch (Exception $e) {
    echo $e->getMessage();
}
