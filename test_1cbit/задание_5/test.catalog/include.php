<?php
\Bitrix\Main\Loader::registerAutoloadClasses("test.catalog", array(
        "Test\\Catalog\\TestCategory" => "classes/general/TestCategory.php",
        "Test\\Catalog\\TestProduct" => "classes/general/TestProduct.php",
        "Test\\Catalog\\CatalogHelper" => "classes/general/CatalogHelper.php",
    )
);
?>