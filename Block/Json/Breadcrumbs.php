<?php
namespace JerVoo\Breadcrumbs\Block\Json;

class Breadcrumbs extends \Magento\Framework\View\Element\Template {
    private $_crumbs = [];

    public function _prepareLayout()
    {
        /** @var \JerVoo\Breadcrumbs\Block\Html\Breadcrumbs $breadcrumbsBlock */
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbsBlock) {
            $this->_crumbs = $breadcrumbsBlock->getCrumbs();
        }
        return parent::_prepareLayout();
    }

    public function getJson() {
        if (count($this->_crumbs)) {
            $json = [
                '@context'          => 'https://schema.org',
                '@type'             => 'BreadcrumbList',
                'itemListElement'   => []
            ];

            $counter = 1;
            foreach($this->_crumbs as $crumbName => $crumbInfo) {
                $json['itemListElement'][$counter-1] = [
                    '@type'             => 'ListItem',
                    'position'          => $counter,
                    'item'              => []
                ];

                if (isset($crumbInfo['link'])) {
                    $json['itemListElement'][$counter-1]['item']['@id'] = $crumbInfo['link'];
                    $json['itemListElement'][$counter-1]['item']['name'] = $crumbInfo['label'];
                } else {
                    $json['itemListElement'][$counter-1]['item']['name'] = $crumbInfo['label'];
                }

                $counter++;
            }
        }

        return $json ?? [];
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
