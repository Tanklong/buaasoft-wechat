<?php
/**
 * Setting page for Homework Module
 *
 * @author Renfei Song
 */

$table_name = get_option('table');
$module_name = get_option('module');

if (isset($_POST['add-subject'])) {
    $subject = $_POST['subject'];
    if (empty($subject)) {
        redirect_failure('Empty form is not accepted.');
        exit;
    }
    $subjects = get_option('subjects');
    if (!isset($subjects)) {
        $subjects = array();
    }
    if (in_array($subject, $subjects)) {
        redirect_failure('Empty form is not accepted.');
        exit;
    }
    array_push($subjects, $subject);
    set_option('subjects', $subjects);
    redirect_success('已添加科目 ' . $subject);
    exit;
}

if (isset($_POST['delete-subject'])) {
    $subject = urldecode($_POST['subject']);
    if (empty($subject)) {
        redirect_failure('System failure - empty subject.');
        exit;
    }
    $subjects = get_option('subjects');
    if (!isset($subjects)) {
        redirect_failure('System failure -  cannot retrieve subjects.');
        exit;
    }
    if (($key = array_search($subject, $subjects)) !== false) {
        unset($subjects[$key]);
        set_option('subjects', $subjects);
        redirect_success('科目 ' . $subject . ' 已经删除。');
        exit;
    } else {
        redirect_failure('System failure - cannot find subject.');
        exit;
    }
}

if (isset($_POST['add-homework'])) {
    $publish_date = $_POST['publishDate'];
    $due_date = $_POST['dueDate'];
    $subject = $_POST['subject'];
    $content = $_POST['content'];
    $forClass = $_POST['for-class'];

    if (empty($publish_date) || empty($subject) || empty($content)) {
        redirect_failure('请填写完整表格。');
        exit;
    }

    if (!isset($forClass)) {
        redirect_failure('请至少选择一个班级。');
        exit;
    }

    $subjects = get_option('subjects');
    if (!isset($subjects))
        $subjects = array();
    if (!in_array($subject, $subjects)) {
        redirect_failure('科目填写无效。');
        exit;
    }

    $publish_date = validate_date($publish_date);
    if ($publish_date == false) {
        redirect_failure('作业发布日期无效。');
        exit;
    }

    if (!empty($due_date)) {
        $due_date = validate_date($due_date);
        if ($due_date == false) {
            redirect_failure('作业截止日期无效。');
            exit;
        }
    }

    $now_timestamp = time();
    $publish_timestamp = strtotime($publish_date);

    if ($publish_timestamp > $now_timestamp) {
        redirect_failure('发布日期不得晚于今天。');
        exit;
    }

    if (!empty($due_date)) {
        $due_timestamp = strtotime($due_date);
        if ($due_timestamp < $publish_timestamp) {
            redirect_failure('截止日期不得早于发布日期。');
            exit;
        }
    }

    global $wxdb; /* @var $wxdb wxdb */
    $wxdb->insert($table_name, array(
        'subject' => $subject,
        'content' => $content,
        'userName' => current_user_name(),
        'publishDate' => $publish_date,
        'dueDate' => $due_date,
        'forClass' => json_encode($forClass),
        'dateUpdated' => date('c')
    ));

    redirect_success('Homework added!');
    exit;
}

global $wxdb; /* @var $wxdb wxdb */

$sql = "SELECT * FROM `" . $table_name . "` ORDER BY homeworkId DESC";
$rows = $wxdb->get_results($sql, ARRAY_A);
$subjects = get_option('subjects');
if (!isset($subjects)) {
    $subjects = array();
}

// Get AJAX Key
$ajax_key = sha1(rand(111111, 999999));
set_option('ajax', $ajax_key);

function get_homework_count($subject) {
    global $wxdb; /* @var $wxdb wxdb */
    $table_name = get_option('table');
    $sql = $wxdb->prepare("SELECT count(*) FROM `" . $table_name . "` WHERE subject = '%s'", $subject);
    return $wxdb->get_var($sql);
}

function validate_date($date) {
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if ($dt !== false && !array_sum($dt->getLastErrors())) {
        return $dt->format('Y-m-d');
    }
    return false;
}

?>

<h2>作业管理面板</h2>

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#panel-add" role="tab" data-toggle="tab">添加作业</a></li>
    <li role="presentation"><a href="#panel-manage-homework" role="tab" data-toggle="tab">作业管理</a></li>
    <li role="presentation"><a href="#panel-manage-subject" role="tab" data-toggle="tab">科目管理</a></li>
</ul>

