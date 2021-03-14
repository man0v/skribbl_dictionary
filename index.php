<?
//xpuc7o
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
// written in early 2000s
function valid($valid, $opt="")
{
        if ($valid == "")
          return false;

        $allowedchars = "абвгдежзийклмнопрстуфхцчшщъьюяАБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪѝЮЯ ,$opt";


        for ($i = 0; $i < mb_strlen($valid); ++$i)
          if (mb_strpos($allowedchars, $valid[$i]) === false)
            return false;

        return true;
}
$db_conn = mysqli_connect("localhost", "skribbl", "HT5T8Sup98jGS5pq", "skribbl");

	// only do work if have a "POST"
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		//con string to db
		
		// split words
		$words = explode(",", $_POST["gorgon"]);
		$q = "INSERT INTO words (`word`) VALUES(?)";
		$stmt = mysqli_prepare($db_conn, $q);
		mysqli_stmt_bind_param($stmt, "s", $v);
		foreach($words as $v) {
			$v = trim($v);
			if(!valid($v)) {
				$error[] = "$v не е валидна дума";
				continue;
			}
			if(mb_strlen($v) > 255) {
				$error[] = "$v е прекалено дълга дума";
				continue;
			}
			if(mysqli_stmt_execute($stmt))
				$success[] = "$v беше успешно добавена в базата данни";
		}
		mysqli_stmt_close($stmt);
		if(!mysqli_query($db_conn, "COMMIT"))
			$error[] = mysqli_error($db_conn);
	}
	if ($_GET['generate']) {
		if(isset($_GET['num']))
			$num = $_GET['num'] + 0;
		else
			$num = 500;
		$q = mysqli_query($db_conn, "select * from words where 1");
		while($row = mysqli_fetch_array($q, MYSQLI_ASSOC))
			$words[] = $row['word'];
		$num = count($words) < $num ? count($words) : $num;
		shuffle($words);
		print($words[0]);
		for($i = 1; $i < $num; $i++)
			print(",".$words[$i]);
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
      background: green;
      color: white;
      }
      .error {
      padding: 5px;
      background: red;
      color: white;
      }
      button {
      width: 100%;
      padding: 10px;
      margin-top: 20px;
      border-radius: 20px;
      border: none;
      border-bottom: 4px solid #3e4f24;
      background: #5a7233; 
      font-size: 16px;
      font-weight: 400;
      color: #fff;
      }
      button:hover {
      background: #3e4f24;
      } 
      @media (min-width: 568px) {
      form {
      width: 60%;
      }
      }
    </style>
  </head>
  <body>
    <form method="post" action="?" class="decor">
      <div class="form-left-decoration"></div>
      <div class="form-right-decoration"></div>
      <div class="circle"></div>
      <div class="form-inner">
	<a href="?generate=1&num=500">Изтегляне на 500 думи</a>
        <h1>Моля въведете грамотно написани думи на български език, разделени със запетайки.</h1>
	<?php
		$q = mysqli_query($db_conn, "select * from words where 1");
		$total_num = mysqli_num_rows($q);
	?>
	<div>Total number of words <?=$total_num;?>
	<div class="success">
		<?php
			foreach($success as $v) {
				print("<p>$v</p>");
			}
		?>
	</div>
	<div class="error">
		<?php
			foreach($error as $v) {
				print("<p>$v</p>");
			}
		?>
	</div>
        <!--input name="user" type="text" placeholder="Your Name"-->
        <textarea placeholder="Message..." rows="5" name="gorgon"></textarea>
        <button type="submit" href="/">Enter</button>
      </div>
    </form>
  </body>
</html>
