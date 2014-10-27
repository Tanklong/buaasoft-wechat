<?php
/**
 * Homework Module
 *
 * @author Renfei Song
 */

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
    $subject = $_POST['subject'];
    if (empty($subject))
        goto fail;
    $subjects = get_option('subjects');
    if (!isset($subjects))
        goto fail;
    if (($key = array_search($subject, $subjects)) !== false) {
        unset($subjects[$key]);
        set_option('subjects', $subjects);
        redirect_success('科目 ' . $subject . ' 已经删除。');
        exit;
    } else {
        goto fail;
    }

    fail:
    redirect_failure('System failure.');
    exit;
}

if (isset($_POST['add-homework'])) {
    $publish_date = $_POST['publishDate'];
    $due_date = $_POST['dueDate'];
    $subject = $_POST['subject'];
    $content = $_POST['content'];

    if (empty($publish_date) || empty($due_date) || empty($subject) || empty($content)) {
        redirect_failure('Empty form is not accepted.');
        exit;
    }

    global $wxdb; /* @var $wxdb wxdb */
    $wxdb->insert('homework', array(
        'subject' => $subject,
        'content' => $content,
        'userName' => current_user_name(),
        'publishDate' => $publish_date,
        'dueDate' => $due_date,
        'dateUpdated' => date('c')
    ));

    redirect_success('Homework added!');
    exit;
}

global $wxdb; /* @var $wxdb wxdb */

$sql = "SELECT * FROM homework ORDER BY homeworkId DESC";
$rows = $wxdb->get_results($sql, ARRAY_A);
$subjects = get_option('subjects');
if (!isset($subjects)) {
    $subjects = array();
}

function get_homework_count($subject) {
    global $wxdb; /* @var $wxdb wxdb */
    $sql = $wxdb->prepare("SELECT count(*) FROM homework WHERE subject = '%s'", $subject);
    return $wxdb->get_var($sql);
}

?>

<h2>Homework Mgmt. Panel</h2>

<h3>添加作业</h3>
<form method="POST" id="add-homework">
    <div class="form-group">
        <div class="prompt">
            <label for="publishDate">布置日期</label>
        </div>
        <div class="control">
            <input class="form-control" type="text" name="publishDate" id="publishDate" required>
        </div>
    </div>
    <div class="form-group">
        <div class="prompt">
            <label for="dueDate">截止日期</label>
        </div>
        <div class="control">
            <input class="form-control" type="text" name="dueDate" id="dueDate">
        </div>
    </div>
    <div class="form-group">
        <div class="prompt">
            <label for="subject">科目</label>
        </div>
        <div class="control">
            <input class="form-control" type="text" name="subject" id="subject">
        </div>
    </div>
    <div class="form-group">
        <div class="prompt">
            <label for="content">内容</label>
        </div>
        <div class="control">
            <textarea class="form-control" name="content" rows="3" id="content"></textarea>
        </div>
    </div>
    <button type="submit" class="button submit-button" name="add-homework"><i class="fa fa-plus"></i> 添加作业</button>
</form>

<script>
    $("#add-homework").validate();
</script>

<h3>作业管理</h3>

<table id="show-homework">
    <thead>
    <tr>
        <th>序号</th><th>布置日期</th><th>过期日期</th><th>添加人</th>
        <th>科目</th><th>内容</th><th>更新日期</th><th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
    <tr data-pk="<?php echo $row['homeworkId'] ?>">
        <td><?php echo $row['homeworkId'] ?></td>
        <td><a href="#" data-type="date" data-pk="<?php echo $row['homeworkId'] ?>" data-url="/modules/Homework1221/ajax.php?table=homework&auth=<?php echo sha1(AJAX_SALT) ?>" data-name="publishDate" class="x-editable-field"><?php echo $row['publishDate'] ?></a></td>
        <td><a href="#" data-type="date" data-pk="<?php echo $row['homeworkId'] ?>" data-url="/modules/Homework1221/ajax.php?table=homework&auth=<?php echo sha1(AJAX_SALT) ?>" data-name="dueDate" class="x-editable-field"><?php echo $row['dueDate'] ?></a></td>
        <td><?php echo $row['userName'] ?></td>
        <td><a href="#" data-type="select2" data-pk="<?php echo $row['homeworkId'] ?>" data-url="/modules/Homework1221/ajax.php?table=homework&auth=<?php echo sha1(AJAX_SALT) ?>" data-name="subject" class="x-editable-subjects"><?php echo $row['subject'] ?></a></td>
        <td><a href="#" data-type="textarea" data-pk="<?php echo $row['homeworkId'] ?>" data-url="/modules/Homework1221/ajax.php?table=homework&auth=<?php echo sha1(AJAX_SALT) ?>" data-name="content" class="x-editable-field"><?php echo $row['content'] ?></a></td>
        <td><?php echo $row['dateUpdated'] ?></td>
        <td>
            <button class="button reset-button delete-homework idle" data-pk="<?php echo $row['homeworkId'] ?>">
                <span class="idle-only" style="display: none">删除</span>
                <span class="confirm-only" style="display: none">请确认</span>
                <span class="in-progress-only" style="display: none"><i class="fa fa-spinner fa-spin"></i> 稍等..</span>
            </button>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
    homeworkTable = $("#show-homework").DataTable();
    $(".x-editable-field").editable();
    $(".x-editable-subjects").editable({
        source: [
            <?php
                foreach ($subjects as $subject) {
                    echo "{id: '" . $subject . "', text: '" . $subject . "'},";
                }
            ?>
        ],
        select2: {

        }
    });
    $(".delete-homework").click(function() {
        $(this).addClass('transition');
        if ($(this).hasClass('confirm')) {
            // execute
            $(this).removeClass('confirm');
            $(this).addClass('in-progress');
            $.ajax({
                url: '/modules/Homework1221/ajax.php?table=homework&action=delete&auth=<?php echo sha1(AJAX_SALT) ?>&pk=' + $(this).data('pk')
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
                    $this.attr('class', 'button reset-button delete-homework idle transition');
                }, 3500)
            }, 200);
        }
    });
</script>

<style>
    button.delete-homework {
        padding: 5px;
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
    .idle {
        width: 55px;
    }
    .pre-confirm,
    .confirm,
    .in-progress {
        width: 80px;
    }
</style>

<h3>科目管理</h3>

<table id="show-subjects">
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
                    <input type="hidden" name="subject" value="<?php echo $subject ?>">
                    <button name="delete-subject" type="submit" class="button reset-button">删除</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<form method="POST" id="add-subject">
    <input name="subject" type="text" class="form-control" placeholder="科目名称" required>
    <button name="add-subject" type="submit" class="button submit-button"><i class="fa fa-plus"></i> 添加科目</button>
</form>

<style>
    #add-subject input {
        width: 250px;
    }
</style>