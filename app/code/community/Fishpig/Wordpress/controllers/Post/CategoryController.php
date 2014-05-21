<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Post_CategoryController extends Fishpig_Wordpress_Controller_Abstract
{
	/**
	 * Used to do things en-masse
	 * eg. include canonical URL
	 *
	 * @return false|Fishpig_Wordpress_Model_Post_Category
	 */
	public function getEntityObject()
	{
		return $this->_initPostCategory();
	}

	public function preDispatch()
	{
		parent::preDispatch();
		
		if (($term = Mage::registry('wordpress_term')) !== null) {
			$this->_forceForwardViaException('view', 'term');
			return false;
		}		
		
		return $this;
	}

	/**
	  * Display the category page and list blog posts
	  *
	  */
	public function viewAction()
	{
		$category = Mage::registry('wordpress_category');
		
		$this->_addCustomLayoutHandles(array(
			'wordpress_category_index', 
			'wordpress_category_'.$category->getId(),
			'WORDPRESS_CATEGORY_'.$category->getId(),
		));
			
		$this->_initializeBlogLayout();
		
		$this->_rootTemplates[] = 'template_post_list';
		
		$this->_title($category->getName());
		$this->_addCrumb('category', array('label' => $category->getName()));

		if ($seo = $this->getSeoPlugin()) {
			if ($seo->getCategoryNoindex()) {
				if ($headBlock = $this->getLayout()->getBlock('head')) {
					$headBlock->setRobots('noindex,follow');
				}
			}
		}
		
		$this->renderLayout();
	}

	
	/**
	 * Load the category based on the slug stored in the param 'category'
	 *
	 * @return Fishpig_Wordpress_Model_Post_Categpry
	 */
	protected function _initPostCategory()
	{
		if (($category = Mage::registry('wordpress_category')) !== null) {
			return $category;
		}
		
		$helper = Mage::helper('wordpress/router');
		
		$slug = $helper->getBlogUri();

		if ($helper->categoryUrlHasBase()) {
			$base = $helper->getCategoryBase();
			$slug = trim(substr($slug, strlen($base)), '/');
		}
		
		$termSlug = false;
		
		if (strpos($slug, '/') !== false) {
			list($slug, $termSlug) = explode('/', $slug);
		}
		
		$category = Mage::getModel('wordpress/post_category')->loadBySlug($slug);
			
		if ($category && $category->getId()) {
			if ($termSlug !== false) {
		
				$term = Mage::getModel('wordpress/term')->loadBySlug($termSlug);
				
				if ($term->getId() && !$term->isDefaultTerm()) {
					$term->setCategory($category);

					Mage::register('wordpress_category', $category);
					Mage::register('wordpress_term', $term);
					return $category;
				}
			}
			else {
				Mage::register('wordpress_category', $category);
				return $category;			
			}
		}
		
		return false;
	}
	
	/**
	 * Display the comment feed
	 *
	 */
	public function feedAction()
	{
		$this->getResponse()
			->setHeader('Content-Type', 'text/xml; charset=' . Mage::helper('wordpress')->getWpOption('blog_charset'), true)
			->setBody($this->getLayout()->createBlock('wordpress/feed_category')->setCategory(Mage::registry('wordpress_category'))->toHtml());
	}
}
