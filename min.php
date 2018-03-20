<?php

/*MySQL
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
$query = "CREATE TABLE IF NOT EXISTS event (id INT NOT NULL UNIQUE PRIMARY KEY AUTO_INCREMENT,date DATE NOT NULL,event TEXT NOT NULL);";
$PDO->query($query);
/**/

/*sqlite
try{
    $PDO = new PDO("sqlite:event.db");
}
catch (Exception $e) {
    echo '<h1 style="color:red">データベース接続エラー</h1>'.PHP_EOL;
    echo "<p>".$e->getMessage()."</p>";
    exit;
}
$query = "CREATE TABLE IF NOT EXISTS event (id INTEGER NOT NULL UNIQUE PRIMARY KEY AUTOINCREMENT,date DATE NOT NULL,event TEXT NOT NULL);";
$PDO->query($query);
/**/

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>年表生成ツール</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
</head>
<body>
    <div class="container">
        <h1><a href="">年表作成ツール</a></h1>
        
        <?php if($_GET["p"] == "edit"){edit();}else{display();} ?>

        <?php if($_POST){action();} ?>

        <form class="row" method="post" action="" enctype="multipart/form-data">
            <div class="form-group col-sm-3 col-md-2">
                <input type="text" name="date" class="form-control" value="<?php echo last_recode(); ?>" placeholder="日付" />
            </div>
            <div class="form-group col-sm-8 col-md-9">
                <input type="text" name="event" class="form-control" placeholder="できごと" />
            </div>
            <div class="form-group col-sm-1 pull-right">
                <input type="submit" class="btn btn-info" />
            </div>
        </form>
    </div>
</body>
</html>
<?php
exit;

function display(){
    global $PDO;
    $query = "SELECT * FROM event ORDER BY date;";
?>
        <table class="table table-condensed">
            <tr>
                <th>日付</th>
                <th>できごと</th>
            </tr>
    <?php foreach($PDO->query($query) as $value): ?>
            <tr>
                <td><?php echo $value["date"] ?></td>
                <td><?php echo $value["event"] ?></td>
            </tr>
    <?php endforeach; ?>
        </table>
<?php
}

function edit(){
    global $PDO;
    $query = "SELECT * FROM event ORDER BY date;";
?>
        <table class="table table-condensed">
            <tr>
                <th>日付</th>
                <th>できごと</th>
                <th></th>
            </tr>
    <?php foreach($PDO->query($query) as $value): ?>
            <tr>
                <td><?php echo $value["date"] ?></td>
                <td><?php echo $value["event"] ?></td>
                <td>
                    <form method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" value="<?php echo $value["id"] ?>" name="id" />
                        <input type="submit" class="btn btn-default" value="削除" />
                    </form>
                </td>
            </tr>
    <?php endforeach; ?>
        </table>
<?php
}

function insert_recode($date,$event){
    global $PDO;

    $prepare = "INSERT INTO event VALUES (0,:date,:event);";
    $stmt = $PDO->prepare($prepare);
    $stmt->bindParam(":date",$date);
    $stmt->bindParam(":event",$event);

    if($stmt->execute()){return "レコードを登録しました。";}
    else{return "レコードの登録に失敗しました。";}

}

function delete_recode($id){
    global $PDO;

    $query = "SELECT * FROM event WHERE id = $id";
    if(!$PDO->query($query)->fetch()){return "指定されたレコードが存在しません。";};

    $query = "DELETE FROM event WHERE id = $id";
    if($PDO->query($query)){return "レコードを消去しました。";}
    else{return "レコードの削除に失敗しました。";}

}

function action(){
    if($_POST["date"] && $_POST["event"]){echo insert_recode($_POST["date"],$_POST["event"]);}
    elseif($_POST["id"]){echo delete_recode($_POST["id"]);}
    else{echo "データが入力されていません。";}
}

function last_recode(){
    global $PDO;
    $query = "SELECT date FROM event ORDER BY id DESC LIMIT 1 ;";
    $return = $PDO->query($query)->fetch()["date"];
    if($return){return $return;}
    else{return false;}
}