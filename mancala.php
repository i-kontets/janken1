<?php

// ----------------------------------------------------
// 1. 定数と初期設定
// ----------------------------------------------------

// 穴のインデックスとプレイヤーの定義
// プレイヤーA (人間): 0-5 (穴), 6 (ストア)
// プレイヤーB (コンピュータ): 7-12 (穴), 13 (ストア)
const STORE_A = 6;
const STORE_B = 13;
const HOLES_A = [0, 1, 2, 3, 4, 5];
const HOLES_B = [7, 8, 9, 10, 11, 12];
const INITIAL_STONES = 4;
const BOARD_SIZE = 14;

// ターンの定義
const PLAYER_A = 1; // 人間
const PLAYER_B = 2; // コンピュータ

// セッション開始（状態管理のため）
session_start();

// ----------------------------------------------------
// 2. ゲーム状態の初期化と取得
// ----------------------------------------------------

/**
 * ゲームを初期状態にリセットする
 * @return array ボードの状態
 */
function initialize_board() {
    $board = array_fill(0, BOARD_SIZE, INITIAL_STONES);
    $board[STORE_A] = 0; // プレイヤーAのストア
    $board[STORE_B] = 0; // プレイヤーBのストア

    // ストアの位置は穴ではないので、初期石数を入れる必要はない
    $board[STORE_A] = 0;
    $board[STORE_B] = 0;

    // 穴に初期石を設定
    foreach (HOLES_A as $i) { $board[$i] = INITIAL_STONES; }
    foreach (HOLES_B as $i) { $board[$i] = INITIAL_STONES; }
    
    $_SESSION['board'] = $board;
    $_SESSION['current_turn'] = PLAYER_A;
    $_SESSION['message'] = 'あなたのターンです。穴を選んでください。';
    $_SESSION['game_over'] = false;
    return $board;
}

// リセット要求またはセッションがない場合は初期化
if (isset($_POST['reset']) || !isset($_SESSION['board'])) {
    initialize_board();
}

$board = &$_SESSION['board'];
$current_turn = &$_SESSION['current_turn'];
$message = &$_SESSION['message'];
$game_over = &$_SESSION['game_over'];

// ----------------------------------------------------
// 3. ゲームロジック
// ----------------------------------------------------

/**
 * ターン処理を実行する
 * @param int $start_index 石を拾い上げる穴のインデックス
 * @param array $board ボードの状態
 * @param int $player 現在のプレイヤー (PLAYER_A or PLAYER_B)
 * @return int 次のターンプレイヤー (同じプレイヤーまたは相手プレイヤー)
 */
function make_move(&$board, $start_index, $player) {
    global $message;

    // 1. 石の数と穴のクリア
    $stones = $board[$start_index];
    $board[$start_index] = 0;
    $current_index = $start_index;

    // 2. 石を配る
    while ($stones > 0) {
        $current_index = ($current_index + 1) % BOARD_SIZE;

        // 相手のストアはスキップ
        if ($player === PLAYER_A && $current_index === STORE_B) {
            continue;
        }
        if ($player === PLAYER_B && $current_index === STORE_A) {
            continue;
        }

        $board[$current_index]++;
        $stones--;
    }

    // 3. 最後の石が入った場所の判定
    $last_index = $current_index;
    $next_turn = ($player === PLAYER_A) ? PLAYER_B : PLAYER_A;
    $is_store = ($player === PLAYER_A && $last_index === STORE_A) || ($player === PLAYER_B && $last_index === STORE_B);
    $is_empty_hole = ($player === PLAYER_A && in_array($last_index, HOLES_A) && $board[$last_index] === 1) || 
                     ($player === PLAYER_B && in_array($last_index, HOLES_B) && $board[$last_index] === 1);

    // 3a. もう一度手番 (ストアに入った場合)
    if ($is_store) {
        $next_turn = $player;
        $message = ($player === PLAYER_A ? 'あなた' : 'コンピュータ') . 'のストアに最後に入りました！もう一度ターンです。';
        return $next_turn;
    }

    // 3b. キャプチャ (自分の空の穴に最後に入り、向かいに石がある場合)
    if ($is_empty_hole) {
        // 向かいの穴のインデックスを計算
        $opponent_index = BOARD_SIZE - 2 - $last_index; 
        
        if ($board[$opponent_index] > 0) {
            $capture_stones = $board[$opponent_index] + $board[$last_index];
            $board[$opponent_index] = 0;
            $board[$last_index] = 0;
            
            $target_store = ($player === PLAYER_A) ? STORE_A : STORE_B;
            $board[$target_store] += $capture_stones;

            $message = ($player === PLAYER_A ? 'あなた' : 'コンピュータ') . 'がキャプチャに成功しました！';
            // キャプチャ後、ターンは相手に移る
            return $next_turn;
        }
    }

    // 4. 次のターンへ
    $message = ($next_turn === PLAYER_A) ? 'あなたのターンです。穴を選んでください。' : 'コンピュータのターンです。お待ちください...';
    return $next_turn;
}

