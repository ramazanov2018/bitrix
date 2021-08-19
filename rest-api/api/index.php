<?
use Api\Classes\CompanyApiClass,
    Api\Classes\PaymentsResidentsApiClass,
    Api\Classes\ShipmentApiClass,
    Api\Classes\ProductsApiClass,
    Api\Classes\ContactApiClass,
    Api\Classes\DealApiClass,
    Api\Classes\CheckExchange,
    Api\Classes\TermPayApiClass,
    Api\Classes\SellerPayApiClass,
    Api\Classes\PackageApiClass,
    Api\Classes\BasisApiClass;
use Serv\CheckEx\ContactCheckExTable;


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
header('Content-Type', 'text/json');

/*Библиотека Slim */
require_once 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->response->headers->set('Content-Type', 'application/json');

/*Переданные данные*/
$json = file_get_contents('php://input');
$data = json_decode($json, true);

//проверка токена
if ($data['TOKEN'] == API_TOKEN):

    /*"Отгрузки" */
    $app->post('/shipment/', function () {

        require_once 'classes/ShipmentApiClass.php';

        global $data;
        $Shipment = new ShipmentApiClass();
        $ID = $Shipment->AddElement($data['DATA'], $data['DIRECTION']);

    });

    /*"Платежи нерезидентов" */
    $app->post('/resident/', function () {

        require_once 'classes/PaymentsResidentsApiClass.php';

        global $data;
        $ResidentsApiClass = new PaymentsResidentsApiClass();
        $ID = $ResidentsApiClass->AddElement($data['DATA'], $data['DIRECTION']);

    });

    /*"Продукт" */
    $app->post('/product/', function () {

        require_once 'classes/ProductsApiClass.php';

        global $data;
        $ProductsApiClass = new ProductsApiClass();
        $ID = $ProductsApiClass->AddElement($data['DATA'], $data['DIRECTION']);

    });

    /*"Компании - прием" */
    $app->post('/company/', function () {

        require_once 'classes/CompanyApiClass.php';

        global $data;
        $CompanyApiClass = new CompanyApiClass();
        $CompanyApiClass->Controller($data['DATA'], $data['DIRECTION']);

    });

    /*"Контакт - прием" */
    $app->post('/contact/', function () {

        require_once 'classes/ContactApiClass.php';

        global $data;
        $ContactApiClass = new ContactApiClass();
        $ContactApiClass->Controller($data['DATA'], $data['DIRECTION']);

    });

    /*"Сделка - прием" */
    $app->post('/deal/', function () {

        require_once 'classes/DealApiClass.php';

        global $data;
        $DealApiClass = new DealApiClass();
        $DealApiClass->Controller($data['DATA'], $data['DIRECTION']);
    });

    /*"Базис отгрузки - прием" */
    $app->post('/basis/', function () {

        require_once 'classes/BasisApiClass.php';

        global $data;
        $DealApiClass = new BasisApiClass();
        $DealApiClass->Controller($data['DATA'], $data['DIRECTION']);
    });

    /*"Условие оплаты - прием" */
    $app->post('/term-pay/', function () {

        require_once 'classes/TermPayApiClass.php';

        global $data;
        $DealApiClass = new TermPayApiClass();
        $DealApiClass->Controller($data['DATA'], $data['DIRECTION']);
    });

    /*"Продавец- прием" */
    $app->post('/seller/', function () {

        require_once 'classes/SellerPayApiClass.php';

        global $data;
        $DealApiClass = new SellerPayApiClass();
        $DealApiClass->Controller($data['DATA'], $data['DIRECTION']);
    });

    /*"Продавец - прием" */
    $app->post('/package/', function () {

        require_once 'classes/PackageApiClass.php';

        global $data;
        $DealApiClass = new PackageApiClass();
        $DealApiClass->Controller($data['DATA'], $data['DIRECTION']);
    });

    /*"Сделка - отдача" */
    $app->post('/deal-get/', function () {

        require_once 'classes/DealApiClass.php';

        global $data;
        $DealApiClass = new DealApiClass();
        $DealApiClass->PushDeals($data);
    });

    /*"Компании - отдача" */
    $app->post('/company-get/', function () {

        require_once 'classes/CompanyApiClass.php';

        global $data;
        $DealApiClass = new CompanyApiClass();
        $DealApiClass->PushDeals($data);
    });


    /*"Контакты - отдача" */
    $app->post('/contact-get/', function () {

        require_once 'classes/ContactApiClass.php';

        global $data;
        $DealApiClass = new ContactApiClass();
        $DealApiClass->PushDeals($data);
    });


    /*"Проверка дубли" */
    $app->post('/exchange-check/', function () {
        require_once 'classes/CheckExchange.php';

        global $data;
        $ExchangeCheck = new CheckExchange();
        $ExchangeCheck->CheckController($data);
    });

    $app->run();

else:

    header("HTTP/1.0 404 Not Found");
    echo 'Ошибка авторизации';

endif;