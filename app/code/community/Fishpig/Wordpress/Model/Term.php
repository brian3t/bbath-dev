<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
class Fishpig_Wordpress_Model_Term extends Fishpig_Wordpress_Model_Abstract
{
	public function _construct()
	{
		$this->_init('wordpress/term');
	}
	
	/**
	 * Determine whether this term is a custom term or a default term
	 *
	 * @return bool
	 */
	public function isDefaultTerm()
	{
		return in_array($this->_getData('taxonomy'), array('category', 'link_category', 'post_tag'));
	}
	
	/**
	 * Retrieve the parent term
	 *
	 * @reurn false|Fishpig_Wordpress_Model_Term
	 */
	public function getParentTerm()
	{
		if (!$this->hasParentTerm()) {
			$this->setParentTerm(false);
			
			if ($this->getParentId()) {
				$parentTerm = Mage::getModel($this->getResourceName())->load($this->getParentId());
				
				if ($parentTerm->getId()) {
					$this->setParentTerm($parentTerm);
				}
			}
		}
		
		return $this->_getData('parent_term');
	}
	
	/**
	 * Retrieve the path for the term
	 *
	 * @return string
	 */
	public function getPath()
	{
		if (!$this->hasPath()) {
			if ($this->getParentTerm()) {
				$this->setPath($this->getParentTerm()->getPath() . '/' . $this->getId());
			}
			else {
				$this->setPath($this->getId());
			}
		}
		
		return $this->_getData('path');
	}
	
	/**
	 * Retrieve a collection of children terms
	 *
	 * @return Fishpig_Wordpress_Model_Mysql_Term_Collection
	 */
	public function getChildrenTerms()
	{
		if (!$this->hasChildrenTerms()) {
			$this->setChildrenTerms($this->getCollection()->addParentFilter($this));
		}
		
		return $this->_getData('children_terms');
	}
	
	/**
	 * Loads the posts belonging to this category
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */    
    public function getPostCollection()
    {
		if (!$this->hasPostCollection()) {
			if ($this->getTaxonomy()) {
				$posts = Mage::getResourceModel('wordpress/post_collection')
    				->addIsPublishedFilter()
    				->addTermIdFilter($this->getId(), $this->getTaxonomy());
    			
	    		$this->setPosts($posts);
	    	}
    	}
    	
    	return $this->_getData('posts');
    }
    
	/**
	 * Retrieve the numbers of items that belong to this term
	 *
	 * @return int
	 */
	public function getItemCount()
	{
		return $this->getCount();
	}

	/**
	 * Load a term based on it's slug
	 *
	 * @param string $slug
	 * @return $this
	 */	
	public function loadBySlug($slug)
	{
		return $this->load($slug, 'slug');
	}
	
	/**
	 * Retrieve the parent ID
	 *
	 * @return int|false
	 */	
	public function getParentId()
	{
		if ($this->_getData('parent')) {
			return $this->_getData('parent');
		}
		
		return false;
	}
	
	public function getTaxonomyType()
	{
		return $this->getTaxonomy();
	}
	
	public function getUrl()
	{
		if ($this->getCategory()) {
			return Mage::helper('wordpress')->getUrl($this->getCategory()->getSlug() . '/' . $this->getSlug() . '/');
		}
		
		return Mage::helper('wordpress')->getUrl($this->getSlug() . '/');
	}
}