/**
 * ゲーム終了判定と終了処理
 */
function check_game_over(&$board) {
    global $game_over, $message;
    
    $is_a_empty = true;
    foreach (HOLES_A as $i) {
        if ($board[$i] > 0) {
            $is_a_empty = false;
            break;
        }
    }

    $is_b_empty = true;
    foreach (HOLES_B as $i) {
        if ($board[$i] > 0) {
            $is_b_empty = false;
            break;
        }
    }

    if ($is_a_empty || $is_b_empty) {
        $game_over = true;

        // 残りの石をそれぞれのストアに入れる
        foreach (HOLES_A as $i) { $board[STORE_A] += $board[$i]; $board[$i] = 0; }
        foreach (HOLES_B as $i) { $board[STORE_B] += $board[$i]; $board[$i] = 0; }

        // 勝敗判定
        if ($board[STORE_A] > $board[STORE_B]) {
            $final_result = "あなたの勝利です！";
        } elseif ($board[STORE_A] < $board[STORE_B]) {
            $final_result = "コンピュータの勝利です。";
        } else {
            $final_result = "引き分けです。";
        }

        $message = "ゲーム終了！ (A: {$board[STORE_A]}, B: {$board[STORE_B]}) {$final_result}";
    }
}

/**
 * コンピュータ (PLAYER_B) の手番処理
 */
function computer_move(&$board) {
    // 非常にシンプルなランダムAI
    $valid_moves = [];
    foreach (HOLES_B as $i) {
        if ($board[$i] > 0) {
            $valid_moves[] = $i;
        }
    }

    if (!empty($valid_moves)) {
        // ランダムに手を選択
        $chosen_index = $valid_moves[array_rand($valid_moves)];
        $GLOBALS['current_turn'] = make_move($board, $chosen_index, PLAYER_B);
    }
}

// ----------------------------------------------------
// 4. メイン処理と入力受付
// ----------------------------------------------------

if (!$game_over) {
    if ($current_turn === PLAYER_A && isset($_POST['move_index'])) {
        $move_index = (int)$_POST['move_index'];
        
        // 入力チェック (自分の穴であり、石があること)
        if (in_array($move_index, HOLES_A) && $board[$move_index] > 0) {
            $current_turn = make_move($board, $move_index, PLAYER_A);
        } else {
            $message = "無効な手です。石が入っているあなたの側の穴(0-5)を選んでください。";
        }
        check_game_over($board);
    }

    // コンピュータのターン処理 (リダイレクトなどで回避すべきだが、シンプル実装のため即時実行)
    if (!$game_over && $current_turn === PLAYER_B) {
        // 短いディレイを挟むことで、処理中であることを表現
        // sleep(1); // Webサーバーによっては動かないためコメントアウト
        computer_move($board);
        check_game_over($board);
    }
}

