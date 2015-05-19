<?php
	class Tommorris_Commercebug_IndexController extends Mage_Core_Controller_Front_Action
	{	
		protected function _profileCollection()
		{
			//get the profiler
			$profiler = Mage::getSingleton('core/resource')
			->getConnection('core_read')
			->getProfiler();
						
			//enable the profiler
			$profiler->setEnabled(true);			
			
			//fetch a product collection
			$product = Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('*')
			->getLastItem();
			
			//dump our id for later use
			echo(
				'ID:' . $product->getId() . "\n\n"
			);
					
			//fetch queries from profiler
			$queries = $profiler->getQueryProfiles();
		
			header('Content-Type: text/plain');
			foreach($queries as $profile)
			{
				echo '--------------------------------------------------' . "\n";
				echo $profile->getQuery() . "\n";
				echo '--------------------------------------------------' . "\n\n";
			}
			exit;		
		}
		
		protected function _profileLoad()
		{
			//get the profiler
			$profiler = Mage::getSingleton('core/resource')
			->getConnection('core_read')
			->getProfiler();
			
			//enable the profiler
			$profiler->setEnabled(true);			
			
			//fetch a product collection
			$product = Mage::getModel('catalog/product')
			->load(167);
			
			//dump our id for later use
			echo(
				'ID:' . $product->getId() . "\n\n"
			);
					
			//fetch queries from profiler
			$queries = $profiler->getQueryProfiles();
		
			header('Content-Type: text/plain');
			foreach($queries as $profile)
			{
				echo '--------------------------------------------------' . "\n";
				echo $profile->getQuery() . "\n";
				echo '--------------------------------------------------' . "\n\n";
			}
			exit;		
		}		
		
		protected function _profileLoadAndSave()
		{
			//work around weird behavior that prevents saving products
			//Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
			//get the profiler
			$profiler = Mage::getSingleton('core/resource')
			->getConnection('core_read')
			->getProfiler();
			
			//enable the profiler
			$profiler->setEnabled(true);			
			
			//fetch a product collection
			$product = Mage::getModel('catalog/product')
			->load(167);
			
			//dump our id for later use
			echo(
				'ID:   ' . $product->getId() . "\n" . 
				'Name: ' . $product->getName() . "\n\n" 
			);
					
			$product->setName('Awesome -- ' . $product->getName())
			->save();
			
			//fetch queries from profiler
			$queries = $profiler->getQueryProfiles();

			header('Content-Type: text/plain');
			foreach($queries as $profile)
			{
				echo '--------------------------------------------------' . "\n";
				echo $profile->getQuery() . "\n";
				if($params = $profile->getQueryParams())
				{
					echo "Params: [" . implode("],[",$params) . "]\n";
				}
				
				echo '--------------------------------------------------' . "\n\n";
			}
			exit;				
		}
		
		public function indexAction()
		{
		}
	}