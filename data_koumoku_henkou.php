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
    $files = get_file_name();
    foreach ($files as $file) {
        $csv = array();
        $fp = fopen("pickup_csv/${file}", 'r');
    
        // csv中身取得
        $i = 0;
        while ($data = fgetcsv($fp)) {
            if ($i == 0) {
                $csv[] = $data;
                $i = $i + 1;
                continue;
            }
            $csv[] = implode(',', $data) . "\n";
        }
    
        // macciの項目名を取得
        $macciName = get_macci_name($csv, $pdo);
    
        // 連想配列を普通の配列にする
        foreach ($macciName as $key => $value) {
            $csv[0][$key] = $value['macci_name'];
        }
        fclose($fp);
        // var_dump($csv);
        $csv[0] = implode(',', $csv[0]) . "\n";
        var_dump($csv[0]);
    
        // 同じファイル名があれば削除
        if ((file_exists("change_koumoku/maccikoumoku_${file}"))) {
			unlink("change_koumoku/maccikoumoku_${file}");
			echo "${file}を削除しました\n";
		}
        // // ファイル保存
        $file = str_replace('pickup_', '', $file);
        file_put_contents("change_koumoku/maccikoumoku_${file}", $csv, FILE_APPEND | LOCK_EX);
        // return;
    }

} catch (\Throwable $th) {
    throw $th;
}

function get_file_name()
{
	$files = array();

	$dir = 'pickup_csv/';
	if (!(is_dir($dir))) return;
	if(!($dh = opendir($dir))) return;
	
	while (($file_name = readdir($dh))) {
		// && strpos($file_name, '廃止') === false && strpos($file_name, '変更') === false && strpos($file_name, '使用禁止') === false
		if (!($file_name != '.DS_Store' && $file_name != '.' && $file_name != '..')) continue;
		$files[] = $file_name;
	}

	return $files;
}

function get_macci_name($csv, $pdo) {
    $koumoku_arr = array();
    // ４行目(外部名のとこ)を取得
    foreach ($csv[0] as $value) {
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