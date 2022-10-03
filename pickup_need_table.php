<?php
$first_line_delete = '/北海道_市立函館保健所|北海道_小樽市保健所|北海道_江別保健所|北海道_千歳保健所|北海道_旭川市保健所|北海道_岩見沢保健所|北海道_滝川保健所|北海道_深川保健所|北海道_富良野保健所|北海道_名寄保健所|北海道_江差保健所|北海道_渡島保健所|北海道_八雲保健所|北海道_室蘭保健所|北海道_苫小牧保健所|北海道_浦河保健所|北海道_静内保健所|北海道_釧路保健所|北海道_根室保健所|北海道_中標津保健所|北海道_網走保健所|北海道_北見保健所|北海道_紋別保健所|北海道_稚内保健所|北海道_留萌保健所|北海道_上川保健所|青森_八戸市保健所|岩手県_県央保健所|岩手県_一関保健所|岩手県_一関保健所|岩手県_二戸保健所|秋田県_能代保健所|秋田県_由利本荘保健所|山形県_最上保健所|福島県_福島県一括|群馬県_高崎市保健所|埼玉県_さいたま市保健所|千葉県_千葉市保健所|千葉県_柏市保健所|千葉県_市川保健所|千葉県_香取保健所|千葉県_安房保健所|東京都_文京保健所|東京都_品川区保健所|東京都_目黒区保健所|東京都_世田谷保健所/';
$two_line_delete = '/a/';
$three_line_delete = '/岩内保健所|帯広保健所/';

$file_names = get_file_name();
foreach ($file_names as $file_name) {
	$contents = get_contents($file_name);
	$data = data_shaping($file_name, $contents);
	output_data($file_name, $data);
}


// ファイル名で動かす関数を決める
function data_shaping($file_name, $contents)
{
	global $first_line_delete, $two_line_delete, $three_line_delete;
	$no_blank_data = delete_blank_line($contents);

	if (strpos($file_name, '小田原')) return odawara_shaping($no_blank_data);
	if (strpos($file_name, '東京都_墨田区保健所')) return sumidaku_shaping($no_blank_data);
	if (strpos($file_name, '東京都_中野区保健所')) return nakanoku_shaping($no_blank_data);
	if (strpos($file_name, '東京都_杉並区保健所')) return suginamiku_shaping($no_blank_data);
	if (preg_match($first_line_delete, $file_name)) return line_delete($no_blank_data, 1);
	if (preg_match($two_line_delete, $file_name)) return line_delete($no_blank_data, 2);
	if (preg_match($three_line_delete, $file_name)) return line_delete($no_blank_data, 3);
	return $no_blank_data;
}
// 空行削除
function delete_blank_line($contents)
{
	foreach ($contents as $value) {
		if (preg_match('/[^,\n\s]/', $value)) $data[] = $value;
	}
	return $data;
}
// pickup_csvディレクトリにoutput
function output_data($file_name, $data)
{
	file_put_contents("pickup_csv/pickup_${file_name}", $data, FILE_APPEND | LOCK_EX);
}

function odawara_shaping($contents)
{
	$data = array();

	$top_header_arr = array();
	$under_header_arr = array();
	foreach ($contents as $key => $value) {
		if ($key <= 5) continue;
		// 1行目のヘッダーと1行目のヘッダーの改行後の文字を2行目のヘッダーに入れる
		if ($key == 6) {
			$replace_data = preg_replace('/\r\n|\r|\n/', '', $value);
			$top_header_arr = explode(',', $replace_data);
			continue;
		}
		if($key == 7) {
			$under_header_arr = explode(',', $value);
			$under_header_arr[0] = $top_header_arr[0];
			$under_header_arr[7] = $top_header_arr[7];
			$under_header_arr[10] = $top_header_arr[10];
			$under_header_arr[11] = $top_header_arr[11];
			$under_header_arr[12] = $top_header_arr[12];
			$under_header_arr[13] = $top_header_arr[13];
			$data[] = implode(',', $under_header_arr) . "\n";
			continue;
		}
		$data[] = $value;
	}
	
	return $data;
}

