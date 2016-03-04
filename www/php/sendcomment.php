<?php
// этот скрипт отображает форму с полями для ввода
// потом все это хозяйство проверяется как-то
// и отправляется на мыло, указанное в переменной to
// при возникновении проблем смотреть переменную sendmail в php.ini
// и грохнуть что-нить типа -f mail@site.com
// для вставки в шаблон нужно выделить весь скрипт и вставить (sic!) куда надо
// ВНИМАНИЕ! возможно придется закомментировать строку вывода хедеров header()
// phpinfo();

// Спасибо, Ваш заказ принят!
// Сотрудники уже занимаются его обработкой.
// Нажмите кнопку "Поделиться" и расскажите своим друзьям про интересное событие.
// Покупайте еще билеты на нашем сайте:
// 27biletov.ru

$title = "Сообщение из мобильного приложения " . $_SERVER["SERVER_NAME"];
// <input name="name" id="name" type="text" placeholder=""/>
// <input name="tel" id="tel" type="tel" placeholder="+7 999 123 45 67"/>
// <input name="inputurl" id="inputurl" type="hidden" placeholder=""/>
// <input name="inputtitle" id="inputtitle" type="hidden" placeholder=""/>

// http://webew.ru/articles/297.webew iconv() действительно очень плохо себя ведет, когда наталкивается на символ, которого нет в кодировке-приемнике 
ini_set('mbstring.substitute_character', "none");

// fake address
$email = 'robot@' . $_SERVER["SERVER_NAME"];

$name =  $_POST['name'];
$contact =  $_POST['contact'];
$newstext =  $_POST['newstext'];
$imageurl =  $_POST['imageurl'];

// $god = $_POST['god'];
// $nowgod = date(Y);

$message = 'Имя отправителя: '.$name.'
Контакт: '.$contact.'

Текст сообщения:
'.$newstext;

if($imageurl) {
$message .= '

URL изображения:
http://27podarkov.ru/mg/uploads/img/' . $imageurl;
}


print_r ($message);
echo "<hr />";

// $message = iconv("UTF-8","KOI8-R",$message);
$message = mb_convert_encoding($message, 'KOI8-R', 'UTF-8');
// $title = iconv("UTF-8","KOI8-R",$title);
$title = mb_convert_encoding($title, 'KOI8-R', 'UTF-8');
print_r ($message);
echo "<hr />";

$to1 = '4blacktop@gmail.com';
// $to2 = '4blogga@gmail.com';
$to2 = 'director@eson.ru';
$subject = '=?KOI8-R?B?' . base64_encode($title) . "?=";
$message = rtrim(chunk_split(base64_encode($message),512,"\r\n"));

$headers = 'From: ' . $email . "\r\n" .
'MIME-Version: 1.0' . "\r\n" .
'Content-Transfer-Encoding: base64' . "\r\n" .
'Content-Type: text/plain; charset=KOI8-R;' . "\r\n";


echo '<pre>';
print_r ($name);
print_r ($tel);
print_r ($inputtitle);
print_r ($inputurl);
echo "<hr />";

print_r ($headers);
print_r ($subject);
print_r ($message);
echo '</pre>';


if (mail($to1, $subject, $message, $headers)) {	
	// дублируем
	mail($to2, $subject, $message, $headers);
	// header( 'Location: http://27biletov.ru', true, 301 );
	}
else {	echo 'ВНИМАНИЕ! Ваша заявка НЕ отправлена. Пожалуйста, позвоните для уточнения. Спасибо.<br /><a href="" onClick="history.back()">Назад</a>';
}







































/* 
function output_err($num)
{
    $err[0] = 'ОШИБКА! Не введено имя.';
    $err[1] = 'ОШИБКА! Неверно введен e-mail.';
    $err[2] = 'ОШИБКА! Не введено количество.';
    $err[3] = 'ОШИБКА! Посмотрите, какой сейчас год.';
    echo '<p>'.$err[$num].'</p>';
    show_form();
    // exit(); // так было в исходнике
}

if (!empty($_POST['submit'])) complete_mail();
else show_form();
 */
 
// echo "<br />реферер: $referer";
// echo "<br />тайтл: $title";
/* 
// если не заполнено поле "Имя" - показываем ошибку 0
if (empty($name)) output_err(0);
// если неправильно заполнено поле email - показываем ошибку 1
elseif(!preg_match("/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i", $email)) output_err(1);
// если не заполнено поле "Сообщение" - показываем ошибку 2
elseif(empty($mess)) output_err(2);
// если неверно указан год - показываем ошибку 3
elseif($god != $nowgod) output_err(3);

else {
 */


// header("Content-Type: text/html; charset=utf-8");
/* 
function show_form()
{

// <br /><script language=\"JavaScript\">document.write(document.title);</script>
?>
<br clear=all><form action="" method=post>
	<div align="left">
		Имя:*<br /><input onFocus="this.select()" type="text" name="name" size="40" value="">
		<br />Контактный тел.:<br /><input onFocus="this.select()" type="text" name="tel" size="40" value="">
		<br />E-mail:* <br /><input onFocus="this.select()" type="text" name="email" size="40" value="">
		<br />Количество взрослых:* <br /><input onFocus="this.select()" type="text" name="mess" size="40" value="2">
		<br />Защита от ботов. Какой сейчас год? (4 цифры):* <br /><input onFocus="this.select()" type="text" name="god" size="40" value="">
		<br /><input type="submit" value="Отправить" name="submit">
	</div>
</form>
* Заполнение обязательно<br /><br />
<?php
} */

// эта универсальная штука но не работает на сервере
// $title =  substr(htmlspecialchars(trim($_POST['title'])), 0, 1000);
// это для зебры
// $title =  substr(htmlspecialchars(z_title()), 0, 1000);
// $title =  substr(htmlspecialchars(z_title()), 0, 1000);
// $referer = $_SERVER['HTTP_REFERER'];

// $mess =  substr(htmlspecialchars(trim($_POST['mess'])), 0, 100000);
// $name =  substr(htmlspecialchars(trim($_POST['name'])), 0, 30);
// $tel =  substr(htmlspecialchars(trim($_POST['tel'])), 0, 30);
// $email =  substr(htmlspecialchars(trim($_POST['email'])), 0, 50);
// $god = substr(htmlspecialchars(trim($_POST['god'])), 0, 30);
 
 
// $headers = 'From: ' . $email . "\r\n" .
// 'Reply-To: kompuroki@sr.kompuroki.ru\r\n' .
// 'X-Mailer: PHP/' . phpversion() .
// 'Reply-To: ' . $email .
// 'From: ' . $email . "\r\n" .
// 'Reply-To: ' .$email. "\r\n" .
// 'Return-Path: ' . $email . "\r\n" .

/* 
$to = '4blacktop@gmail.com';
$subject = '=?UTF-8?B?' . base64_encode($title) . "?=";
$message = rtrim(chunk_split(base64_encode($message),512,"\r\n"));
$headers = 'From: ' . $email . "\r\n" .
'Reply-To: ' .$email. "\r\n" .
'Return-Path: ' . $email . "\r\n" .
'X-Priority: 3' . "\r\n" .
'X-Mailer: PHP/' . phpversion() .
'MIME-Version: 1.0' . "\r\n" .
'Content-Transfer-Encoding: base64' . "\r\n" .
'Content-Type: text/plain; charset=utf-8;' . "\r\n";
*/


 
?> 