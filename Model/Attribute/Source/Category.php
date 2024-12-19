<?php
namespace JerVoo\Breadcrumbs\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Category extends AbstractSource
{
    protected $categoryCollectionFactory;

    public function __construct(CategoryCollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $collection = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect(['name', 'level'])
                ->addFieldToFilter('level', 1)
                ->setOrder('position', 'asc');

            $options = [];
            $options[] = [
                'label' => ' -- Selecteer -- ',
                'value' => 0
            ];

            foreach($collection as $category) {
                $childrenValues = $this->getChildren($category);

                $options[] = [
                    'label'     => $category->getName(),
                    'value'     => $childrenValues
                ];

            }

            $this->_options = $options;
        }

        return $this->_options;
    }

    private function getChildren(\Magento\Catalog\Model\Category $category) {
        $children = $category->getChildrenCategories();
        $childrenValues = [];
        foreach($children as $child) {
            $childrenValues[] = $this->addOption($child);
            if ($child->hasChildren()) {
                $childrenValues = array_merge($childrenValues, $this->getChildren($child));
            }
        }

        return $childrenValues;
    }

    public function addOption(\Magento\Catalog\Model\Category $category) {
        $categoryOption = [
            'value'     => $category->getId(),
            'label'     => str_repeat(" > ", $category->getLevel() - 1) . $category->getName()
        ];

        return $categoryOption;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
