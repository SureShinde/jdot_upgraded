<?php

namespace RLTSquare\SearchLimit\Model\ResourceModel\Query;

class Collection extends \Magento\Search\Model\ResourceModel\Query\Collection {



    public function setQueryFilter($query)
    {
        $this->getSelect()->reset(
            \Magento\Framework\DB\Select::FROM
        )->distinct(
            true
        )->from(
            ['main_table' => $this->getTable('search_query')]
        )->where(
            'num_results > 0 AND display_in_terms = 1 AND query_text LIKE ?',
            $this->_resourceHelper->addLikeEscape($query, ['position' => 'start'])
        )->order(
            'popularity ' . \Magento\Framework\DB\Select::SQL_DESC
        );
        if ($this->getStoreId()) {
            $this->getSelect()->where('store_id = ?', (int)$this->getStoreId());
        }
        return $this->setPageSize(10);
    }

}
