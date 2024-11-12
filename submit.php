<?php
// 设置提交限制
$limit = 4;
// 数据存储文件路径
$dataFile = 'data.txt';
// 锁定文件路径
$lockFile = 'lockfile.txt';

// 1. 尝试打开文件并加锁
$lockHandle = fopen($lockFile, 'w+');
if (!flock($lockHandle, LOCK_EX)) {
    // 无法获得锁时返回错误
    echo json_encode(['status' => 'error', 'message' => '服务器繁忙，请稍后再试']);
    exit();
}

// 2. 检查当前提交数量
$dataHandle = fopen($dataFile, 'c+'); // 如果文件不存在则创建
$dataContents = fread($dataHandle, filesize($dataFile) ?: 1);
$data = json_decode($dataContents, true) ?: [];

// 检查数据数量是否超出限制
if (count($data) >= $limit) {
    echo json_encode(['status' => 'error', 'message' => '回复已达上限']);
    // 释放锁并退出
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit();
}

// 3. 处理提交数据
$newEntry = [
    'content' => $_POST['content'],
    'timestamp' => date('Y-m-d H:i:s')
];
$data[] = $newEntry;

// 写入新数据
rewind($dataHandle);
fwrite($dataHandle, json_encode($data));
ftruncate($dataHandle, ftell($dataHandle));
fclose($dataHandle);

// 4. 释放锁
flock($lockHandle, LOCK_UN);
fclose($lockHandle);

// 返回成功消息
echo json_encode(['status' => 'success', 'message' => '提交成功！感谢留言✨']);
?>
