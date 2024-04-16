<?php

namespace RNS\Integrations\Models;

class EntityTypeMap
{
    /** @var mixed */
    private $defaultTypeId;
    /** @var EntityTypeMapItem[] */
    private $items = [];

    /**
     * @return mixed
     */
    public function getDefaultTypeId()
    {
        return $this->defaultTypeId;
    }

    /**
     * @param mixed $defaultTypeId
     * @return EntityTypeMap
     */
    public function setDefaultTypeId($defaultTypeId): EntityTypeMap
    {
        $this->defaultTypeId = $defaultTypeId;
        return $this;
    }

    /**
     * @return EntityTypeMapItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param EntityTypeMapItem[] $items
     * @return EntityTypeMap
     */
    public function setItems(array $items): EntityTypeMap
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @param mixed $id
     * @param mixed $projectId
     * @return EntityTypeMapItem|null
     */
    public function getItemByExternalTypeId($id, $projectId = null)
    {
        foreach ($this->items as $item) {
            if ($item->getExternalTypeId() == $id && (!$projectId || $projectId == $item->getExternalProjectId())) {
                return $item;
            }
        }
        return null;
    }

    public function addNewItem($typeId, $projectId = '')
    {
        $item = new EntityTypeMapItem();
        $item->setExternalProjectId($projectId);
        $item->setExternalTypeId($typeId);
        $this->items[] = $item;
    }

    public function addItem(EntityTypeMapItem $item)
    {
        $this->items[] = $item;
    }

    public function setExternalProjectId($projectId)
    {
        foreach ($this->items as $item) {
            $item->setExternalProjectId($projectId);
        }
    }
}
