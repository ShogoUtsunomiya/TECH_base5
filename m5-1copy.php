<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <title>m5-1</title>
    </head>
    <body>
        <?php
            //SQL
            //データベース接続
            $dsn = 'mysql:dbname=データベース名;host=localhost';
            $user = 'ユーザー名';
            $password_sql = 'パスワード';
            $pdo = new PDO($dsn, $user, $password_sql, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            //テーブル作成
            $sql = 'CREATE TABLE IF NOT EXISTS tbtest'
            ." ("
            . "id INT AUTO_INCREMENT PRIMARY KEY,"
            . "name CHAR(32),"
            . "comment TEXT,"
            . "password CHAR(5),"
            . "created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,"
            . "updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
            .");";
            $stmt = $pdo->query($sql);
            //初期値設定
            $edit_now=-1; //新規投稿か編集内容投稿かを判別するための初期値設定
            $edit_name="";
            $edit_comment="";
            $edit_password="";
            //編集したい情報を入力欄に載せるためのデータ取得とその定義づけ
            if (!empty($_POST["edit"]) && !empty($_POST["submit_edit"])) {
                $id = $_POST["edit"];
                //SQL
                //SELECTで名前とコメントを取得、情報を関数に格納PHPでフォームにechoで表示
                $sql = 'SELECT * FROM tbtest WHERE id=:id ';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt -> fetch();

                $edit_name = $result['name'];
                $edit_comment = $result['comment'];
                $edit_password = $result['password'];
                $edit_now = $result['id'];
            }
        ?>
        <!--フォーム-->
        <form action="" method="post">
            <!--新規投稿-->
            <input type="text" name="name" placeholder="名前" value=<?php echo $edit_name?>>
            <input type="text" name="comment" placeholder="コメント" value=<?php echo $edit_comment?>>
            <input type="text" name="password" placeholder="5つの番号で設定" value=<?php echo $edit_password?>>
            <input type="submit" name="submit">
            <br>
            <!--削除-->
            <input type="text" name="delete" placeholder="削除番号">
            <input type="text" name="delete_password" placeholder="パスワード">
            <input type="submit" name="submit_delete" value="削除"><br>
            <!--編集機能-->
            <input type="text" name="edit" placeholder="編集番号">
            <input type="text" name="edit_password" placeholder="パスワード">
            <input type="submit" name="submit_edit" value="編集">
            <input type="hidden" name="edit_num" placeholder="編集中の番号" value=<?php echo $edit_now?>>
            
        </form>

        <?php
            //新規投稿フォーム
            if (!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["password"]) && !empty($_POST["submit"])) {
                //新規投稿処理
                if ($_POST["edit_num"] == -1) {
                    $name = $_POST["name"];
                    $comment = $_POST["comment"];
                    $password = $_POST["password"];
                    //SQL
                    $sql = "INSERT INTO tbtest (name, comment, password) VALUES (:name, :comment, :password)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt->execute();
                    echo "投稿成功<br>";
                }
                //編集内容投稿処理
                else {
                    $id = $_POST["edit_num"];
                    $edit_password = $_POST["edit_password"];
                    $edit_name = $_POST["name"];
                    $edit_comment = $_POST["comment"];
                    $edit_password = $_POST["password"];
                    //SQL
                    $sql = 'UPDATE tbtest SET name=:name,comment=:comment, password=:password WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $edit_name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $edit_comment, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $edit_password, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();                
                }
            }

            //削除処理
            if (!empty($_POST["delete"]) && !empty($_POST["submit_delete"]) && !empty($_POST["delete_password"])) {
                $id = $_POST["delete"];
                $delete_password = $_POST["delete_password"];
                //SQL
                $sql = 'SELECT password FROM tbtest WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt -> fetch();
                //$resultでDBからの取得が成功してるかどうか確認→パスワード一致の確認
                if ($result && $delete_password === $result['password']) {
                    $sql = 'delete from tbtest where id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                    echo "削除完了<br>";
                } else {
                    echo "パスワードが違います<br>";
                }
            }
            //SQL
            //DB表示
            $sql = 'SELECT * FROM tbtest';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
                echo $row['id'].',';
                echo $row['name'].',';
                echo $row['comment'].',';
                echo $row['password'].',';
                $formattedCreatedAt = date('Y-m-d H:i:s', strtotime($row['created_at']));
                echo $formattedCreatedAt.',';
                echo "<hr>";
            }
        ?>
    </body>
</html>