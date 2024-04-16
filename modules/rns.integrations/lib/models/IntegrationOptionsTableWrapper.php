<?php

namespace RNS\Integrations\Models;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;
use CAgent;
use JsonMapper;
use RNS\Integrations\ExchangeTypeTable;
use RNS\Integrations\IntegrationOptionsTable;

class IntegrationOptionsTableWrapper
{
    /** @var IntegrationOptionsTable */
    private $obj;
    /** @var OptionsBase */
    private $options;
    /** @var Mapping */
    private $mapping;

    const AGENT_MIN_INTERVAL = 600;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->obj->getId();
    }

    /**
     * @return int|null
     */
    public function getSystemId(): ?int
    {
        return $this->obj->getSystemId();
    }

    /**
     * @return string|null
     */
    public function getSystemCode(): ?string
    {
        return $this->obj->getSystem()->getCode();
    }

    /**
     * @return int|null
     */
    public function getExchangeTypeId(): ?int
    {
        return $this->obj->getExchangeTypeId();
    }

    /**
     * @return string|null
     */
    public function getExchangeTypeCode(): ?string
    {
        return $this->obj->getExchangeType() ? $this->obj->getExchangeType()->getCode() : null;
    }

    /**
     * @return int|null
     */
    public function getDirection(): ?int
    {
        return $this->obj->getDirection();
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->obj->getName();
    }

    /**
     * @return integer|null
     */
    public function getSchedule(): ?int
    {
        return $this->obj->getSchedule();
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->obj->getActive();
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->obj->getDescription();
    }

    /**
     * @return OptionsBase|null
     */
    public function getOptions(): ?OptionsBase
    {
        return $this->options;
    }

    /**
     * @param OptionsBase $options
     * @return IntegrationOptionsTableWrapper
     */
    public function setOptions(OptionsBase $options): IntegrationOptionsTableWrapper
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return Mapping
     */
    public function getMapping(): Mapping
    {
        return $this->mapping;
    }

    public function getLastOperationDate()
    {
        return $this->obj->getLastOperationDate();
    }

    /**
     * @param Mapping $mapping
     * @return IntegrationOptionsTableWrapper
     */
    public function setMapping(Mapping $mapping): IntegrationOptionsTableWrapper
    {
        $this->mapping = $mapping;
        return $this;
    }

    /**
     * @param array $fields
     * @param array $filter
     * @return array
     * @throws ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getList(array $fields = [], $filter = ['=ACTIVE' => 'Y']): array
    {
        $res = IntegrationOptionsTable::getList([
            'select' => empty($fields) ? ['*'] : $fields,
            'filter' => $filter
        ]);
        return $res->fetchAll();
    }

    /**
     * @param int $id
     * @param array $projectIds
     * @return IntegrationOptionsTableWrapper
     * @throws ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public static function getById(?int $id, array $projectIds = [])
    {
        $result = new self;
        if ($id > 0) {
            $result->obj = IntegrationOptionsTable::getByPrimary($id, ['select' => ['*', 'EXCHANGE_TYPE.CODE', 'SYSTEM.CODE']])
              ->fetchObject();
            $mapper = new JsonMapper();
            $mapper->bIgnoreVisibility = true;

            $result->options = $result->createDefaultOptions();
            $options = json_decode($result->obj->getOptions());
            if (!empty($options)) {
                $mapper->map($options, $result->options);
            }

            $result->mapping = new Mapping();
            $mapping = json_decode($result->obj->getMapping());
            if (!empty($mapping)) {
                if (empty($mapping->projectMap->items)) {
                    $mapping->projectMap->items = [];
                }
                if (empty($mapping->entityTypeMap->items)) {
                    $mapping->entityTypeMap->items = [];
                }
                if (empty($mapping->entityStatusMap->items)) {
                    $mapping->entityStatusMap->items = [];
                }
                if (empty($mapping->entityPropertyMap->items)) {
                    $mapping->entityPropertyMap->items = [];
                }
                $mapper->map($mapping, $result->mapping);

                if (!empty($projectIds)) {
                    $items = array_filter(
                      $result->mapping->getProjectMap()->getItems(),
                      function($item) use ($projectIds) {
                          /** @var EntityMapItem $item */
                        return in_array($item->getExternalEntityId(), $projectIds);
                    });
                    $result->mapping->getProjectMap()->setItems($items);
                    foreach ($projectIds as $projectId) {
                        if (empty($result->mapping->getProjectMap()->getItemsByExternalId($projectId))) {
                            $result->mapping->getProjectMap()->addItem($projectId);
                        }
                    }
                }
            }
        } else {
            $result->obj = IntegrationOptionsTable::createObject();
            $result->options = new OptionsBase();
            $result->mapping = new Mapping();
        }

        return $result;
    }

    /**
     * @param array $fields
     * @throws \Exception
     */
    public function save(array $fields)
    {
        global $USER;

        if (!empty($fields['mapping'])) {
            $mappings = ['projectMap', 'entityTypeMap', 'entityStatusMap', 'entityPropertyMap', 'userMap'];
            foreach ($mappings as $mapping) {
                if (empty($fields['mapping'][$mapping]) || empty($fields['mapping'][$mapping]['items'])) {
                    continue;
                }
                $fields['mapping'][$mapping]['items'] = $this->filterDeletedItems($fields['mapping'][$mapping]['items']);
                $fields['mapping'][$mapping]['items'] = $this->DuplicatesDeleteItems($mapping, $fields['mapping'][$mapping]['items']);
            }
        }

        if (!empty($fields['externalSystem'])) {
            $this->obj->setSystemId($fields['externalSystem']);
        }
        if (!empty($fields['exchangeType'])) {
            $this->obj->setExchangeTypeId($fields['exchangeType']);
        }

        if (empty($fields['name'])) {
            throw new \InvalidArgumentException('name');
        }
        if ($this->getExchangeTypeCode() != 'email' && empty($fields['options']['taskLevel'])) {
            throw new \InvalidArgumentException('taskLevel');
        }

        $this->obj->setName($fields['name']);
        $this->obj->setDirection($fields['exchangeDirection']);
        $interval = intval($fields['schedule']);
        if ($interval > 0 && $interval < self::AGENT_MIN_INTERVAL) {
            $interval = self::AGENT_MIN_INTERVAL;
        }

        if ($this->getExchangeTypeCode() == 'files') {
            $defaultOptions = include $_SERVER['DOCUMENT_ROOT'].'/local/modules/rns.integrations/default_options.php';
            $val = intval($fields['options']['files']['fileMaxSize']);
            if ($val > $defaultOptions['files']['fileMaxSize']) {
                throw new \Exception("Введенное значение ({$val}) превышает максимально допустимый размер файла в {$defaultOptions['files']['fileMaxSize']} Мб.");
            }
            $val = intval($fields['options']['files']['taskMaxCount']);
            if ($val > $defaultOptions['files']['taskMaxCount']) {
                throw new \Exception("Введенное значение ({$val}) превышает максимально допустимое число задач в файле для загрузки (не более {$defaultOptions['files']['taskMaxCount']}).");
            }
        }

        $this->obj->setSchedule($interval);
        $this->obj->setActive(!empty($fields['active']) ? 'Y' : 'N');
        if (!$this->obj->getId()) {
            $this->obj->setCreated(DateTime::createFromTimestamp(time()));
            $this->obj->setCreatedBy($USER->GetID());
        }
        $this->obj->setModifiedBy($USER->GetID());
        $this->obj->setModified(DateTime::createFromTimestamp(time()));

        $code = $this->getExchangeTypeCode();
        foreach ($fields['options'][$code] as $key => $value) {
            $this->options->{'set'.ucwords($key)}($value);
        }
        $this->options->setTaskLevel($fields['options']['taskLevel']);

        $this->obj->setOptions(json_encode($this->options, JSON_UNESCAPED_UNICODE));

        if ($this->getExchangeTypeCode() == ExchangeTypeTable::TYPE_DATABASE) {

            $extraMapping = ['entityTypeMap', 'entityStatusMap', 'entityPropertyMap'];

            foreach ($extraMapping as $key) {
                foreach ($fields['mapping'][$key]['items'] as &$item) {
                    if ($key == 'entityTypeMap') {
                        if (empty($item['internalTypeId'])) {
                            $item['internalTypeId'] = $this->tryGetMappingParameter($fields['mapping'], $key, $item, 'internalTypeId');
                        }
                    } elseif ($key == 'entityStatusMap') {
                        if (empty($item['internalTypeId'])) {
                            $item['internalTypeId'] = $this->tryGetMappingParameter($fields['mapping'], $key, $item, 'internalTypeId');
                        }
                        if (empty($item['internalStatusId'])) {
                            $item['internalStatusId'] = $this->tryGetMappingParameter($fields['mapping'], $key, $item, 'internalStatusId');
                        }
                    } elseif ($key == 'entityPropertyMap') {
                        if (empty($item['internalTypeId'])) {
                            $item['internalTypeId'] = $this->tryGetMappingParameter($fields['mapping'], $key, $item, 'internalTypeId');
                        }
                        if (empty($item['internalPropertyId'])) {
                            $item['internalPropertyId'] = $this->tryGetMappingParameter($fields['mapping'], $key, $item, 'internalPropertyId');
                        }
                    }
                }
            }
        }

        $this->obj->setMapping(json_encode($fields['mapping'], JSON_UNESCAPED_UNICODE));

        $this->obj->setDescription($fields['description']);

        $this->obj->save();

        if (!(($this->getExchangeTypeCode() == ExchangeTypeTable::TYPE_FILES) ||
          ($this->getExchangeTypeCode() == ExchangeTypeTable::TYPE_EMAIL
                && $this->getDirection() == IntegrationOptionsTable::DIRECTION_EXPORT))) {
            $this->addOrUpdateAgent();
        }
    }

    /**
     * @return ApiOptions|DatabaseOptions|EmailOptions|FilesOptions
     * @throws ArgumentException
     */
    public function createDefaultOptions()
    {
        switch ($this->getExchangeTypeCode()) {
            case ExchangeTypeTable::TYPE_API:
                return new ApiOptions();
            case ExchangeTypeTable::TYPE_DATABASE:
                return new DatabaseOptions();
            case ExchangeTypeTable::TYPE_EMAIL:
                return new EmailOptions();
            case ExchangeTypeTable::TYPE_FILES:
                return new FilesOptions();
            default:
                throw new ArgumentException('Unsupported exchange type code.', 'exchangeTypeCode');
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public static function delete($id)
    {
        return IntegrationOptionsTable::delete($id)->isSuccess();
    }

    /**
     * @param int $id
     * @param $date
     * @return \Bitrix\Main\ORM\Data\UpdateResult
     * @throws \Exception
     */
    public static function setLastOperationDate(int $id, $date)
    {
        return IntegrationOptionsTable::update($id, ['LAST_OPERATION_DATE' => $date]);
    }

    /**
     * @param int $systemId
     * @param bool $active
     * @throws ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public static function activate(int $systemId, bool $active)
    {
        $filter = ['=SYSTEM_ID' => $systemId, '=ACTIVE' => $active ? 'N' : 'Y'];
        $res = IntegrationOptionsTable::getList([
          'select' => ['ID'],
          'filter' => $filter
        ]);
        while ($row = $res->fetch()) {
            $activeStr = $active ? 'Y' : 'N';
            IntegrationOptionsTable::update($row['ID'], ['ACTIVE' => $activeStr]);
            $name = "\\RNS\\Integrations\\Processors\\IntegrationAgent::run({$row['ID']});";
            $res = $list = CAgent::GetList([], ['NAME' => $name]);
            if ($agent = $res->Fetch()) {
                CAgent::Update($agent['ID'], ['ACTIVE' => $activeStr]);
            }
        }
    }

    private function filterDeletedItems(array $arr)
    {
        return array_values(array_filter($arr, function($item) {
              return !isset($item['deleted']);
          },
          ARRAY_FILTER_USE_BOTH
        ));
    }
    private function DuplicatesDeleteItems($MappingType, array $arr)
    {
        if($MappingType == "projectMap"){
            $arr = $this->unique_multidim_array($arr, 'externalEntityId');
        }elseif ($MappingType == "entityTypeMap"){
            $arr = $this->unique_multidim_array($arr, 'externalTypeId');
        }elseif ($MappingType == "userMap"){
            $arr = $this->unique_multidim_array($arr, 'externalId');
        }
       return $arr;
    }

    private function unique_multidim_array($array, $key) {
        $temp_array = [];
        $i = 0;
        $key_array = [];

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    private function addOrUpdateAgent()
    {
        $active = $this->obj->getActive() && $this->getSchedule() > 0 ? 'Y' : 'N';
        $name = "\\RNS\\Integrations\\Processors\\IntegrationAgent::run({$this->getId()});";
        $res = $list = CAgent::GetList([], ['NAME' => $name]);
        if ($row = $res->Fetch()) {
            CAgent::Update($row['ID'], [
              'AGENT_INTERVAL' => $this->getSchedule(),
              'ACTIVE' => $active
            ]);
        } else {
            CAgent::AddAgent($name, 'rns.integrations', 'N', $this->getSchedule(), '', $active);
        }
    }

    private function tryGetMappingParameter(array $mapping, string $key, array $item, string $parameterName)
    {
        foreach ($mapping[$key]['items'] as $mapItem) {
            if ($key == 'entityTypeMap') {
                if ($mapItem['externalTypeId'] == $item['externalTypeId']) {
                    return $mapItem[$parameterName] ?? '';
                }
            } else if ($key == 'entityStatusMap') {
                if ($mapItem['externalStatusId'] == $item['externalStatusId']) {
                    return $mapItem[$parameterName] ?? '';
                }
            } elseif ($key == 'entityPropertyMap') {
                if ($mapItem['externalPropertyId'] == $item['externalPropertyId']) {
                    return $mapItem[$parameterName] ?? '';
                }
            }
        }
        return $item[$parameterName] ?? '';
    }
}
