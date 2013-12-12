<h2>Naive Bayesによる自動メールフィルタリング</h2>
<?
/* mecab exe path */
$mecab_exe = '/usr/local/bin/mecab';

/*
 * csvファイルから条件付き確率の連想配列を作成
 */
$key_map = array();
$sum = array();
foreach (array('mailmagazine','recruit','university') as $f) {
  $file = file_get_contents('csv/'.$f.'.csv');
  $key_map[$f] = array();
  foreach (explode("\n",$file) as $val) {
    $line = explode(",",$val);
    if((isset($line[0]) && $line[0]) || (isset($line[1]) && $line[1])){
      $key_map[$f][$line[0]] = $line[1];
      if(!isset($sum[$f])) $sum[$f]=0;
      $sum[$f] ++;
    }
  }
}

/*
 * それぞれの語彙数、不公平さをなくすために用いる
 */
$sum['total'] = 31602;
$sum['university'] = 15370;
$sum['mailmagazine'] = 15967;
$sum['recruit'] = 265;

/*
 * 条件付き確率の連想配列を呼び出す関数
 */
function Prob($name,$word){
  global $key_map;
  if(isset($key_map[$name][$word]) && $key_map[$name][$word])
    return floatval($key_map[$name][$word]);
  else
    return floatval(1.0E-10);
}



if(isset($_POST['keyword'])){
  $all_prob = array();
  $keyword = '"'.$_POST['keyword'].'"';

  $result = '';
  //Mecabを起動し、品詞の分解を行う。文節ごとにスペースで区切られている
  exec('/bin/echo '.$keyword.' | '.$mecab_exe.' -O wakati',$result);

  var_dump($result);

  foreach($result as $subject){
    $all_prob[$keyword] = array();
    foreach(array('mailmagazine','recruit','university') as $key){
      $all_prob[$keyword][$key] = $sum[$key]/$sum['total'];
      foreach(explode(' ',$subject) as $word){
        $all_prob[$keyword][$key] *= Prob($key,$word);
      }
    }
  }

?>
  <h4>分類件名：<?=($_POST['keyword'])?> </h4>
  大学である確率：<?=$all_prob[$keyword]["university"]?> <br>
  リクルートである確率：<?=$all_prob[$keyword]["recruit"]?> <br>
  メルマガである確率：<?=$all_prob[$keyword]["mailmagazine"]?> <br>
  <br>
  このメールは<span style="color:red;">
<?
  $max_prob = max($all_prob[$keyword]["university"],$all_prob[$keyword]["recruit"],$all_prob[$keyword]["mailmagazine"]);
  if($max_prob === $all_prob[$keyword]["university"]){
    echo "大学関係";
  }else if($max_prob === $all_prob[$keyword]["recruit"]){
    echo "リクルート関係";
  }else if($max_prob === $all_prob[$keyword]["mailmagazine"]){
    echo "メールマガジン関係";
  }
?>
  </span>のメールです。<br>
<?}?>
<hr>
<form action="./index.php" method="POST">
*.input your email subject:<input width="300" name="keyword"/> <input type="submit" value="submit"/>
<br>
<br>
<br>
Hiroaki Murayama - g2112035 FUN<br>