function sumidaku_shaping($contents)
{
	$data = array();

	$top_line = array();
	$under_line = array();
	foreach ($contents as $key => $value) {
		if ($key <= 1) continue;
		if ($key % 2 == 0) {
			$replace_data = preg_replace('/\r\n|\r|\n/', '', $value);
			$top_line = explode(',', $replace_data);
			continue;
		}
		$under_line = explode(',', $value);
		unset($under_line[0]);
		$merge_array = array_merge($top_line, $under_line);
		$data[] = implode(',', $merge_array);
	}

	return $data;
}

function nakanoku_shaping($contents)
{
	$data = array();

	$top_line = array();
	$under_line = array();
	foreach ($contents as $key => $value) {
		if ($key % 2 == 0) {
			$replace_data = preg_replace('/\r\n|\r|\n/', '', $value);
			$top_line = explode(',', $replace_data);
			continue;
		}
		$under_line = explode(',', $value);
		unset($under_line[0], $under_line[2]);
		$merge_array = array_merge($top_line, $under_line);
		$data[] = implode(',', $merge_array);
	}

	return $data;
}

function suginamiku_shaping($contents)
{
	$data = array();

	$last_line = array_key_last($contents);
	foreach ($contents as $key => $value) {
		if ($key <= 0) continue;
		if ($key == $last_line) continue;
		$data_array = explode(',', $value);
		array_splice($data_array, 0, 11);
		$data[] = implode(',', $data_array);
	}

	return $data;
}

function line_delete($contents, $which_line)
{
	foreach ($contents as $key => $value) {
		if ($key > $which_line) {
			$deleted_data[] = $value;
		}
	}
	return $deleted_data;
}

function get_contents($file_name)
{
	$file = fopen("original_data/${file_name}", "r");
	while($data = fgetcsv($file)){
		$implode_data = implode(',', $data);
		$replace_data = preg_replace('/\r\n|\r|\n/', '', $implode_data);
		$contents[] = $replace_data . "\n";
	}
	// test.csvを閉じます。
	fclose($file);
	return $contents;
}

// function get_SqlFileObject($file_name)
// {
// 	$original_data = "original_data/${file_name}";
// 	$file = null;
// 	$file = new SplFileObject($original_data);
// 	$file->setFlags(
// 		\SplFileObject::READ_CSV |
// 		\SplFileObject::READ_AHEAD |
// 		\SplFileObject::SKIP_EMPTY |
// 		\SplFileObject::DROP_NEW_LINE
// 	);
// 	foreach ($file as $data) {
// 		//１行ずつ配列として取得できる
// 		$contents[] = implode(',', $data);
// 	}
// 	return $contents;
// }

