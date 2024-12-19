<?php
namespace JerVoo\Breadcrumbs\Block\Html;

use Magento\Catalog\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Breadcrumbs extends \Magento\Framework\View\Element\Template {
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'JerVoo_Breadcrumbs::html/breadcrumbs.phtml';

    /**
     * @var Data
     */
    protected $_catalogData;

    /**
     * List of available breadcrumb properties
     *
     * @var string[]
     */
    protected $_properties = ['label', 'title', 'link', 'first', 'last', 'readonly'];

    protected $_registry;
    protected $_categoryFactory;
    protected $_productRepository;

    /**
     * List of breadcrumbs
     *
     * @var array
     */
    protected $_crumbs;

    public function __construct(
        Data $catalogData,
        Registry $registry,
        \Magento\Catalog\Model\ResourceModel\CategoryFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        Template\Context $context,
        array $data = []
    )
    {
        $this->_catalogData = $catalogData;
        $this->_registry = $registry;
        $this->_categoryFactory = $categoryCollectionFactory;
        $this->_productRepository = $productRepository;

        parent::__construct($context, $data);
    }

    /**
     * Add crumb
     *
     * @param string $crumbName
     * @param array $crumbInfo
     * @return Breadcrumbs
     */
    public function addCrumb($crumbName, $crumbInfo)
    {
        foreach ($this->_properties as $key) {
            if (!isset($crumbInfo[$key])) {
                $crumbInfo[$key] = null;
            }
        }

        if (!isset($this->_crumbs[$crumbName]) || !$this->_crumbs[$crumbName]['readonly']) {
            $this->_crumbs[$crumbName] = $crumbInfo;
        }

        return $this;
    }

    public function _prepareLayout()
    {
        $this->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
            ]
        );

        $path = $this->_catalogData->getBreadcrumbPath();
        if (/*count($path) == 1 && */isset($path['product'])) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->_registry->registry('current_product');
            $longestCategoryPath = [];
            $preferedCategoryPath = [];

            $categoryFactory = $this->_categoryFactory->create();

            foreach($product->getAvailableInCategories() as $categoryId) {
                /** @var \Magento\Catalog\Model\ResourceModel\Category $category */
                $category = ObjectManager::getInstance()->create(\Magento\Catalog\Model\Category::class);
                $categoryFactory->load($category, $categoryId);

                if ($category->getIsActive() && $category->getIncludeInMenu()) {
                    $pathParts = explode('/', $category->getPath());

                    if ($product->getData('preferred_breadcrumb_category') && $categoryId == $product->getData('preferred_breadcrumb_category')) {
                        $preferedCategoryPath = $pathParts;
                        break;
                    } else if ($preferedCategoryPath == [] && $category->getData('preferred_breadcrumb_category') == 1) {
                        $preferedCategoryPath = $pathParts;
                    } else if (count($pathParts) > count($longestCategoryPath)) {
                        $longestCategoryPath = $pathParts;
                    }
                }
            }

            /**
             * If a prefered category path has been found, this path should be used.
             * Otherwise the longest possible path should be used.
             */
            if ($preferedCategoryPath != []) {
                $longestCategoryPath = $preferedCategoryPath;
            }

            unset($preferedCategoryPath);

            // Remove first 2 elements from array
            array_shift($longestCategoryPath);
            array_shift($longestCategoryPath);
            foreach($longestCategoryPath as $categoryId) {
                /** @var \Magento\Catalog\Model\ResourceModel\Category $category */
                $category = ObjectManager::getInstance()->create(\Magento\Catalog\Model\Category::class);
                $categoryFactory->load($category, $categoryId);

                if ($category->getIsActive() && $category->getIncludeInMenu()) {
                    $link = $this->_storeManager->getStore()->getUrl($category->getUrlPath());
                    if (str_ends_with($link, '/')) {
                        $link = substr($link, 0, -1);
                    }

                    $this->addCrumb('category' . $categoryId, [
                        'label' => __($category->getName()),
                        'link' => $link
                    ]);
                }
            }

            $this->addCrumb('product', $path['product']);
        }
        elseif (count($path)) {
            foreach($path as $crumbName => $crumbInfo) {
                $this->addCrumb($crumbName, $crumbInfo);
            }
        }

        return parent::_prepareLayout();
    }

    public function getCrumbs() {
        return $this->_crumbs;
    }

    public function _toHtml()
    {
        if (count($this->_crumbs) <= 1) {
            return;
        }
        return parent::_toHtml();
    }
}
