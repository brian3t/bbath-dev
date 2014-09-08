<?php
	class Tommorris_Commercebug_Block_Html extends Mage_Core_Block_Template
	{	
		public function __construct()
		{
			//can't do this, the scriptpath is set in the actual render view method
			//$this->setScriptPath(dirname(__FILE__) . '/../static');
		}

		protected function _checkValidScriptPath($scriptPath)
		{
			$paths_to_check = array(Mage::getBaseDir('design'),Mage::getModuleDir('', 'Tommorris_Commercebug'));
			$valid			= false;
			foreach($paths_to_check as $path)
			{
				if(strpos($scriptPath, realpath($path)) === 0)
				{
					$valid = true;
				}
			}
			return $valid;		
		}
		
		public function setScriptPath($dir)
		{
			$scriptPath = realpath($dir);
			if ($this->_checkValidScriptPath($scriptPath)) {
				$this->_viewDir = $dir;
			} else {
				Mage::log('Not valid script path:' . $dir, Zend_Log::CRIT, null, null, true);
			}
			return $this;
		}
    
	    public function fetchView($fileName)			    
	    {	    	
	    	$module_dir = Mage::getModuleDir('', 'Tommorris_Commercebug');
	    	$this->setScriptPath($module_dir . '/static');
	    	return parent::fetchView($this->getTemplate());
	    }
	    
	    public function getPathSkin()
	    {
	    	$base_skin = Mage::getBaseUrl('skin');
	    	$base_skin = preg_replace('{/$}','',$base_skin);
	    	$url = str_replace('{{base_skin}}',$base_skin,Mage::getStoreConfig('commercebug/options/path_skin'));
	    	//legacy, add root path if it's not there AND we're not an http url
	    	
	    	if( (strpos($url, $base_skin) !== 0)  && $url[0] != '/' )
	    	{
	    		$url = '/' . $url;
	    	}
	    	return $url;
	    }
	 
	 	const UPDATE_URL = 'http://commercebug.alanstorm.com/index.php/version?callback=?';
	 	public function getUpdateUrl()
	 	{
	 		return self::UPDATE_URL;
	 	}	 		 	


		//86400 = 1 day	 	
	 	const UPDATE_CHECK_RATE_IN_SECONDS = 86400;
	 	public function getCheckForUpdatesFlag()
	 	{			
	 		$last_time = Mage::getSingleton('commercebug/jsonbroker')->jsonDecode(Mage::getStoreConfig('commercebug/options/update_last_check'));
	 		$last_time = $last_time->date;	 			
	 		if((strToTime($last_time) + self::UPDATE_CHECK_RATE_IN_SECONDS) > time())
	 		{
	 			return false;
	 		}	 		
	 		
	 		//if we're still here, check teh config flag
	 		return Mage::getStoreConfig('commercebug/options/check_for_updates');
	 	}

		public function fetchUseKeyboardShortcuts()
		{
	 		return Mage::getStoreConfig('commercebug/options/keyboard_shortcuts');		
		}
		public function calculateNextUpdateCheck()
		{
			$last_time = Mage::getModel('commercebug/jsonbroker')->jsonDecode(Mage::getStoreConfig('commercebug/options/update_last_check'));		
			return date(DATE_RFC822, strToTime($last_time->date)+self::UPDATE_CHECK_RATE_IN_SECONDS);
		}
		public function getLastUpdateCheck()
		{
			$last_time = Mage::getModel('commercebug/jsonbroker')->jsonDecode(Mage::getStoreConfig('commercebug/options/update_last_check'));		
			return date(DATE_RFC822, strToTime($last_time->date));		
		}
	 	public function resetLastUpdated()
	 	{
	 		$object				= new StdClass();
	 		$object->date		= date(DATE_RFC822);	 		
	 		
			$groups_value 		= array();			
			$groups_value['options']['fields']['update_last_check']['value'] = Mage::getModel('commercebug/jsonbroker')->jsonEncode($object);
			$model = Mage::getModel('adminhtml/config_data')
				->setSection('commercebug')
				->setWebsite(null)
				->setStore(null)
				->setGroups($groups_value)
				->save();
				
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();				
	 	}
	 	
	 	public function fetchResultsFromLastUpdateCheck()
	 	{
			return Mage::getStoreConfig('commercebug/options/update_last_check');	 	
	 	}
	 	
	 	public function includeStatic($path)
	 	{	 		
	 		ob_start();
	 		include('app/code/local/Alanstormdotcom/Commercebug/static/' . $path);
	 		return ob_get_clean();
	 	}
	 	
	 	public function getLayout()
	 	{
	 		return Mage::getSingleton('core/layout');
	 	}

        /**
        * Fixing up the tyranny of allow symlinks 
        * If you have a custom block that extends 
        *   Tommorris_Commercebug_Block_Html
        * and want to get rid of the exceptions 
        * being thrown, just add the following
        * method definition to your block
        *
        *   function useTemplateFile()
        *   {
        *       return true;
        *   }
        *
        */
        public function _toHtml()
        {
            if($this->useTemplateFile())
            {
                return parent::_toHtml();
            }

            ob_start();
            switch($this->getTemplate())
            {
                case 'head_jquery_ui.phtml':
                    $this->_renderHeadJqueryUi();
                    break;
                case 'bodyend_jquery_ui.phtml':
                    $this->_renderBodyendJqueryUi();
                    break;
                case 'bodystart_jquery_ui_tabs_html.phtml':
                    $this->_renderBodystartJqueryUiTabsHtml();
                    break;
                case 'tabs/ascommercebug_welcome.phtml':
                    $this->_tabsAscommercebugWelcome();
                    break;
                case 'tabs/ascommercebug_controllers.phtml':
                    $this->_tabsAscommercebugControllers();    
                    break;
                case 'tabs/ascommercebug_models.phtml':
                    $this->_tabsAscommercebugModels();
                    break;
                case 'tabs/ascommercebug_collections.phtml';
                    $this->_tabsAscommercebugCollections();
                    break;
                case 'tabs/ascommercebug_blocks.phtml':
                    $this->_tabsAscommercebugBlocks();
                    break;
                case 'tabs/ascommercebug_layout.phtml':
                    $this->_tabsAscommercebugLayout();
                    break;
                case 'tabs/ascommercebug_lookup.phtml':
                    $this->_tabsAscommercebugLookup();
                    break;
                case 'tabs/ascommercebug_systemtasks.phtml':
                    $this->_tabsAscommercebugSystemtasks();
                    break;
                default:
                    $output = 'See docblock for: ';
                    Mage::throwException($output . __METHOD__ . 'in ' . 
                    __FILE__ . '::' . '::' . __LINE__);        
            }      
            
            $output = ob_get_clean();
            return $output;
        }
        
        protected function _tabsAscommercebugSystemtasks()
        {
            ?>
            <p>
                <button id="ascommercebug_but_clear_cache" class="fg-button ui-state-default ui-corner-all"><?php echo $this->__('Clear Cache');?></button>
            </p>
            <div id="ascommercebug_clear_cache_results">
            </div>		            
            <?php
        }
        
        protected function _tabsAscommercebugLookup()
        {
            ?>
            <div id="ascommercebug_lookup_container">
                <p><?php echo $this->__('Enter a URI (<span class="classname">catalog/product</span>) or a class name 
                (<span class="classname">Mage_Catalog_Model_Product</span>).</p>');?>
                <div><?php echo $this->__('Using URI context'); ?>
                <select id="ascommercebug_select_context">
                    <option value="all"><?php echo $this->__('All'); ?></option>
                    <option value="model"><?php echo $this->__('Model'); ?></option>
                    <option value="block"><?php echo $this->__('Block'); ?></option>								
                    <option value="helper"><?php echo $this->__('Helper'); ?></option>				
                </select>, 
                <input type="text" id="ascommercebug_text_lookup_uri"/>  
                <button id="ascommercebug_but_lookup" class="fg-button ui-state-default ui-corner-all">
                    <?php echo $this->__('Resolves To'); ?>
                </button>
                </div>
                <div id="ascommercebug_lookup_results">
                </div>
            </div>            
            <?php
        }
        
        protected function _tabsAscommercebugLayout()
        {
            ?>
            <?php
                $data = new stdClass();
                $data->dot = Tommorris_Commercebug_Model_Observer_Dot::renderGraph();
            ?>
            
            <script type="text/javascript">	
                var as_commercebug_display_dot = function(){
                    var as_commercebug_dot = <?php echo json_encode($data);?>;		
                    jQueryCommercebug('#as_commercebug_dot_console').val(as_commercebug_dot.dot);
                    jQueryCommercebug('#as_commercebug_dot_console').toggle();
                }
            </script>                        
            
            <textarea style="display:none;width:500px;height:300px" name="as_commercebug_dot_console" id="as_commercebug_dot_console"></textarea>
            
            <div id="ascommercebug_layout_results">
            </div>		
            
            
            <?php
        }
        
        protected function _tabsAscommercebugBlocks()
        {
            echo '<div id="ascommercebug_blocks_results"></div>';
        }
        
        protected function _tabsAscommercebugCollections()
        {
            ?>
            <?php echo $this->__('<strong>Note:</strong> This table captures collections which inherit from core Magento collection class (<code>Mage_Core_Model_Mysql4_Collection_Abstract</code>).  Legacy collections which inherit directly from <code>Varien_Data_Collection_Db</code> are not captured.
            '); ?>
            <div id="ascommercebug_collections_results">
            </div>
            
            <script type="text/javascript">
                jQueryCommercebug(document).ready(function(){
                    jQueryCommercebug.fn.commercebug.registerCallback(function(info){
                        var o = jQueryCommercebug.parseJSON(info);
                        var collections 		= o.collections;			
                        var collectionFiles 	= o.collectionFiles;				
                        var collectionModels	= o.collectionModels;
                        var table  = [];
                        table.push('<table id="'+s_prefix+'_collections_results_table" class="tablesorter">');
                        table.push('<thead><tr><th><?php echo $this->__('Collection Name'); ?><\/th><!--<th>Model Name<\/th>--><th><?php echo $this->__('Times Instantiated'); ?><\/th><\/tr><\/thead>');
                    
                        table.push('<tbody>');		
                        var c=0;
                        jQueryCommercebug.each(collections, function(key, value){
                            var odd_or_even = c % 2 ? 'odd' : 'even';
                            table.push('<tr class="'+odd_or_even+'">');
                    
                            table.push('<td class="classname">');
                            table.push(key);
                            table.push('<br />');
                            table.push('<span class="pathinfo">' + 
                            'Collects Mage::getModel("'+collectionModels[key]+'"); <br />' +
                            collectionFiles[key] + 
                            '<\/span>');								
                            
                            table.push('<\/td>');
            // 				table.push('<td>');
            // 				table.push(collectionModels[key]);
            // 				table.push('<\/td>');				
                            table.push('<td>');
                            table.push(value);
                            table.push('<\/td>');
                            table.push('<' + '/tr>');			
                            c++;
                        });
                        table.push('<\/tbody>');						
                        table.push('<\/table>');
                        jQueryCommercebug('#'+s_prefix+'_collections_results').html(table.join(''));
                        jQueryCommercebug('#'+s_prefix+'_collections_results_table').tablesorter({widgets: ['zebra']});			
                    });
                });
            </script>
            
            <?php
        }
        
        protected function _tabsAscommercebugModels()
        {
            echo '<div id="ascommercebug_models_results"></div>';
        }
        
        protected function _tabsAscommercebugControllers()
        {
            ?>
            <div id="ascommercebug_controllers_results">
                <table id="ascommercebug_controllers_results_table" class="tablesorter">
                    <thead>
                        <tr>
                            <th>
                                <?php echo $this->__('Label'); ?>
                            </th>
                            <th>
                                <?php echo $this->__('Value'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?php echo $this->__('Controller Class Name'); ?>
                            </td>
                            <td>
                                <span id="ascommercebug_controllers_classname" class="classname"></span> 
                                <br />
                                <span id="ascommercebug_controllers_filename" class="pathinfo"></span> 
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $this->__('Full Action Name'); ?>
                            </td>
                            <td>
                                <span id="ascommercebug_controllers_full_actionname"></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $this->__('Module Name'); ?>
                            </td>
                            <td>
                                <span id="ascommercebug_controllers_modulename"></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $this->__('Controller Name'); ?>
                            </td>
                            <td>
                                <span id="ascommercebug_controllers_controllername"></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $this->__('Action Name'); ?>
                            </td>
                            <td>
                                <span id="ascommercebug_controllers_actionname"></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $this->__('Path Info'); ?>
                            </td>
                            <td>
                                <span id="ascommercebug_controllers_pathinfo"></span>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <?php echo $this->__('CMS Page ID'); ?>
                            </td>
                            <td>
                                <span id="ascommercebug_controllers_page_id"></span>
                            </td>
                        </tr>			
                    </tbody>
                </table>
            </div>
            
            <?php
        }
        
        protected function _tabsAscommercebugWelcome()
        {
            ?>
            <p> <?php echo $this->__('Information about the current request can be found in the tabs on the right.'); ?></p>
            <p>
            <?php echo $this->__('Use the form below to reload the tabs with information from a different request. (see System -&gt; Configuration -&gt; Commerce Bug)');?>
            </p>
            <p id="ascommercebug_version">
            
            </p>
            <form>
                <ul>
                    <li>
                    <h2><?php echo $this->__('Paste JSON from log:'); ?></h2>
                    <div id="ascommercebug_results_json"></div>
                    </li>
                    <li><textarea id="ascommercebug_textarea_json"></textarea></li>
                    <li><button id="ascommercebug_button_json" onclick="return false;" class="fg-button ui-state-default ui-corner-all"><?php echo $this->__('Render Into Tabs');?></button></li>
                </ul>
            </form>
            
            <?php
        }
        
        protected function _renderBodystartJqueryUiTabsHtml()
        {
            ?>
            <div style="text-align:left;background-color:#fff;display:block;width:4em;">
                + <a href="#" id="ascommercebug_showhide">Debug</a>
            </div>
            <div id="ascommercebug-tabs" style="display:none;">
                <ul>
                    <?php foreach($this->getTabIdPairs() as $id=>$name): ?>
                        <li><a href="#<?php echo $id;?>"><span><?php echo $name;?></span></a></li>
                    <?php endforeach; ?>
                </ul>
                
                <?php foreach($this->getTabIdAndHtmlPairs() as $id=>$html): ?>
                    <div id="<?php echo$id;?>" class="tab-container">
                        <?php echo $html;?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <script type="text/javascript">
                jQueryCommercebug(document).ready(function(){
                    jQueryCommercebug("#ascommercebug-tabs").commercebug(commercebug_json);
                    
                    <?php if(Mage::getStoreConfig('commercebug/options/keyboard_shortcuts')): ?>		
                        jQueryCommercebug(document).bind('keyup',function(e){		
                            var code = (e.keyCode ? e.keyCode : e.which);
                            
                            //bail if we're in certain tags.  Not ideal as it kills
                            //tab navigation, but that's why we let them turn it off
                            if( jQueryCommercebug(e.target).is('input') || 
                            jQueryCommercebug(e.target).is('textarea') 	||
                            jQueryCommercebug(e.target).is('select') 	||
                            jQueryCommercebug(e.target).is('option')	)
                            {
                                return true;
                            }
                            
                            if(code == 76)
                            {
                                jQueryCommercebug('#ascommercebug-tabs').commercebug.tab_forward('#ascommercebug-tabs');
                            }
                            else if (code == 72)
                            {
                                jQueryCommercebug('#ascommercebug-tabs').commercebug.tab_backwards('#ascommercebug-tabs');
                            }				
                        });		
                    <?php endif; ?>		
                    
                });			
            </script>
            
            <!-- updater -->
            
            <?php if($this->getCheckForUpdatesFlag()): ?>
                <script type="text/javascript">
                jQueryCommercebug.getJSON('<?php echo $this->getUpdateUrl();?>',function(data){
                    var current_version = '<?php echo Mage::getConfig()->getModuleConfig(Mage::helper('commercebug')->calculateRealModuleName())->version;?>';
                    
                    var message 		= 'Your Version: <strong>' + current_version + '<\/strong>; Latest Version:<strong>' + data.message;
                    message				+= '<\/strong>. The latest version may always be obtained from your <a href="/">download page</a>';
                    jQueryCommercebug('#ascommercebug_version').html(message);		
                });	
                </script>
                <?php $this->resetLastUpdated();?>
            <?php else:?>	
                <script type="text/javascript">	
                    var r = jQueryCommercebug.parseJSON('<?php echo $this->fetchResultsFromLastUpdateCheck();?>');
                    jQueryCommercebug('#ascommercebug_version').html('<?php $this->__("Last Update Check:");?> <?php echo $this->getLastUpdateCheck();?>');		
                </script>
            <?php endif;?>            
            <?php
        }
        
        protected function _renderBodyendJqueryUi()
        {
            ?>
            <?php $skin_path = $this->getPathSkin(); ?>
            <script type="text/javascript"></script>
            <script type="text/javascript" src="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/js/jquery-1.4.2.min.js"></script>
            <script type="text/javascript" src="<?php echo $skin_path; ?>/plugins/jquery.form.js"></script>
            <script type="text/javascript">
            
            jQueryCommercebug.noConflict();
            
            </script>
            <script type="text/javascript" src="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/js/jquery.cookie.js"></script>
            <script type="text/javascript" src="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/js/jquery-ui-1.8.custom.min.js"></script>
            <script type="text/javascript" src="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/js/jquery.tablesorter.min.js"></script>
            <script type="text/javascript" src="<?php echo $skin_path; ?>/commercebug.1.4.js"></script>
            
            <?php
        }
        
        protected function _renderHeadJqueryUi()
        {
            ?>
            <?php $skin_path = $this->getPathSkin(); ?>
            <link type="text/css" href="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/css/tablesorter/blue/style.css" rel="stylesheet" />
            <link type="text/css" href="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/css/overcast/jquery-ui-1.8.custom.css" rel="stylesheet" />
            <link type="text/css" href="<?php echo $skin_path; ?>/commercebug.css" rel="stylesheet" />            
            <?php
        }
        
        /**
        * Should this block render with a template?
        */        
        public function useTemplateFile()
        {
            return false;
        }
	}