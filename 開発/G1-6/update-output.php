<?php
require '../others/db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $new_user_name = $_POST['new_user_name'];
    $new_post_id = $_POST['new_post_id'];
    $old_user_name = $_POST['old_user_name'];
    $old_post_name = $_POST['old_post_name'];

    // 同じユーザー名が既に存在するかを確認
    $check_user_sql = "SELECT COUNT(*) FROM User WHERE user_name = :new_user_name AND user_name <> :old_user_name";
    $check_user_stmt = $pdo->prepare($check_user_sql);
    $check_user_stmt->bindParam(':new_user_name', $new_user_name, PDO::PARAM_STR);
    $check_user_stmt->bindParam(':old_user_name', $old_user_name, PDO::PARAM_STR);
    $check_user_stmt->execute();
    $user_count = $check_user_stmt->fetchColumn();

    if ($user_count > 0) {
        echo "Error: 新しいユーザー名が既に存在します。別のユーザー名を選択してください。<a href='update.php'>ホームに戻る</a>";
    } else {
        // 変更前のユーザー情報を取得
        $old_user_info_sql = "SELECT * FROM User WHERE user_name=:old_user_name";
        $old_user_info_stmt = $pdo->prepare($old_user_info_sql);
        $old_user_info_stmt->bindParam(':old_user_name', $old_user_name, PDO::PARAM_STR);
        $old_user_info_stmt->execute();
        $old_user_info = $old_user_info_stmt->fetch(PDO::FETCH_ASSOC);

        // ユーザーテーブルのユーザー名変更
        $update_user_sql = "UPDATE User SET user_name=:new_user_name, post_id=:new_post_id WHERE user_name=:old_user_name";
        $update_user_stmt = $pdo->prepare($update_user_sql);
        $update_user_stmt->bindParam(':new_user_name', $new_user_name, PDO::PARAM_STR);
        $update_user_stmt->bindParam(':new_post_id', $new_post_id, PDO::PARAM_INT);
        $update_user_stmt->bindParam(':old_user_name', $old_user_name, PDO::PARAM_STR);
        $update_user_stmt->execute();

        // 変更後のユーザー情報を取得
        $new_user_info_sql = "SELECT * FROM User WHERE user_name=:new_user_name";
        $new_user_info_stmt = $pdo->prepare($new_user_info_sql);
        $new_user_info_stmt->bindParam(':new_user_name', $new_user_name, PDO::PARAM_STR);
        $new_user_info_stmt->execute();
        $new_user_info = $new_user_info_stmt->fetch(PDO::FETCH_ASSOC);

        // 変更前のポスト名を取得
        $old_post_info_sql = "SELECT Post.post_name FROM Post WHERE Post.post_id = (SELECT post_id FROM User WHERE user_name = :old_user_name)";
        $old_post_info_stmt = $pdo->prepare($old_post_info_sql);
        $old_post_info_stmt->bindParam(':old_user_name', $old_user_name, PDO::PARAM_STR);
        $old_post_info_stmt->execute();
        $old_post_info = $old_post_info_stmt->fetch(PDO::FETCH_ASSOC);

        // 変更後のポスト名を取得
        $new_post_info_sql = "SELECT Post.post_name FROM Post WHERE Post.post_id = :new_post_id";
        $new_post_info_stmt = $pdo->prepare($new_post_info_sql);
        $new_post_info_stmt->bindParam(':new_post_id', $new_post_id, PDO::PARAM_INT);
        $new_post_info_stmt->execute();
        $new_post_info = $new_post_info_stmt->fetch(PDO::FETCH_ASSOC);

        echo "更新が完了しました。<br>";

        echo "<p>変更後のユーザー名: " . htmlspecialchars($new_user_name) . "</p>";
        echo "<p>変更後のポスト名: " . htmlspecialchars($new_post_info['post_name']) . "</p>";

        echo "<br><a href='update.php'>ホームに戻る</a>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $pdo = null; // データベース接続解除
}
?>
