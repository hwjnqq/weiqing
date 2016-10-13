<?php

error_reporting(0);
header("Content-type:text/html; Charset=utf-8");
require_once("./image.class.php");

$images = new Images("file");

$cutImage = $_GET['image'];
$cut = $_POST['cut'];
if (empty($cutImage) && empty($cut)) {
    exit();
}

if ($_GET['act'] == 'cut') {
    $res = $images->thumb($cut, false, 1);
    if ($res == false) {
        echo '裁剪失败, <a href="javascript:history.back();">点此返回</a>';
    } elseif (is_array($res)) {
        // echo '<img src="' . $res['big'] . '" style="margin:10px;">';
        // echo '<img src="' . $res['small'] . '" style="margin:10px;">';
        echo '裁剪成功, <a href="javascript:history.back();">点此返回</a>';
    } elseif (is_string($res)) {
        echo '<img src="' . $res . '">';
    }
} elseif (isset($_GET['act']) && $_GET['act'] == "upload") {
    $path = $images->move_uploaded();
    //文件比规定的尺寸大则生成缩略图，小则保持原样
    $images->thumb($path, false, 0);
    if ($path == false) {
        $images->get_errMsg();
    } else {
        echo "上传成功！<a href='" . $path . "' target='_blank'>查看</a>";
    }
} else {
    ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta name="Author" content="SeekEver">
    <meta name="Keywords" content="">
    <meta name="Description" content="">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <script src="./js/jquery.min.js" type="text/javascript"></script>
    <script src="./js/jquery.Jcrop.js" type="text/javascript"></script>
    <link rel="stylesheet" href="./css/jquery.Jcrop.css" type="text/css"/>
    <script type="text/javascript">
        jQuery(function ($) {
            // Create variables (in this scope) to hold the API and image size
            var jcrop_api, boundx, boundy;
            $('#target').Jcrop({
                    minSize: [48, 48],
                    setSelect: [0, 0, 190, 190],
                    onChange: updatePreview,
                    onSelect: updatePreview,
                    onSelect: updateCoords,
                    aspectRatio: 1
                },
                function () {
                    // Use the API to get the real image size
                    var bounds = this.getBounds();
                    boundx = bounds[0];
                    boundy = bounds[1];
                    // Store the API in the jcrop_api variable
                    jcrop_api = this;
                });
            function updateCoords(c) {
                $('#x').val(c.x);
                $('#y').val(c.y);
                $('#w').val(c.w);
                $('#h').val(c.h);
            };
            function checkCoords() {
                if (parseInt($('#w').val())) return true;
                alert('Please select a crop region then press submit.');
                return false;
            };
            function updatePreview(c) {
                if (parseInt(c.w) > 0) {
                    var rx = 48 / c.w;		//小头像预览Div的大小
                    var ry = 48 / c.h;

                    $('#preview').css({
                        width: Math.round(rx * boundx) + 'px',
                        height: Math.round(ry * boundy) + 'px',
                        marginLeft: '-' + Math.round(rx * c.x) + 'px',
                        marginTop: '-' + Math.round(ry * c.y) + 'px'
                    });
                }
                {
                    var rx = 199 / c.w;		//大头像预览Div的大小
                    var ry = 199 / c.h;
                    $('#preview2').css({
                        width: Math.round(rx * boundx) + 'px',
                        height: Math.round(ry * boundy) + 'px',
                        marginLeft: '-' + Math.round(rx * c.x) + 'px',
                        marginTop: '-' + Math.round(ry * c.y) + 'px'
                    });
                }
            };
        });

    </script>
</head>
<body>
<!-- <form method="post" action="?act=upload" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="submit" value="上传">
</form> -->
<div style="float:left;">
    <img id="target" src="<?php echo $cutImage ?>" />
</div>

<!-- <div style="width:48px;height:48px;margin:10px;overflow:hidden; float:left;">
    <img style="float:left;" id="preview" src="0000.jpg"></div> -->

<div style="width:200px;height:200px;margin:10px;overflow:hidden; float:left;">
    <img style="float:left;" id="preview2" src="<?php echo $cutImage ?>">
</div>

<form action="?act=cut" method="post" onsubmit="return checkCoords();" id="cutImageForm">
    <input type="hidden" id="x" name="x"/>
    <input type="hidden" id="y" name="y"/>
    <input type="hidden" id="w" name="w"/>
    <input type="hidden" id="h" name="h"/>
    <input type="hidden" name="cut" value="<?php echo $_GET['cut'] ?>" />
    <!-- <input type="submit" value="裁剪" style="position:absolute;font-size:20px;" /> -->
    <a href="javascript:;$('#cutImageForm').submit();" style="position:absolute;font-size:50px;">裁剪</a>
</form>
</body>
</html>

<?php
}
?>