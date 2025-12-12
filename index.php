<?php
// リンク先のゲームリストを定義
$games = [
    [
        'name' => 'じゃんけんゲーム',
        'link' => './janken.php',
        'description' => 'コンピュータと対戦するじゃんけんゲームです。'
    ],
    // 今後ゲームを追加する際はここに追加できます
    // [
    //     'name' => '新しいゲーム',
    //     'link' => './new_game.php',
    //     'description' => '新しいゲームの説明です。'
    // ],
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ゲーム一覧</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap');
        
        body {
            font-family: 'Noto Sans JP', sans-serif;
            text-align: center;
            background-color: #f8f9fa; /* 明るいグレーの背景 */
            color: #343a40;
            margin: 0;
            padding-top: 50px;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff; /* プライマリーブルー */
            font-size: 2.2em;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 3px solid #007bff;
            display: inline-block;
        }

        /* ゲームリストのスタイル */
        .game-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }
        .game-list-item {
            margin-bottom: 15px;
            text-align: left;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .game-list-item:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
            background-color: #f1f7fe;
        }
        
        .game-link {
            display: block;
            text-decoration: none;
            color: inherit;
            padding: 15px 20px;
        }

        .game-title {
            font-size: 1.25em;
            font-weight: 700;
            color: #343a40;
            margin-bottom: 5px;
        }
        
        .game-description {
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🎮 ゲーム一覧</h1>

    <ul class="game-list">
        <?php foreach ($games as $game): ?>
        <li class="game-list-item">
            <a href="<?php echo htmlspecialchars($game['link']); ?>" class="game-link">
                <div class="game-title"><?php echo htmlspecialchars($game['name']); ?></div>
                <div class="game-description"><?php echo htmlspecialchars($game['description']); ?></div>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    
    <p style="margin-top: 40px; font-size: 0.85em; color: #adb5bd;">リストの項目をクリックしてゲームを開始してください。</p>

</div>

</body>
</html>