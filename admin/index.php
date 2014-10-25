<?php
/**
 * The index page of Admin Panel.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/includes/admin.php';

if (is_logged_in() == false) {
    redirect('login.php');
    exit;
}

if (is_disabled()) {
    redirect('account-disabled.php');
    exit;
}

if (isset($_POST['submit'])) {
    $data = $_POST;
    foreach ($data as $key => $value) {
        set_value(new $_GET['page'], $key, $value);
    }
}

?>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE">
    <link rel="stylesheet" href="../includes/css/admin.css" media="all">
    <script type="text/javascript" src="../includes/js/jquery/jquery-2.1.1.js"></script>
    <title>管理后台</title>
</head>
<body>

<div id="wrapper">
    <div id="main">
        <div id="navigation">
            <nav class="global">
                <?php list_global_setting_items() ?>
            </nav>
            <nav class="modules">
                <?php list_module_setting_items() ?>
            </nav>
        </div>
        <div id="content">
            <?php include_settings($_GET['page']) ?>
        </div>
    </div>
</div>

</body>
</html>