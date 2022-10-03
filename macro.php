<?php
function macro01() {
    // ファイル取得
    $file = 'change_csv/【蛯名】データDB取込（飲食）（本番用） テンプレートver1.9 - テンプレート のコピー.csv';
    $fp = fopen($file, 'r');

    // csv中身取得
    while ($data = fgetcsv($fp)) {
        $csv[] = $data;
    }
}
?>