<?php

use Bitrix\Main\Context;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Highloadblock as HL;
use Rns\Bitrix24Examples\Helpers\UserBirthdaysEntity;

class BirthDaysList extends CBitrixComponent
{
    protected Query $query;
    protected Options $filterOptions;
    protected GridOptions $gridOptions;
    protected string $gridId = 'SONET_GROUP_LIST';
    protected string $filterId = 'SONET_GROUP_LIST';
    protected array $filter = [];
    protected array $gridHeader = [];
    protected bool $iframe = false;

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function init()
    {
        $this->filter = $this->filterFields();
        $this->gridHeader = $this->gridHeader();
        $this->filterOptions = new Options($this->filterId);
        $this->gridOptions = new GridOptions($this->gridId);

        $hlbBirthdays = HL\HighloadBlockTable::getList(['filter' => ['NAME' => UserBirthdaysEntity::HLB_NAME]])->fetch();
        $this->query = new Query(Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlbBirthdays));
    }

    private function prepareData():array
    {
        $context = Context::getCurrent();
        $this->arParams['IFRAME'] = $context->getRequest()->get('IFRAME') == 'Y' ? 'Y' : 'N';

        #Инициализируем свойства класса
        $this->init();
        $result['SORT'] = $this->gridOptions->getSorting($this->getDefaultGridSorting())['sort'];
        $result['GRID_ID'] = $this->gridId;
        $result['FILTER_ID'] = $this->filterId;

        #Постраничная навигация
        $nav_params = $this->gridOptions->GetNavParams();
        $nav = new PageNavigation('page');
        $nav->allowAllRecords(true)
            ->setPageSize($nav_params['nPageSize'])
            ->initFromUri();

        #Строим запрос к базе
        $this->addQueryOrder();
        $this->addQuerySelect();
        $this->addQueryFilter();
        $this->query->countTotal(true);
        $this->query->setOffset($nav->getOffset());
        $this->query->setLimit($nav->getLimit());

        #Получаем данные и преобразуем на нужный формат
        $res = $this->query->exec();
        $rowsList = $this->prepareRowList($res->fetchAll());
        $result['ROWS'] = $rowsList;
        $result['ROWS_COUNT'] = $res->getCount();
        $nav->setRecordCount($result['ROWS_COUNT']);

        $result['NAV'] = $nav;
        $result['FILTER'] = $this->filter;
        $result['COLUMNS'] = $this->gridHeader;

        return $result;
    }

    protected function prepareRowList(array $records): array
    {
        $rowList = [];
        foreach ($records as $row) {
            $rowList[] = [
                'data' => [
                    "UF_USER" => $row['UF_USER'],
                    "UF_DATE_BIRTHDAY" => $row['UF_DATE_BIRTHDAY'],
                    "UF_ADDRESS_CITY" => $row['UF_ADDRESS_CITY'],
                ],
                'actions' => [
                    [
                        'text'    => 'Просмотр',
                        'default' => true,
                        'onclick' => 'openSidePanel("/birthdays/detail/'.$row['UF_XML_ID'].'/")'
                    ],
                    [
                        'text'    => 'Удалить',
                        'default' => true,
                        'onclick' => 'if(confirm("Точно?")){document.location.href="?op=delete&id='.$row['ID'].'"}'
                    ]
                ]
            ];
        }
        return $rowList;
    }

    protected function addQuerySelect():void
    {
        foreach ($this->getSelect() as $filed)
            $this->query->addSelect($filed);
    }

    protected function addQueryFilter():void
    {
        $filterData = $this->getFilter([]);

        foreach ($filterData as $k => $v) {
            $this->query->addFilter($k, $v);
        }


        /* foreach ($this->filterOptions as $k => $v) {
            // Тут разбор массива $filterData из формата, в котором его формирует main.ui.filter в формат, который подойдет для вашей выборки.
            // Обратите внимание на поле "FIND", скорее всего его вы и захотите засунуть в фильтр по NAME и еще паре полей
            $filter['NAME'] = "%".$filterData['FIND']."%";
        }*/
        //$this->query->setSelect($this->getFilter());
    }

    /**
     * Сортировка выборки
     *
     * @return void
     */
    private function addQueryOrder(): void
    {
        $orderFields = $this->getOrder();
        foreach ($orderFields as $fieldName => $value)
        {
            $this->query->addOrder($fieldName, $value);
        }
    }

    /**
     * Получаем настройки сортировки у грида
     *
     * @return array
     */
    private function getOrder(): array
    {
        $result = [];

        $gridSort = $this->gridOptions->getSorting();

        if (!empty($gridSort['sort']))
        {
            foreach ($gridSort['sort'] as $by => $order)
            {
                $result[$by] = mb_strtoupper($order);
            }
        }

        return $result;
    }


    /**
     * Поля для выборки
     *
     * @return array
     */
    protected function getSelect():array
    {
        return ['UF_XML_ID', 'UF_DATE_BIRTHDAY', 'UF_USER', 'UF_ADDRESS_CITY'];
    }

    /**
     * @return array
     */
    protected function getFilter():array
    {
        $filter = [];
        $filterData = $this->filterOptions->getFilter([]);
        foreach ($filterData as $k => $v){
            if ($k == 'UF_DATE_BIRTHDAY_from' && !empty($v))
            {
                $filter['>=UF_DATE_BIRTHDAY'] = $v;
            }

            if ($k == 'UF_DATE_BIRTHDAY_to' && !empty($v))
            {
                $filter['<=UF_DATE_BIRTHDAY'] = $v;
            }

            if ($k == 'UF_USER' && !empty($v))
            {
                $filter['=UF_USER'] = (int)preg_replace('/^U(\d+)$/', '$1', ($v ?? ''));
            }
        }
        return $filter;
    }

    private function getDefaultGridSorting(): array
    {
        return [
            'sort' => [ 'ID' => 'ASC'],
            'vars' => [
                'by' => 'by',
                'order' => 'order',
            ],
        ];
    }


    /**
     * Поля фильтра
     *
     * @return array
     */
    private function filterFields():array
    {
        return [
            ['id' => 'UF_USER', 'name' => 'Сотрудник', 'type'=>'dest_selector', 'default' => true, 'params'=>['contextCode' => 'U']],
            ['id' => 'UF_DATE_BIRTHDAY', 'name' => 'День рождения', 'type'=>'date', 'default' => true],
        ];
    }

    /**
     * Поля grid
     *
     * @return array
     */
    private function gridHeader():array
    {
        $columns = [];
        $columns[] = ['id' => 'UF_USER', 'name' => 'Пользователь', 'sort' => 'UF_USER', 'default' => true];
        $columns[] = ['id' => 'UF_DATE_BIRTHDAY', 'name' => 'День рождения', 'sort' => 'UF_DATE_BIRTHDAY', 'default' => true];
        $columns[] = ['id' => 'UF_ADDRESS_CITY', 'name' => 'Город', 'sort' => 'UF_ADDRESS_CITY', 'default' => true];

        return $columns;
    }

    public function executeComponent()
    {
        #Готовим данные
        $this->arResult = $this->prepareData();

        #Подключаем шаблон
        if ($this->arParams['IFRAME'] == 'Y') {
            /** @var CMain $APPLICATION */
            global $APPLICATION;
            $APPLICATION->RestartBuffer();
            $this->includeComponentTemplate();

            require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
            exit;
        }else{
            $this->includeComponentTemplate();
        }
    }
}