// original_dataディレクトリ内のファイル内容とファイル名を取得
function get_file_name()
{
	$file_names = array();

	$dir = 'original_data/';
	if (!(is_dir($dir))) return;
	if(!($dh = opendir($dir))) return;
	
	while (($file_name = readdir($dh))) {
		// && strpos($file_name, '廃止') === false && strpos($file_name, '変更') === false && strpos($file_name, '使用禁止') === false
		if (!($file_name != '.DS_Store' && $file_name != '.' && $file_name != '..')) continue;
		// 同じファイル名があれば削除
		if ((file_exists("pickup_csv/pickup_${file_name}"))) {
			unlink("pickup_csv/pickup_${file_name}");
			echo "${file_name}を削除しました\n";
		}
		$file_names[] = $file_name;
	}
	return $file_names;
}
// test();
// function test()
// {
// 	$a = 7;
// 	if (!($a > 6)) return;
// 	echo "6より上";
// }
// $file_name = ["（終）149_神奈川県_小田原保健福祉事務所_食品衛生(許可)_2022年07月_末日_新規.xlsx - Table 1.csv", "（終）186_東京都_墨田区保健所_食品衛生(許可)_2022年05月_末日_新規.xlsx - Table 1.csv", "（終）451-463_鹿児島県_鹿児島県一括_食品衛生(許可)_2022年05月-2022年06月_末日.xlsx - table01.csv", "391_福岡県_中央保健所_食品衛生(許可)_2022年07月_末日_新規、廃止.xlsx - 1.新法許可(新規）.csv", "450_鹿児島県_鹿児島市保健所_食品衛生(許可-届出)_2022年08月まで全件_末日.csv"];
// // $file_name = ["（終）186_東京都_墨田区保健所_食品衛生(許可)_2022年05月_末日_新規.xlsx - Table 1.csv"];
// foreach ($file_name as $name) {
// 	if (file_exists("pickup_csv/pickup_${name}")) {
// 		unlink("pickup_csv/pickup_${name}");
// 		echo "${name}を削除しました\n";
// 	}
// 	$original_data = "original_data/${name}";
// 	$file = null;
// 	$file = new SplFileObject($original_data);
// 	$file->setFlags(
// 		\SplFileObject::READ_CSV |
// 		\SplFileObject::READ_AHEAD |
// 		\SplFileObject::SKIP_EMPTY |
// 		\SplFileObject::DROP_NEW_LINE
// 	);
// 	foreach ($file as $data) {
// 		//１行ずつ配列として取得できる
// 		$contents[] = implode(',', $data);
// 	}
// 	// $data = delete_blank_line($contents);
// 	check_file_name("${name}", $contents);
// }
// $dir = 'original_data/';
// if (is_dir($dir)) {
// 	if($dh = opendir($dir)) {
// 		while (($name = readdir($dh)) !== false) {
// 			if ($name != '.DS_Store' && $name != '.' && $name != '..') {
// 			if (file_exists("pickup_csv/pickup_${name}")) {
// 				unlink("pickup_csv/pickup_${name}");
// 				echo "${name}を削除しました\n";
// 			}
// 			$file_name[] = $name;
// 			// $original_data = "original_data/${name}";
// 			// $file = new SplFileObject($original_data);
// 			// $file->setFlags(
// 			// 	\SplFileObject::READ_CSV |           
// 			// 	\SplFileObject::READ_AHEAD |       
// 			// 	\SplFileObject::SKIP_EMPTY |       
// 			// 	\SplFileObject::DROP_NEW_LINE 
// 			// );
// 			// foreach ($file as $data) {
// 			// 	//１行ずつ配列として取得できる
// 			// 	$contents[] = implode(',', $data);
// 			// }
// 			// // $data = delete_blank_line($contents);
// 			// check_file_name($name, $contents);
// 			}
// 		}
// 	}
// }
// // "（終）451-463_鹿児島県_鹿児島県一括_食品衛生(許可)_2022年05月-2022年06月_末日.xlsx - table01.csv", 
// $f_name = ["（終）149_神奈川県_小田原保健福祉事務所_食品衛生(許可)_2022年07月_末日_新規.xlsx - Table 1.csv", "391_福岡県_中央保健所_食品衛生(許可)_2022年07月_末日_新規、廃止.xlsx - 1.新法許可(新規）.csv", "（終）186_東京都_墨田区保健所_食品衛生(許可)_2022年05月_末日_新規.xlsx - Table 1.csv", "450_鹿児島県_鹿児島市保健所_食品衛生(許可-届出)_2022年08月まで全件_末日.csv"];
// if ($file_name === $f_name) {
// 	echo 'same';
// }
// foreach ($f_name as $name) {
// 	$original_data = "original_data/${name}";
// 	$file = new SplFileObject($original_data);
// 	$file->setFlags(
// 		\SplFileObject::READ_CSV |           
// 		\SplFileObject::READ_AHEAD |       
// 		\SplFileObject::SKIP_EMPTY |       
// 		\SplFileObject::DROP_NEW_LINE 
// 	);
// 	foreach ($file as $data) {
// 		//１行ずつ配列として取得できる
// 		$contents[] = implode(',', $data);
// 	}
// 	// $data = delete_blank_line($contents);
// 	check_file_name($name, $contents);
// }
?>