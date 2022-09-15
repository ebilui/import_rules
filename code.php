<?php
try {
    // db接続
    $pdo = new PDO(
        // ホスト名、データベース名
        'mysql:host=localhost;dbname=import_rules;',
        // ユーザー名
        'root',
        // パスワード
        '',
        // レコード列名をキーとして取得させる
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    // ファイル取得
    $file = 'change_csv/【蛯名】データDB取込（飲食）（本番用） テンプレートver1.9 - テンプレート のコピー.csv';
    $fp = fopen($file, 'r');

    // csv中身取得
    while ($data = fgetcsv($fp)) {
        $csv[] = $data;
    }

    // macciの項目名を取得
    $macciName = get_macci_name($csv, $pdo);

    // 連想配列を普通の配列にする
    foreach ($macciName as $key => $value) {
        $csv[2][$key] = $value['macci_name'];
    }
    fclose($fp);

    // csvファイルを上書き
    overwrite_csv($file, $csv);

} catch (\Throwable $th) {
    throw $th;
}

function get_macci_name($csv, $pdo) {
    $koumoku_arr = array();
    // ４行目(外部名のとこ)を取得
    foreach ($csv[3] as $value) {
        $koumoku_arr[] = $value;
    }
    // 項目名を一つずつ取得
    foreach ($koumoku_arr as $single_koumoku_arr) {
        // dbから取得
        $single_koumoku_arr = "%" . $single_koumoku_arr . "%";
        $stmt = $pdo->prepare('SELECT macci_name FROM add_koumoku WHERE gaibu_name LIKE :single_koumoku_arr LIMIT 1');
        $stmt->bindValue(':single_koumoku_arr', $single_koumoku_arr);
        $res = $stmt->execute();
        if ($res) {
            $macci_name = $stmt->fetch();
            if ($macci_name != false) {
                $mc[] = $macci_name;
            } else {
                $mc[] = array('macci_name'=>'');
            }
        }
    }

    return $mc;
}

function overwrite_csv($file, $csv) {
    $fw = fopen($file, 'w');
    foreach ($csv as $value) {
        fputcsv($fw, $value);
    }
    fclose($fw);
}
?>