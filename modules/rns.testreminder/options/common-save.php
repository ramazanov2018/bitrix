<?
use Bitrix\Main\Config\Option;
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

Option::set($module_id, "TEST_REMAINDER_USE", $request->getPost("TEST_REMAINDER_USE"));
Option::set($module_id, "TEST_REMAINDER_PERIOD_FROM", $request->getPost("TEST_REMAINDER_PERIOD_FROM"));
Option::set($module_id, "TEST_REMAINDER_PERIOD_TO", $request->getPost("TEST_REMAINDER_PERIOD_TO"));
Option::set($module_id, "TEST_REMAINDER_GROUPS", serialize((array)$request->getPost("TEST_REMAINDER_GROUPS")));
Option::set($module_id, "TEST_REMAINDER_TEST_URL", $request->getPost("TEST_REMAINDER_TEST_URL"));
Option::set($module_id, "TEST_REMAINDER_NOTIFY", $request->getPost("TEST_REMAINDER_NOTIFY"));

/*Option::set($module_id, "WEBWAY_WEBCHAT_USE_SITES", serialize((array)$request->getPost("WEBWAY_WEBCHAT_USE_SITES")));

Option::set($module_id, "WEBWAY_WEBCHAT_ISNEW_USER", intval($request->getPost("WEBWAY_WEBCHAT_ISNEW_USER")) * 60);

if(\WebWay\WebChat\Module::checkModuleMetricsPro()){
	Option::set($module_id, "WEBWAY_WEBCHAT_MANAGER_MESSAGES_DAY", intval($request->getPost("WEBWAY_WEBCHAT_MANAGER_MESSAGES_DAY")));
}*/
?>