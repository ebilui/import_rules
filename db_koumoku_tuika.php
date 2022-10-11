<?php
// メモリ制限を増やす
ini_set("memory_limit", "1000M");

try {
    // 項目名
    $koumoku = array();
    // 既に使ったgaibu_nameか確認
    $already_used = array();

    // csv_fileフォルダにあるファイルを１つずつ取得
    foreach (glob('csv_file/*') as $file) {
        $fp = fopen($file, 'r');
        // csvの中身を取得
        $single_csv = single_csv($fp);

        // 項目と既に使った項目を取得
        list($koumoku, $already_used) = get_column_name($single_csv, $koumoku, $already_used);

        // csvの中身が入ってる変数を初期化
        $single_csv = array();
        fclose($fp);
    }

    // データベースにupdateする
    update_database($koumoku);

} catch (\Throwable $th) {
    throw $th;
} finally {
    $pdo = null;
}

// 関数達
function single_csv($fp) {
    while ($data = fgetcsv($fp)) {
        $single_csv[] = $data;
    }
    return $single_csv;
}


function get_column_name($single_csv, $koumoku, $already_used) {
    // 項目名が「ここまで」って書いてあるとこまでの項目名を取得
    $kokomade = array_search('ここまで', $single_csv[2]);
    for ($i=0; $i < $kokomade; $i++) { 
        $macci_name = '';
        $gaibu_name = '';
        // 元データ項目名が空のと、住所0のやつは、マクロを使う用の項目名だからスキップする
        if(($single_csv[2][$i] == '施設-住所0' || $single_csv[2][$i] == '申請-住所0') && $single_csv[3][$i] == '') continue;
        $macci_name = $single_csv[2][$i];
        $gaibu_name = $single_csv[3][$i];
        if (in_array($gaibu_name, $already_used)) continue;
        if (array_key_exists($macci_name, $koumoku)) {
            $koumoku[$macci_name] = $koumoku[$macci_name] . ',' . $gaibu_name;
            $already_used[] = $gaibu_name;
        } else {
            $koumoku[$macci_name] = $gaibu_name;
            $already_used[] = $gaibu_name;
        }
    }
    return array($koumoku, $already_used);
}


function update_database($koumoku) {
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
    $stmt = $pdo->prepare('UPDATE add_koumoku SET gaibu_name = :gaibu_name WHERE macci_name = :macci_name');
    foreach ($koumoku as $macciName => $gaibuName) {
        $stmt->bindValue(':macci_name', $macciName);
        $stmt->bindValue(':gaibu_name', $gaibuName);
        $stmt->execute();
    }
}
    
?>