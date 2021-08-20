<?php
namespace Test\Catalog;

class CatalogHelper
{
    public static function TestAgent()
    {
        mail("test@gmail.com", "Загаловок", "Текст письма \n 1-ая строчка \n 2-ая строчка \n 3-ая строчка");
        return __CLASS__."::TestAgent();";
    }
}