<div class="tab-content">
<div role="tabpanel" class="tab-pane fade in active" id="panel-add">
    <h3>添加作业</h3>
    <form method="POST" id="add-homework">
        <div class="form-group">
            <div class="prompt">
                <label for="publishDate">布置日期</label>
            </div>
            <div class="control">
                <input class="form-control date-picker" type="text" name="publishDate" id="publishDate" required>
            </div>
        </div>
        <div class="form-group">
            <div class="prompt">
                <label for="dueDate">截止日期</label>
            </div>
            <div class="control">
                <input class="form-control date-picker" type="text" name="dueDate" id="dueDate">
            </div>
        </div>
        <div class="form-group">
            <div class="prompt">
                <label for="subject">科目</label>
            </div>
            <div class="control">
                <select class="form-control" name="subject" data-placeholder="选择科目..." required>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject ?>"><?php echo $subject ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="prompt">
                <label>适用班级</label>
            </div>
            <div class="control">
                <input name="for-class[]" value="1" type="checkbox" class="class-option" id="class-1" checked><label for="class-1">一班</label>&nbsp;&nbsp;
                <input name="for-class[]" value="2" type="checkbox" class="class-option" id="class-2" checked><label for="class-2">二班</label>&nbsp;&nbsp;
                <input name="for-class[]" value="3" type="checkbox" class="class-option" id="class-3" checked><label for="class-3">三班</label>&nbsp;&nbsp;
                <input name="for-class[]" value="4" type="checkbox" class="class-option" id="class-4" checked><label for="class-4">四班</label>&nbsp;&nbsp;
                <input name="for-class[]" value="5" type="checkbox" class="class-option" id="class-5" checked><label for="class-5">五班</label>
                <div class="selection-buttons">
                    <span id="select-all" class="xs-button gray-button button"><i class="fa fa-check-square-o"></i> 全选</span>
                    <span id="clear-selection" class="xs-button gray-button button"><i class="fa fa-close"></i> 清除选择</span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="prompt">
                <label for="content">内容</label>
            </div>
            <div class="control">
                <textarea class="form-control" name="content" rows="5" id="content" required></textarea>
            </div>
        </div>
        <button type="submit" class="button submit-button green-button button-with-icon" name="add-homework"><i class="fa fa-plus"></i> 添加作业</button>
    </form>
</div>

<div role="tabpanel" class="tab-pane fade" id="panel-manage-homework">
    <h3>作业管理</h3>

    <table id="show-homework" class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th style="width: 20px">#</th>
            <th style="width: 75px">布置日期</th>
            <th style="width: 75px">过期日期</th>
            <th style="width: 75px" class="nosort">适用班级</th>
            <th style="width: 120px">添加人</th>
            <th style="width: 75px">科目</th>
            <th class="nosort">内容</th>
            <th style="width: 140px">添加日期</th>
            <th style="width: 70px" class="nosort">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($rows as $row):
            $forClass = json_decode($row['forClass']);
            if (count($forClass) == 5)
                $forClass = '全体';
            else
                $forClass = implode(', ', $forClass) . ' 班';
            ?>
            <tr data-pk="<?php echo $row['homeworkId'] ?>">
                <td><?php echo $row['homeworkId'] ?></td>
                <td><a href="#" data-type="date" data-pk="<?php echo $row['homeworkId'] ?>" data-url="<?php echo ROOT_URL ?>modules/<?php echo $module_name ?>/ajax.php?table=<?php echo $table_name ?>&m=<?php echo $_GET['page'] ?>&auth=<?php echo sha1(AJAX_SALT . $ajax_key) ?>" data-name="publishDate" class="x-editable-date"><?php echo $row['publishDate'] ?></a></td>
                <td><a href="#" data-type="date" data-pk="<?php echo $row['homeworkId'] ?>" data-url="<?php echo ROOT_URL ?>modules/<?php echo $module_name ?>/ajax.php?table=<?php echo $table_name ?>&m=<?php echo $_GET['page'] ?>&auth=<?php echo sha1(AJAX_SALT . $ajax_key) ?>" data-name="dueDate" class="x-editable-date"><?php echo $row['dueDate'] ?></a></td>
                <td><?php echo $forClass ?></td>
                <td><?php echo $row['userName'] ?></td>
                <td><a href="#" data-type="select2" data-pk="<?php echo $row['homeworkId'] ?>" data-url="<?php echo ROOT_URL ?>modules/<?php echo $module_name ?>/ajax.php?table=<?php echo $table_name ?>&m=<?php echo $_GET['page'] ?>&auth=<?php echo sha1(AJAX_SALT . $ajax_key) ?>" data-name="subject" class="x-editable-subject"><?php echo $row['subject'] ?></a></td>
                <td><a href="#" data-type="textarea" data-pk="<?php echo $row['homeworkId'] ?>" data-url="<?php echo ROOT_URL ?>modules/<?php echo $module_name ?>/ajax.php?table=<?php echo $table_name ?>&m=<?php echo $_GET['page'] ?>&auth=<?php echo sha1(AJAX_SALT . $ajax_key) ?>" data-name="content" class="x-editable-content"><?php echo $row['content'] ?></a></td>
                <td><?php echo $row['dateUpdated'] ?></td>
                <td>
                    <button class="button gray-button xs-button delete-homework idle" data-pk="<?php echo $row['homeworkId'] ?>">
                        <span class="idle-only" style="display: none"><i class="fa fa-trash-o"></i> 删除</span>
                        <span class="confirm-only" style="display: none">请确认</span>
                        <span class="in-progress-only" style="display: none"><i class="fa fa-spinner fa-spin"></i> 稍等..</span>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div role="tabpanel" class="tab-pane fade" id="panel-manage-subject">
    <h3>科目管理</h3>

    <table id="show-subjects" class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>科目名称</th><th>作业数量</th><th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($subjects as $subject): ?>
            <tr>
                <td><?php echo $subject ?></td>
                <td><?php echo $count = get_homework_count($subject) ?></td>
                <td><?php if ($count == 0): ?>
                        <form method="POST">
                            <input type="hidden" name="subject" value="<?php echo urlencode($subject) ?>">
                            <button name="delete-subject" type="submit" class="button gray-button xs-button"><i class="fa fa-trash-o"></i> 删除</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <form method="POST" id="add-subject">
        <input name="subject" type="text" class="form-control" placeholder="科目名称" required>
        <button name="add-subject" type="submit" class="button green-button button-with-icon"><i class="fa fa-plus"></i> 添加科目</button>
    </form>
