<?php
define('MAX_WORDS', 500);
$error = [];
$success = [];

//xpuc7o
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// written in early 2000s
function valid($valid)
{
    if ($valid == "") 
        return false;
    

    $split = preg_split('//u', $valid, null, PREG_SPLIT_NO_EMPTY);
    $allowed_chars = "абвгдежзийклмнопрстуфхцчшщъьюяАБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪѝЮЯ ,";

    for ($i = 0; $i < mb_strlen($valid); ++$i) {
        if (mb_strpos($allowed_chars, $split[$i]) === false) 
            return false;
    }

    return true;
}

$db_conn = mysqli_connect("localhost", "skribbl", "HT5T8Sup98jGS5pq", "skribbl");

// only do work if have a "POST"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // split words
    $words = explode(",", $_POST["gorgon"]);
    $q = "INSERT INTO words (`word`) VALUES(?)";
    $stmt = mysqli_prepare($db_conn, $q);
    mysqli_stmt_bind_param($stmt, "s", $v);

    foreach ($words as $v) {
        $v = trim($v);

        if (!valid($v)) {
            $error[] = "$v не е валидна дума";
            continue;
        }
        if (mb_strlen($v) > 255) {
            $error[] = "$v е прекалено дълга дума";
            continue;
        }

        $v = mb_strtolower($v, 'UTF-8');

        if (mysqli_stmt_execute($stmt)) {
            $success[] = "$v беше успешно добавена в базата данни";
        }
    }

    mysqli_stmt_close($stmt);

    if (!mysqli_query($db_conn, "COMMIT")) {
        $error[] = mysqli_error($db_conn);
    }
}

if (isset($_GET['generate'])) {
    $q = mysqli_query($db_conn, "select * from words where `ban` = 0 order by rand() limit " . MAX_WORDS);
    $words = [];

    while ($row = mysqli_fetch_array($q, MYSQLI_ASSOC)) {
        $words[] = $row['word'];
    }

    print(implode(',', $words));

    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Skribbl Dictionary Maker</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            min-height: 100vh;
            padding: 0;
            margin: 0;
            font-family: Roboto, Arial, sans-serif;
            font-size: 14px;
            color: #666;
        }

        input, textarea {
            outline: none;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: #5a7233;
        }

        h1 {
            margin-top: 0;
            font-weight: 500;
        }

        form {
            position: relative;
            width: 80%;
            border-radius: 30px;
            background: #fff;
        }

        .form-left-decoration,
        .form-right-decoration {
            content: "";
            position: absolute;
            width: 50px;
            height: 20px;
            border-radius: 20px;
            background: #5a7233;
        }

        .form-left-decoration {
            bottom: 60px;
            left: -30px;
        }

        .form-right-decoration {
            top: 60px;
            right: -30px;
        }

        .form-left-decoration:before,
        .form-left-decoration:after,
        .form-right-decoration:before,
        .form-right-decoration:after {
            content: "";
            position: absolute;
            width: 50px;
            height: 20px;
            border-radius: 30px;
            background: #fff;
        }

        .form-left-decoration:before {
            top: -20px;
        }

        .form-left-decoration:after {
            top: 20px;
            left: 10px;
        }

        .form-right-decoration:before {
            top: -20px;
            right: 0;
        }

        .form-right-decoration:after {
            top: 20px;
            right: 10px;
        }

        .circle {
            position: absolute;
            bottom: 80px;
            left: -55px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
        }

        .form-inner {
            padding: 40px;
        }

        .form-inner input,
        .form-inner textarea {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 10px;
            border: none;
            border-radius: 20px;
            background: #d0dfe8;
        }

        .form-inner textarea {
            resize: none;
        }

        .success {
            padding: 5px;
            color: #5a7233;
        }

        .error {
            padding: 5px;
            color: red;
        }

        .btn,
        .btn:visited,
        button {
            width: 100%;
            padding: 10px 20px;
            margin-top: 20px;
            border-radius: 20px;
            border: none;
            border-bottom: 4px solid #3e4f24;
            background: #5a7233;
            font-size: 16px;
            font-weight: 400;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        .btn,
        .btn:hover,
        button:hover {
            background: #3e4f24;
        }

        @media (min-width: 568px) {
            form {
                width: 60%;
            }
        }

        .mt-2 {
            margin-top: 20px;
        }

        .mt-3 {
            margin-top: 30px;
        }

        .d-flex {
            display: flex;
        }

        .space-between {
            justify-content: space-between;
        }
    </style>
</head>
<body>
<form method="post" action="/" class="decor">
    <div class="form-left-decoration"></div>
    <div class="form-right-decoration"></div>
    <div class="circle"></div>
    <div class="form-inner">
        <h1>Моля въведете грамотно написани думи на български език, разделени със запетайки.</h1>

        <div class="d-flex space-between">
            <div>
                <?php
                $q = mysqli_query($db_conn, "select * from words where `ban` = 0");
                $total_num = mysqli_num_rows($q);
                ?>
                Общ брой думи: <?= $total_num; ?>
            </div>
            <div>
                <a href="?generate" class="btn">Изтегляне на 500 думи</a>
            </div>
        </div>

        <div class="mt-3">
            <?php if (count($success)) { ?>
                <div class="success">
                    <?php foreach ($success as $v) { ?>
                        <p><?= $v ?></p>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if (count($error)) { ?>
                <div class="error">
                    <?php foreach ($error as $v) { ?>
                        <p><?= $v ?></p>
                    <?php } ?>
                </div>
            <?php } ?>
            <textarea placeholder="Message..." rows="5" name="gorgon"></textarea>
            <button type="submit" class="mt-2">Enter</button>
        </div>
</form>
</body>
</html>
