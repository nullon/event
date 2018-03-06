<?php
$mysql = [
    "host" => "localhost",
    "dbname" => "database",
    "user" => "username",
    "pass" => "password",
];
try{
    $PDO = new PDO("mysql:host={$mysql["host"]};dbname={$mysql["dbname"]}",$mysql["user"],$mysql["pass"]);
}
catch (Exception $e) {
    echo '<h1 style="color:red">データベース接続エラー</h1>'.PHP_EOL;
    echo "<p>".$e->getMessage()."</p>";
    exit;
}
$query = 'CREATE TABLE IF NOT EXISTS event (id INT NOT NULL UNIQUE PRIMARY KEY AUTO_INCREMENT,date DATE NOT NULL,event TEXT NOT NULL,status ENUM("visible","hidden") NOT NULL DEFAULT "visible");';
$PDO->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <title>年表生成ツール</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" />
</head>
<body>
    <div class="container">
        <h1><a href="">年表作成ツール</a></h1>
        <?php if($_POST){execution();} ?>
        <?php output_html(); ?>
    </div>
</body>
</html>
<?php
exit;
function display (){
    global $PDO;
    $query = "SELECT * FROM event ORDER BY date;";
        ?>
        <table class="table">
            <tr>
                <th>日付</th>
                <th>できごと</th>
            </tr>
        <?php
        foreach($PDO->query($query) as $value):
        if($value["status"] == "visible"):
        ?>
            <tr>
                <td><?php echo $value["date"] ?></td>
                <td><?php echo $value["event"] ?></td>
            </tr>
        <?php
        endif;
        endforeach;
        ?>
        </table>
        <form class="row" method="post" action="" enctype="multipart/form-data">
            <div class="form-group col-sm-4 col-md-3 col-lg-2">
                <input type="text" name="date" class="form-control" value="<?php echo last_record(); ?>" placeholder="日付" />
            </div>
            <div class="form-group col-sm-6 col-md-7 col-lg-8 col-xl-9">
                <input type="text" name="event" class="form-control" placeholder="できごと" />
            </div>
            <div class="form-group col-sm-2 col-xl-1 text-right">
                <button type="submit" class="btn btn-info" name="action" value="register">記録</button>
            </div>
        </form>
        
        <div class="text-right"><a class="btn btn-primary" href="?p=edit">編集</a></div>

        <?php
}
function insert_record($date,$event){
    global $PDO;
    $prepare = "INSERT INTO event (date,event) VALUES (:date,:event);";
    $stmt = $PDO->prepare($prepare);
    $stmt->bindParam(":date",$date);
    $stmt->bindParam(":event",$event);
    if($stmt->execute()){return "レコードを登録しました。";}
    else{return "レコードの登録に失敗しました。";}
}
function delete_record($id){
    global $PDO;
    $query = "DELETE FROM event WHERE id = $id";
    if($PDO->query($query)){return "レコードを消去しました。";}
    else{return "レコードの削除に失敗しました。";}
}
function last_record(){
    global $PDO;
    $query = "SELECT date FROM event ORDER BY id DESC LIMIT 1 ;";
    $return = $PDO->query($query)->fetch()["date"];
    if($return){return $return;}
    else{return False;}
}
function edit(){
    global $PDO;
    $query = "SELECT * FROM event ORDER BY date;";
?>
        <table class="table table-condensed">
            <tr>
                <th>編集</th>
            </tr>
<?php foreach($PDO->query($query) as $value): ?>           
            <tr<?php if($value["status"] == "hidden"){echo ' class="table-secondary"';} ?>>
                <td>
                    <form class="row" method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" value="<?php echo $value["id"] ?>" name="id" />
                        <div class="form-group col-md-3 col-lg-2">
                            <input type="text" name="date" class="form-control" value="<?php echo $value["date"]; ?>" placeholder="日付" />
                        </div>
                        <div class="form-group col-md-5 col-lg-7">
                            <input type="text" name="event" class="form-control" value="<?php echo $value["event"]; ?>" placeholder="できごと" />
                        </div>
                        <div class="form-group col-md-4 col-lg-3 text-right">
                            <button type="submit" class="btn btn-info" name="action" value="update">更新</button>
                            <button type="submit" class="btn btn-danger" name="action" value="delete">削除</button>
                            <?php if($value["status"] == "visible"): ?>
                                <button type="submit" class="btn btn-secondary" name="action" value="hidden">非表示</button>
                            <?php endif; ?>
                            <?php if($value["status"] == "hidden"): ?>
                                <button type="submit" class="btn btn-light" name="action" value="visible">再表示</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </td>
            </tr>
<?php endforeach; ?>
        </table>
        <div class="text-right"><a class="btn btn-primary" href="./">戻る</a></div>
<?php
}
function is_record_exists($id){
    global $PDO;
    $query = "SELECT * FROM event WHERE id = $id";
    if($PDO->query($query)->fetch()){return true;}else{return false;}
}
function update_record($id,$date,$event){
    global $PDO;
    $prepare = "UPDATE event SET date = :date , event = :event WHERE id = :id;";
    $stmt = $PDO->prepare($prepare);
    $stmt->bindParam(":id",$id);
    $stmt->bindParam(":date",$date);
    $stmt->bindParam(":event",$event);
    if($stmt->execute()){return "レコードを更新しました。";}
    else{return "レコードの更新に失敗しました。";}
}
function switch_status($id,$status){
    global $PDO;
    if($status == "visible" OR $status == "hidden"){}
    else{return "ステータス情報が不正です。";}
    $prepare = "UPDATE event SET status = :status WHERE id = :id;";
    $stmt = $PDO->prepare($prepare);
    $stmt->bindParam(":id",$id);
    $stmt->bindParam(":status",$status);
    if($status == "hidden" && $stmt->execute()){return "レコードを非表示にしました。";}
    elseif($status == "visible" && $stmt->execute()){return "レコードを表示します。";}
    else{return "レコードの表示切り替えに失敗しました。";}
}
function output_html(){
    if($_GET["p"] == "edit"){edit();}
    else{display();}
}
function execution(){
    if($_POST["action"] == "register"){
        if($_POST["date"] && $_POST["event"]){echo insert_record($_POST["date"],$_POST["event"]);}
        else{echo "データが入力されていません。";}
    }
    //if(!is_numric($_POST["id"])){echo "IDが不正です。";}
    if($_POST["action"] == "update"){
        if($_POST["id"] && $_POST["date"] && $_POST["event"]){
            echo update_record($_POST["id"],$_POST["date"],$_POST["event"]);
        }
        else{echo "入力されたデータに不備があります。";}
    }
    if($_POST["action"] == "hidden"){echo switch_status($_POST["id"],"hidden");}
    if($_POST["action"] == "visible"){echo switch_status($_POST["id"],"visible");}
    if($_POST["action"] == "delete"){echo delete_record($_POST["id"]);}
}