</div>
</div>

<script>
    $('.date-picker').datepicker({
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        language: "zh-CN",
        keyboardNavigation: false,
        autoclose: true,
        todayHighlight: true
    });
    $('#add-homework').validate();
    $('#select-all').click(function() {
        $('.class-option').iCheck('check');
    });
    $('#clear-selection').click(function() {
        $('.class-option').iCheck('uncheck');
    });

    homeworkTable = $("#show-homework").DataTable({
        'aoColumnDefs': [{
            'bSortable': false,
            'aTargets': ['nosort']
        }],
        "order": [[ 0, "desc" ]]
    });
    $(".x-editable-content").editable({
        emptytext: "点击添加..."
    });
    $(".x-editable-subject").editable({
        source: [
            <?php
                foreach ($subjects as $subject) {
                    echo "{id: '" . $subject . "', text: '" . $subject . "'},";
                }
            ?>
        ],
        select2: {
            placeholder: "选择科目..."
        }
    });
    $(".x-editable-date").editable({
        format: 'yyyy-mm-dd',
        datepicker: {
            format: "yyyy-mm-dd",
            keyboardNavigation: false,
            todayHighlight: true
        }
    });
    $(".delete-homework").click(function() {
        $(this).addClass('transition');
        if ($(this).hasClass('confirm')) {
            // execute
            $(this).removeClass('confirm');
            $(this).addClass('in-progress');
            $.ajax({
                url: '<?php echo ROOT_URL ?>modules/<?php echo $module_name ?>/ajax.php?table=<?php echo $table_name ?>&action=delete&auth=<?php echo sha1(AJAX_SALT) ?>&pk=' + $(this).data('pk')
            }).done(function() {
                location.reload();
                $("#show-homework tr[data-pk='" + $(this).data('pk') + "']").addClass('to-delete');
                homeworkTable.row('.to-delete').remove().draw(false);
            });
        } else if ($(this).hasClass('idle')) {
            // confirm
            var $this = $(this);
            $this.removeClass('idle');
            $this.addClass('pre-confirm');
            setTimeout(function() {
                $this.removeClass('pre-confirm');
                $this.addClass('confirm');
                setTimeout(function() {
                    $this.attr('class', 'button gray-button xs-button delete-homework idle transition');
                }, 3500)
            }, 200);
        }
    });
</script>

<style>
    #show-subjects {
        width: 351px;
    }
    #add-subject input {
        width: 250px;
    }
    #add-subject button {
        vertical-align: top;
    }
    button.transition {
        transition: width .2s ease;
    }
    .idle .idle-only,
    .pre-confirm .idle-only,
    .confirm .confirm-only,
    .in-progress .in-progress-only {
        display: inline !important;
    }
    .idle,
    .pre-confirm,
    .confirm,
    .in-progress {
        width: 65px;
    }
    .selection-buttons {
        margin: 10px 0;
    }
    .selection-buttons .button {
        margin-right: 6px;
    }
</style>