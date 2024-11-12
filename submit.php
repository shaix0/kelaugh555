<?php
$limit = 4; // 提交上限

// 构建 Google Sheets API 请求 URL
$url = 'https://sheets.googleapis.com/v4/spreadsheets/1kOjmFM-spJO94CbbqnCdOdjtCW-dCeG7fmSxUSN0O4k/values/reply1?alt=json&key=AIzaSyBgdXDH29MGCtTFncLvBHFnew91ZMo4keA';

// 使用 file_get_contents 发出 GET 请求获取数据
$response = file_get_contents($url);

// 检查是否成功获取数据
if ($response === false) {
    echo json_encode(['status' => 'error', 'message' => '无法访问 Google Sheets 数据']);
    exit();
}

// 解析 JSON 数据
$data = json_decode($response, true);
$values = $data['values'];
$currentCount = count($values);

// 判断数据数量是否已达上限
if ($currentCount >= $limit + 1) { // +1 是因为标题行也算在内
    echo json_encode(['status' => 'error', 'message' => '回覆已達上限']);
    exit();
}

// 获取用户提交的数据
$newContent = $_POST['content'] ?? '';

// 检查用户提交的数据
if (empty($newContent)) {
    echo json_encode(['status' => 'error', 'message' => '提交内容不能为空']);
    exit();
}

// 准备数据追加到 Google Sheets 的 URL（使用 Google Forms 提交 URL）
$formUrl = 'https://docs.google.com/forms/u/0/d/e/1FAIpQLSc2GWRZ64CoI4WG_kw7WwzwhZfvHFriy6tZCjoXZEU_Vs-4LA/formResponse';
$formData = [
    'entry.485373546' => $newContent, // 表单项 ID 和内容
];

// 使用 cURL 发送 POST 请求以提交数据到 Google 表单
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $formUrl);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_exec($curl);
curl_close($curl);

// 成功提示
echo json_encode(['status' => 'success', 'message' => '提交成功！感谢留言✨']);
?>

