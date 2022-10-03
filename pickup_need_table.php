<?php
$first_line_delete = '/北海道_市立函館保健所|北海道_小樽市保健所|北海道_江別保健所|北海道_千歳保健所|北海道_旭川市保健所|北海道_岩見沢保健所|北海道_滝川保健所|北海道_深川保健所|北海道_富良野保健所|北海道_名寄保健所|北海道_江差保健所|北海道_渡島保健所|北海道_八雲保健所|北海道_室蘭保健所|北海道_苫小牧保健所|北海道_浦河保健所|北海道_静内保健所|北海道_釧路保健所|北海道_根室保健所|北海道_中標津保健所|北海道_網走保健所|北海道_北見保健所|北海道_紋別保健所|北海道_稚内保健所|北海道_留萌保健所|北海道_上川保健所|青森_八戸市保健所|岩手県_県央保健所|岩手県_一関保健所|岩手県_一関保健所|岩手県_二戸保健所|秋田県_能代保健所|秋田県_由利本荘保健所|山形県_最上保健所|福島県_福島県一括|群馬県_高崎市保健所|埼玉県_さいたま市保健所|千葉県_千葉市保健所|千葉県_柏市保健所|千葉県_市川保健所|千葉県_香取保健所|千葉県_安房保健所|東京都_文京保健所|東京都_品川区保健所|東京都_目黒区保健所|東京都_世田谷保健所|東京都_足立区保健所|東京都_八王子市保健所|東京都_東京都一括|東京都_世田谷保健所|東京都_品川区保健所|神奈川県_藤沢市保健所|神奈川県_平塚保健福祉事務所|神奈川県_鎌倉保健福祉事務所|新潟県_新発田保健所|新潟県_三条保健所|新潟県_長岡保健所|新潟県_南魚沼保健所|新潟県_柏崎保健所|新潟県_糸魚川保健所|新潟県_上越保健所|石川県_石川県一括|福井県_福井市保健所|福井県_福井県一括|山梨県_山梨県一括|愛知県_豊橋市保健所|滋賀県_滋賀県一括|大阪府_吹田市保健所|大阪府_吹田市保健所|兵庫県_西宮市保健所_食品衛生(許可)|岡山県_倉敷市保健所|香川県_西讃保健所|香川県_東讃保健所|香川県_小豆保健所|香川県_東讃保健所|香川県_中讃保健所|愛媛県_愛媛県一括|高知県_高知県一括|福岡県_嘉穂・鞍手保健福祉環境事務所|宮崎県_宮崎市保健所|沖縄県_宮古保健所/';
$two_line_delete = '/東京都_荒川区保健所|東京都_新宿区保健所|島根県_松江保健所（安来市）|島根県_雲南保健所|島根県_出雲保健所|島根県_益田保健所|島根県_隠岐保健所|福岡県_北筑後保健福祉環境事務所/';
$three_line_delete = '/岩内保健所|帯広保健所/';
$special_case = '/東京都_目黒区保健所|鹿児島県_鹿児島県一括/';

$file_names = get_file_name();
foreach ($file_names as $file_name) {
	$contents = [];
	if (!(preg_match($special_case, $file_name))) $contents = get_contents($file_name);
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
	if (strpos($file_name, '東京都_板橋区保健所')) return itabashiku_shaping($no_blank_data);
	if (strpos($file_name, '東京都_葛飾区保健所')) return katushikaku_shaping($no_blank_data);
	if (strpos($file_name, '鹿児島県_鹿児島県一括')) return kagoshima_shaping($file_name);
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

	$top_line = array();
	$under_line = array();
	foreach ($contents as $key => $value) {
		if ($key <= 5) continue;
		// 1行目のヘッダーと1行目のヘッダーの改行後の文字を2行目のヘッダーに入れる
		if ($key == 6) {
			$replace_data = preg_replace('/\r\n|\r|\n/', '', $value);
			$top_line = explode(',', $replace_data);
			continue;
		}
		if($key == 7) {
			$under_line = explode(',', $value);
			$under_line[0] = $top_line[0];
			$under_line[7] = $top_line[7];
			$under_line[10] = $top_line[10];
			$under_line[11] = $top_line[11];
			$under_line[12] = $top_line[12];
			$under_line[13] = $top_line[13];
			$data[] = implode(',', $under_line) . "\n";
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
		$array_data = array_merge($top_line, $under_line);
		$data[] = implode(',', $array_data);
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
		$array_data = explode(',', $value);
		array_splice($array_data, 0, 11);
		$data[] = implode(',', $array_data);
	}

	return $data;
}

function itabashiku_shaping($contents)
{
	$data = array();

	$top_line = array();
	foreach ($contents as $key => $value) {
		if ($key <= 0) continue;
		if ($key % 2 != 0) {
			$replace_data = preg_replace('/\r\n|\r|\n/', '', $value);
			$top_line = explode(',', $replace_data);
			unset($top_line[0], $top_line[2], $top_line[3], $top_line[5], $top_line[7], $top_line[9]);
			continue;
		}
		$under_line = explode(',', $value);
		unset($under_line[0], $under_line[1], $under_line[3], $under_line[5], $under_line[6], $under_line[7], $under_line[9]);
		$array_data = array_merge($top_line, $under_line);
		$data[] = implode(',', $array_data);
	}

	return $data;
}

function katushikaku_shaping($contents)
{
	$data = array();

	$top_line = array();
	foreach ($contents as $key => $value) {
		if ($key <= 0) continue;
		if ($key % 2 != 0) {
			$replace_data = preg_replace('/\r\n|\r|\n/', '', $value);
			$top_line = explode(',', $replace_data);
			continue;
		}
		$under_line = explode(',', $value);
		unset($under_line[0]);
		$array_data = array_merge($top_line, $under_line);
		$data[] = implode(',', $array_data);
	}

	return $data;
}

function kagoshima_shaping($file_name)
{
	$file = fopen("original_data/${file_name}", "r");
	while($data = fgetcsv($file)){
		$implode_data = implode(',', $data);
	}
}

function line_delete($contents, $which_line)
{
	foreach ($contents as $key => $value) {
		if ($key < $which_line) continue;
		$deleted_data[] = $value;
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
?>