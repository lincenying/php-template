<?php
include 'inc/conn.php';

function dd($v, $t = '')
{
    echo '<pre>';
    echo '<h1>' . $t . '</h1>';
    var_dump($v);
    echo '</pre>';
}

echo $get1;
echo $get2;

print_r($_GET);

$sql = 'SELECT * FROM cyxw_cash';
$memcache_key = getSqlMd5($sql, []);
$person = [];
if ($onmemcache == false || !($person = $memcache->get($memcache_key))) {
    $person = $db->query($sql);
    echo '$memcache_key 未命中缓存';
    $onmemcache && $memcache->set($memcache_key, $person, 0, 86400);
}
dd($person, "query('SELECT * FROM cyxw_cash')");

$persons_num = $db->single('SELECT count(*) as total FROM cyxw_cash');
dd($persons_num, "single('SELECT count(*) FROM cyxw_cash')");

$sql = 'SELECT c_cashname FROM cyxw_cash WHERE c_id > ?';
$data = ['1'];
echo getSqlMd5($sql, $data);
$firstname = $db->single($sql, $data);
dd($firstname, "single('SELECT c_cashname FROM cyxw_cash WHERE c_id > ?', ['1'])");

$sql = 'SELECT c_id, c_cashname FROM cyxw_cash WHERE c_id > :id order by c_id desc';
$data = ['id' => '1'];
echo getSqlMd5($sql, $data);
$id_age = $db->row($sql, $data);
dd($id_age, "row('SELECT c_id, c_cashname FROM cyxw_cash WHERE c_id > :id ', ['id' => '1'])");

// Single Row with numeric index
$sql = 'SELECT c_id, c_cashname FROM cyxw_cash WHERE c_cashname = :f';
$data = ['f' => '林岑影'];
echo getSqlMd5($sql, $data);
$id_age_num = $db->row($sql, $data, PDO::FETCH_NUM);
dd($id_age_num, "row('SELECT c_id, c_cashname FROM cyxw_cash WHERE c_cashname = :f', ['f' => '林岑影'], PDO::FETCH_NUM)");
// Column, numeric index
$ages = $db->column('SELECT c_cashname FROM cyxw_cash');
dd($ages, "column('SELECT c_cashname FROM cyxw_cash')");
// The following statements will return the affected rows

// Update statement
$update = $db->query('UPDATE cyxw_cash SET c_cashname = :f WHERE c_id = :id', ['f' => '林轻灵', 'id' => '2']);
dd($update, "query('UPDATE cyxw_cash SET c_cashname = :f WHERE c_id = :id', ['f' => '林轻灵', 'id' => '2'])");
// Insert statement
// $insert = $db->query(
//     'INSERT INTO  cyxw_cash (c_card,c_userid,c_cb,c_cashname,c_cashuser,c_status) VALUES (:card,:userid,:cb,:cashname,:cashuser,:status)',
//     [
//         'card' => '1231234232',
//         'userid' => '123',
//         'cb' => '123',
//         'cashname' => '魅影',
//         'cashuser' => '魅影',
//         'status' => '1',
//     ],
// );

// dd($insert, "'INSERT INTO  cyxw_cash (c_card,c_userid,c_cb,c_cashname,c_cashuser,c_status) VALUES (:card,:userid,:cb,:cashname,:cashuser,:status)'");

// Delete statement
$delete = $db->query('DELETE FROM cyxw_cash WHERE c_id = :id', ['id' => '4']);
dd($delete, "query('DELETE FROM cyxw_cash WHERE c_id = :id', ['id' => '4'])");

phpinfo();
?>
