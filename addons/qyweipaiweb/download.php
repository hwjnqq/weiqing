<?php

ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
if (PHP_SAPI == 'cli') {
    die('This example should only be run from a Web Browser');
}
require_once './../framework/library/phpexcel/PHPExcel.php';
global $_GPC, $_W;
$rid = intval($_GPC['id']);
if (empty($rid)) {
    message('抱歉，传递的参数错误！', '', 'error');
}

$sql = "SELECT a.id,a.xfm,a.stype,a.status,a.create_time,a.use_time FROM " . tablename('qywpweb_xfm') . " AS a WHERE a.rid = :rid and a.weid=:weid
        ORDER BY a.id DESC limit 0,300";
$list = pdo_fetchall($sql, array(':rid' => $rid, ':weid' => $_W['weid']));

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("微拍")
    ->setLastModifiedBy("微拍")
    ->setTitle("")
    ->setSubject("")
    ->setDescription("")
    ->setKeywords("")
    ->setCategory("");
// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'ID')
    ->setCellValue('B1', '消费码')
    ->setCellValue('C1', '分配类型')
    ->setCellValue('D1', '使用状态')
    ->setCellValue('E1', '使用时间')
    ->setCellValue('F1', '生成时间');

$i = 2;
foreach ($list as $row) {
    if ($row['status'] == 0) {
        $row['status'] = '未消费';
    } elseif ($row['status'] == 1) {
        $row['status'] = '已消费';
    }
    if ($row['stype'] == 0) {
        $row['stype'] = '系统分配';
    } elseif ($row['stype'] == 1) {
        $row['stype'] = '线下分配';
    }

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . $i, $row['id'])
        ->setCellValue('B' . $i, $row['xfm'])
        ->setCellValue('C' . $i, $row['stype'])
        ->setCellValue('D' . $i, $row['status'])
        ->setCellValue('E' . $i, empty($row['use_time']) ? '未使用' : date('Y-m-d H:i', $row['use_time']))
        ->setCellValue('F' . $i, empty($row['create_time']) ? '' : date('Y-m-d H:i', $row['create_time']));

    $i++;
}

$objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(18);

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('消费码导出_' . $rid);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SN_' . $rid . '_' . date('Y_m_d_H_i') . '.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

	