// ----------------------------------------------------
// 5. HTML出力とUI (CSS込み)
// ----------------------------------------------------
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マンカラゲーム</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap');
        
        body {
            font-family: 'Noto Sans JP', sans-serif;
            text-align: center;
            background-color: #34495e; /* ダークブルーの背景 */
            color: #ecf0f1;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 95%;
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: #2c3e50; /* 濃い目のコンテナ */
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        h1 {
            color: #ecf0f1;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .message-box {
            background-color: #3498db; /* メッセージボックスの色 */
            color: white;
            padding: 10px;
            margin: 15px 0;
            border-radius: 8px;
            font-weight: 700;
        }

        /* ボードデザイン */
        .board {
            display: grid;
            grid-template-columns: 1fr repeat(6, 80px) 1fr; /* store(1) holes(6) store(1) */
            grid-template-rows: 80px 80px; /* Bの穴, Aの穴 */
            gap: 10px;
            margin: 30px 0;
            align-items: center;
            justify-content: center;
        }
        
        /* 穴とストアの共通スタイル */
        .hole, .store {
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            font-weight: 700;
            color: #343a40;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.4);
            transition: all 0.2s;
            position: relative;
        }

        /* 穴 (Holes) */
        .hole {
            width: 80px;
            height: 80px;
            background-color: #e74c3c; /* 赤茶色の穴 */
        }
        
        /* ストア (Stores) */
        .store {
            border-radius: 40px;
            width: 60px;
            height: 180px;
            background-color: #2ecc71; /* 緑色のストア */
            color: white;
            font-size: 1.5em;
        }

        /* Grid配置 */
        .store-b { grid-column: 8; grid-row: 1 / 3; }
        .store-a { grid-column: 1; grid-row: 1 / 3; }
        
        /* B (コンピュータ) の穴 */
        .hole-b { grid-row: 1; background-color: #f1c40f; /* 黄色 */ }
        /* A (人間) の穴 */
        .hole-a { grid-row: 2; background-color: #e74c3c; /* 赤 */ }
        
        /* 穴のインデックス表示 (デバッグ用) */
        .hole::after {
            content: attr(data-index);
            position: absolute;
            bottom: -20px;
            font-size: 0.7em;
            color: #bdc3c7;
            font-weight: 400;
        }
        
        /* Aの穴のインデックスを上に表示 */
        .hole-a::after { top: -20px; bottom: initial; }

        /* ボタンの配置とスタイル */
        .move-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            margin: 0 auto;
            color: #343a40; /* 石の数が見えるように */
            font-size: 1.2em;
            font-weight: 700;
        }
        .move-button:hover:not(:disabled) {
            outline: 3px solid #3498db; /* ホバー時の青い枠線 */
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.8);
        }
        .move-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .reset-button {
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #95a5a6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.3s;
        }
        .reset-button:hover {
            background-color: #7f8c8d;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>💎 マンカラゲーム</h1>
    
    <div class="message-box">
        <?php echo htmlspecialchars($message); ?>
    </div>

    <form method="POST" action="mancala.php">
        <div class="board">
            
            <div class="store store-b">
                <?php echo $board[STORE_B]; ?>
                <span style="position: absolute; bottom: 5px; font-size: 0.8em;">Bストア</span>
            </div>

            <?php foreach (array_reverse(HOLES_B) as $index): // 奥側は逆順に表示 ?>
                <div class="hole hole-b" style="grid-column: <?php echo 7 - $index + 7; ?>" data-index="<?php echo $index; ?>">
                    <?php echo $board[$index]; ?>
                </div>
            <?php endforeach; ?>

            <?php foreach (HOLES_A as $index): ?>
                <button 
                    type="submit" 
                    name="move_index" 
                    value="<?php echo $index; ?>" 
                    class="move-button hole hole-a"
                    style="grid-column: <?php echo $index + 2; ?>"
                    data-index="<?php echo $index; ?>"
                    <?php echo ($current_turn !== PLAYER_A || $board[$index] === 0 || $game_over) ? 'disabled' : ''; ?>
                >
                    <?php echo $board[$index]; ?>
                </button>
            <?php endforeach; ?>

            <div class="store store-a">
                <?php echo $board[STORE_A]; ?>
                <span style="position: absolute; bottom: 5px; font-size: 0.8em;">Aストア</span>
            </div>

        </div>
        
        <?php if ($game_over): ?>
            <button type="submit" name="reset" class="reset-button">新しいゲームを始める</button>
        <?php else: ?>
            <button type="submit" name="reset" class="reset-button">リセット</button>
        <?php endif; ?>
    </form>

</div>

</body>
</html>