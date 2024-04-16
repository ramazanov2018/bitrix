<?php

namespace RNS\Integrations\Models;

class PropertyMap
{
    /** @var int|null  */
    private $defaultPriority = 1;
    /** @var PropertyMapItem[] */
    private $items = [];

    /**
     * @return int|null
     */
    public function getDefaultPriority(): ?int
    {
        return $this->defaultPriority;
    }

    /**
     * @param int|null $defaultPriority
     * @return PropertyMap
     */
    public function setDefaultPriority(?int $defaultPriority): PropertyMap
    {
        $this->defaultPriority = $defaultPriority;
        return $this;
    }

    /**
     * @return PropertyMapItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param $projectKey
     * @return PropertyMapItem[]
     */
    public function getItemsByProject($projectKey): array
    {
        return array_filter($this->items, function($item) use ($projectKey) {
            /** @var PropertyMapItem $item */
           return $item->getExternalProjectId() == $projectKey;
        });
    }

    /**
     * @param PropertyMapItem[] $items
     * @return PropertyMap
     */
    public function setItems(array $items): PropertyMap
    {
        $this->items = $items;
        return $this;
    }

    public function getItemByExternalPropertyId($typeId, $propertyId, $internalTypeId = null, $projectId = null)
    {
        foreach ($this->items as $item) {
            if ($item->getExternalTypeId() == $typeId && $item->getExternalPropertyId() == $propertyId &&
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
            /** @var PropertyMapItem $item */
            return $item->getExternalTypeId() == $typeId;
        });
    }

    public function addOrUpdateItem($typeId, $propertyId, $internalTypeId = '', $projectId = '')
    {
        $item = $this->getItemByExternalPropertyId($typeId, $propertyId);
        if (!$item) {
            $item = new PropertyMapItem();
            if ($projectId) {
                $item->setExternalProjectId($projectId);
            }
            $item->setExternalTypeId($typeId);
            $item->setExternalPropertyId($propertyId);
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

    public function addItem(PropertyMapItem $item)
    {
        $this->items[] = $item;
    }

    public function setExternalProjectId($projectId)
    {
        foreach ($this->items as $item) {
            $item->setExternalProjectId($projectId);
        }
    }

    public function indexOf(PropertyMapItem $item)
    {
        return array_search($item, $this->items);
    }
}
