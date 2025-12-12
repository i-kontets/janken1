<?php

// ----------------------------------------------------
// 1. 定数と初期設定
// ----------------------------------------------------

// じゃけんの手の定義
const HANDS = [
    1 => 'グー',
    2 => 'チョキ',
    3 => 'パー',
];

// 初期メッセージ
$message = "じゃんけん！ '1:グー', '2:チョキ', '3:パー' のどれかを入力してください。";
$user_hand_name = '';
$computer_hand_name = '';
$result = '';

// ----------------------------------------------------
// 2. ユーザーからの入力処理
// ----------------------------------------------------

// POSTデータが送信されたかチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = filter_input(INPUT_POST, 'hand', FILTER_VALIDATE_INT);

    // 入力値のバリデーション
    if ($user_input === false || !isset(HANDS[$user_input])) {
        // 不正な入力の場合
        $message = "無効な入力です。'1', '2', '3' のいずれかを入力してください。";
    } else {
        // ----------------------------------------------------
        // 3. ゲームロジックの実行
        // ----------------------------------------------------

        // ユーザーの手を確定
        $user_hand = $user_input;
        $user_hand_name = HANDS[$user_hand];

        // コンピュータの手をランダムで決定
        $computer_hand = array_rand(HANDS);
        $computer_hand_name = HANDS[$computer_hand];

        // 勝敗判定ロジック
        $diff = $user_hand - $computer_hand;

        if ($diff === 0) {
            $result = "あいこ！";
            $message = "もう一度！ '1:グー', '2:チョキ', '3:パー' のどれかを入力してください。";
        } elseif ($diff === 2 || $diff === -1) {
            $result = "あなたの勝ち！";
            $message = "おめでとう！新しいゲームを始めるには、再度入力してください。";
        } else { // $diff === 1 || $diff === -2
            $result = "コンピュータの勝ち！";
            $message = "残念... 新しいゲームを始めるには、再度入力してください。";
        }
    }
}

// ----------------------------------------------------
// 4. HTML出力
// ----------------------------------------------------
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>じゃんけんゲーム</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap');
        
        body {
            font-family: 'Noto Sans JP', sans-serif;
            text-align: center;
            background-color: #eef4f8; /* 薄い水色の背景 */
            color: #333;
            margin: 0;
            padding-top: 50px;
        }
        .container {
            width: 90%;
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            display: inline-block;
            padding-bottom: 5px;
        }
        h2 {
            font-size: 1.2em;
            color: #34495e;
            margin-bottom: 25px;
        }
        hr {
            border: 0;
            height: 1px;
            background-color: #ecf0f1;
            margin: 20px 0;
        }

        /* 結果表示ボックスのスタイル */
        .result-box {
            margin-bottom: 30px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .result-box p {
            margin: 5px 0;
            font-size: 1.1em;
        }
        .result-text {
            font-size: 1.8em;
            font-weight: 700;
            margin-top: 10px;
            padding: 5px 0;
            border-radius: 5px;
            animation: pulse 1s infinite alternate;
        }

        /* 勝敗ごとの色の定義 */
        .win {
            color: #27ae60; /* エメラルドグリーン */
            background-color: #e8f8f5;
        }
        .lose {
            color: #e74c3c; /* 赤 */
            background-color: #fdedec;
        }
        .draw {
            color: #f39c12; /* オレンジ */
            background-color: #fef9e7;
        }

        /* じゃんけんボタンのスタイル */
        .hand-buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
        }
        .hand-buttons button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 15px;
            transition: all 0.3s ease;
            width: 100px;
            height: 100px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .hand-buttons button:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .hand-buttons button:active {
            transform: translateY(0);
        }
        
        .hand-buttons button span {
            font-size: 40px; /* 絵文字のサイズ */
            display: block;
            margin-bottom: 5px;
        }
        
        /* ボタンの色付け（今回は絵文字主体なので背景色は統一） */
        .hand-buttons button[value="1"] { background-color: #f4f6f6; } /* グー */
        .hand-buttons button[value="2"] { background-color: #f4f6f6; } /* チョキ */
        .hand-buttons button[value="3"] { background-color: #f4f6f6; } /* パー */

        /* アニメーション */
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.03); opacity: 0.95; }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>✊✌️✋ じゃんけんゲーム </h1>

    <?php if ($result): ?>
        <div class="result-box">
            <p><strong>あなたの手:</strong> <?php echo htmlspecialchars($user_hand_name); ?></p>
            <p><strong>コンピュータの手:</strong> <?php echo htmlspecialchars($computer_hand_name); ?></p>
            <p class="result-text 
                <?php if ($result === 'あなたの勝ち！') echo 'win'; 
                      elseif ($result === 'コンピュータの勝ち！') echo 'lose';
                      else echo 'draw'; 
                ?>
            ">
                <?php echo htmlspecialchars($result); ?>
            </p>
        </div>
    <?php endif; ?>

    <hr>
    
    <h2><?php echo htmlspecialchars($message); ?></h2>

    <form method="POST" action="">
        <div class="hand-buttons">
            <button type="submit" name="hand" value="1">
                <span>✊</span>グー
            </button>
            <button type="submit" name="hand" value="2">
                <span>✌️</span>チョキ
            </button>
            <button type="submit" name="hand" value="3">
                <span>✋</span>パー
            </button>
        </div>
    </form>

    <p style="margin-top: 30px; font-size: 0.8em; color: #7f8c8d;">※ グー(1), チョキ(2), パー(3) の数値の差分で勝敗を判定しています。</p>

</div>

</body>
</html>
