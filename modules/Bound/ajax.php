<?php
/**
 * Created by PhpStorm.
 * User: timmyxu
 * Date: 14/11/9
 * Time: 上午12:04
 */
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
global $wxdb;
$openid = $_POST['openid'];
$stuid = $_POST['stuid'];
$identify = $_POST['identify'];
if($identify != "" && $stuid != "")
{
    $today = time('c');
    $sql = $wxdb->prepare("SELECT * FROM user WHERE userId = " . $stuid, $today);
    $wxdb->query($sql);
    $result = $wxdb->last_result;
    $num = $wxdb->num_rows;
    if ($num != 0) {
        $row = $result[0];
        if ($row->identifyId == $identify) {
            $result = $wxdb->update(
                'user',
                array(
                    'openid'=>$openid
                ),
                array(
                    'userId'=>$stuid
                )
            );
            if ($result === false)
                echo 0;
            else
                echo 1;
        } else {
            echo 2;
        }
    } else {
        echo 2;
    }
}
?>