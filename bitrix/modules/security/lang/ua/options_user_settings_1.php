<?
$MESS["SEC_OTP_ACCESS_DENIED"] = "Управління параметрами одноразових паролів недоступне.";
$MESS["SEC_OTP_SWITCH_ON"] = "Увімкнути складений пароль";
$MESS["SEC_OTP_SECRET_KEY"] = "Секретний ключ (надається індивідуально для кожного пристрою)";
$MESS["SEC_OTP_INIT"] = "Ініціалізація";
$MESS["SEC_OTP_PASS1"] = "Перше значення пароля на пристрої (натиснути і записати з екрану дисплея)";
$MESS["SEC_OTP_PASS2"] = "Наступне значення пароля на пристрої (натиснути ще раз і записати з екрану дисплея)";
$MESS["SEC_OTP_NOTE"] = "<h3 style=\"clear:both\"><br>Одноразовий пароль</h3>
<img src=\"/bitrix/images/security/etoken_pass.png\" align=\"left\">
Система <a href=\"http://ru.wikipedia.org/wiki/%D0%9E%D0%B4%D0%BD%D0%BE%D1%80%D0%B0%D0%B7%D0%BE%D0%B2%D1%8B%D0%B9_%D0%BF%D0%B0%D1%80%D0%BE%D0%BB%D1%8C\">одноразових паролей</a> доповнює стандартну систему авторизації та дозволяє значно підсилити систему безпеки інтернет-проекту. Для увімкнення системи необхідно використовувати апаратний пристрій (наприклад, <a href=\"http://www.aladdin-rd.ru/catalog/etoken/pass/\">Aladdin eToken PASS</a>) або використовувати відповідне програмне забезпечення, що реалізує <a href=\"http://www.1c-bitrix.ru/products/cms/security/otp.php\">OTP</a>. Система одноразових паролів рекомендується до використання адміністраторам сайтів для підвищення рівня безпеки.
<h3 style=\"clear:both\"><br>Як користуватися </h3>
<img src=\"/bitrix/images/security/ru_pass_form.jpg\" align=\"left\">
При включенні системи одноразового пароля, цей користувач зможе авторизуватися тільки з використанням свого імені (login) і складеного пароля, що складається з пароля та одноразового пароля пристрою (6 цифр). Одноразовий пароль (див. <font style=\"color:red\">2</font> на малюнку) вводиться у полі «Пароль» стандартної форми авторизації на сайті одразу після звичайного паролю (див. <font style=\"color:red\">1</font> на малюнку) без пробілів.<br>
Расширенная аутентификация с одноразовым паролем начнет работать после ввода секретного ключа и двух <b>последовательно сгенерированных одноразовых паролей</b>, полученных с устройства.
<h3 style=\"clear:both\"><br>Инициализация</h3>
При ініціалізації або повторної синхронізації пристрою з користувачем необхідно заповнити два <b>послідовно сгенерованих одноразових паролі</b>, отриманих з пристрою.
<h3 style=\"clear:both\"><br>Опис</h3>
Система авторизації з використанням одноразових паролів (One-Time Password — OTP) розроблена в рамках ініціативи <a href=\"http://www.openauthentication.org/\">OATH</a>.<br>
Реалізація заснована на алгоритмі HMAC та хеш-функції SHA-1. Для розрахунку значення OTP приймаються два вхідних параметра — секретний ключ (початкове значення для генератора) і поточне значення лічильника (кількість необхідних циклів генерації). Початкове значення зберігається як в самому пристрої, так і на сайті після ініціалізації пристрою. Лічильник у пристрої збільшується при кожній генерації OTP, на сервері — при кожній вдалій аутентифікації по OTP.<br>
Партія пристроїв OTP постачається із зашифрованим файлом, що містить початкові значення (секретні ключі) для всіх пристроїв партії, пов'язаного з серійним номером пристрою (друкується на корпусі пристрою).<br>
У разі порушення синхронізації лічильника генерації в телефоні або на сервері, її можна легко відновити — привести значення на сервері у відповідність значенням, що зберігається в пристрої. Для цього адміністратор системи або сам користувач (за наявності відповідних дозволів) повинен згенерувати два послідовних значення одноразових паролів (OTP) і ввести їх у форму на сайті.";
?>