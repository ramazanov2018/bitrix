<?php

namespace RNS\Integrations\Models;

class EntityStatusMap
{
    /** @var mixed */
    private $defaultStatusId;
    /** @var EntityStatusMapItem[] */
    private $items = [];

    /**
     * @return mixed
     */
    public function getDefaultStatusId()
    {
        return $this->defaultStatusId;
    }

    /**
     * @param mixed $defaultStatusId
     * @return EntityStatusMap
     */
    public function setDefaultStatusId($defaultStatusId): EntityStatusMap
    {
        $this->defaultStatusId = $defaultStatusId;
        return $this;
    }

    /**
     * @return EntityStatusMapItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param EntityStatusMapItem[] $items
     * @return EntityStatusMap
     */
    public function setItems(array $items): EntityStatusMap
    {
        $this->items = $items;
        return $this;
    }

    public function getItemByExternalStatusId($typeId, $statusId, $internalTypeId = null, $projectId = null)
    {
        foreach ($this->items as $item) {
            if ($item->getExternalTypeId() == $typeId && $item->getExternalStatusId() == $statusId &&
              (!$internalTypeId || $item->getInternalTypeId() == $internalTypeId) &&
              (!$projectId || $item->getExternalProjectId() == $projectId)) {
                return $item;
            }
        }
        return null;
    }

    public function getItemsByExternalTypeId($typeId)
    {
        return array_filter($this->items, function($item) use ($typeId) {
            /** @var EntityStatusMapItem $item */
           return $item->getExternalTypeId() == $typeId;
        });
    }

    public function addOrUpdateItem($externalTypeId, $statusId, $internalTypeId = '', $projectId = '')
    {
        $item = $this->getItemByExternalStatusId($externalTypeId, $statusId);
        if (!$item) {
            $item = new EntityStatusMapItem();
            if ($projectId) {
                $item->setExternalProjectId($projectId);
            }
            $item->setExternalTypeId($externalTypeId);
            $item->setExternalStatusId($statusId);
            if ($internalTypeId) {
                $item->setInternalTypeId($internalTypeId);
            }
            $this->items[] = $item;
        } else {
            if ($internalTypeId) {
                $item->setInternalTypeId($internalTypeId);
            }
        }
    }

    public function addItem(EntityStatusMapItem $item)
    {
        $this->items[] = $item;
    }

    public function setExternalProjectId($projectId)
    {
        foreach ($this->items as $item) {
            $item->setExternalProjectId($projectId);
        }
    }

    public function indexOf(EntityStatusMapItem $item)
    {
        return array_search($item, $this->items);
    }
}
