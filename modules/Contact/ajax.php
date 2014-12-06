<?php
/**
 * Ajax page for contact query module.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

if (isset($_POST["action"])) {
    $return_dict = array();
    global $wxdb; /* @var $wxdb wxdb */

    switch ($_POST["action"]) {
        case "add-record": {
            $results = $wxdb->get_results("SELECT * FROM contact WHERE userName = '" . $_POST["user_name"] . "'", ARRAY_A);
            if ($results != null) {
                $return_dict["code"] = 1;
                $return_dict["message"] = "already exist";
                break;
            } else {
                $result = $wxdb->insert("contact", array(
                    "userName"=>$_POST["user_name"],
                    "identity"=>$_POST["identity"],
                    "phoneNumber"=>$_POST["phone_number"],
                    "email"=>$_POST["email"]));
                if ($result != false) {
                    if ($result != 0) {
                        $return_dict["code"] = 0;
                        $return_dict["message"] = "success";
                        $return_dict["id"] = $wxdb->insert_id;
                    } else {
                        $return_dict["code"] = 2;
                        $return_dict["message"] = "already added";
                    }
                } else {
                    $return_dict["code"] = 3;
                    $return_dict["message"] = "error";
                }
            }
            break;
        }
        case "delete-record": {
            $result = $wxdb->delete("contact", array("id"=>$_POST["id"]));
            if ($result != false) {
                if ($result != 0) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                } else {
                    $return_dict["code"] = 1;
                    $return_dict["message"] = "already deleted";
                }
            } else {
                $return_dict["code"] = 2;
                $return_dict["message"] = "error";
            }
            break;
        }
        case "edit-user-name": {
            if ($_POST["user_name"] == "") {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo "姓名不能为空";
                exit;
            }
            $results = $wxdb->get_results("SELECT * FROM contact WHERE userName = '" . $_POST["user_name"] . "'", ARRAY_A);
            if ($results != null) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo "已存在姓名相同的记录";
                exit;
            } else {
                if ($wxdb->update("contact", array("userName"=>$_POST["user_name"]), array("id"=>$_POST["id"])) != false) {
                    $return_dict["code"] = 0;
                    $return_dict["message"] = "success";
                } else {
                    $return_dict["code"] = 2;
                    $return_dict["message"] = "error";
                }
            }
            break;
        }
        case "edit-identity": {
            if ($wxdb->update("contact", array("identity"=>$_POST["identity"]), array("id"=>$_POST["id"])) != false) {
                $return_dict["code"] = 0;
                $return_dict["message"] = "success";
            } else {
                $return_dict["code"] = 1;
                $return_dict["message"] = "error";
            }
            break;
        }
        case "edit-phone-number": {
            if (!preg_match("/^1[3|4|5|7|8]\\d{9}$/", $_POST["phone_number"])) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo "手机号格式不正确";
                exit;
            }
            if ($wxdb->update("contact", array("phoneNumber"=>$_POST["phone_number"]), array("id"=>$_POST["id"])) != false) {
                $return_dict["code"] = 0;
                $return_dict["message"] = "success";
            } else {
                $return_dict["code"] = 1;
                $return_dict["message"] = "error";
            }
            break;
        }
        case "edit-email": {
            if (!preg_match("/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/", $_POST["email"])) {
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
                echo "邮箱格式不正确";
                exit;
            }
            if ($wxdb->update("contact", array("email"=>$_POST["email"]), array("id"=>$_POST["id"])) != false) {
                $return_dict["code"] = 0;
                $return_dict["message"] = "success";
            } else {
                $return_dict["code"] = 1;
                $return_dict["message"] = "error";
            }
            break;
        }
        case "edit-format": {
            _set_value("Contact", "output_format", $_POST["format"]);
            $return_dict["code"] = 0;
            $return_dict["message"] = "success";
            break;
        }
        default: {
            $return_dict["code"] = 1;
            $return_dict["message"] = "error";
            break;
        }
    }
    echo json_encode($return_dict);
}