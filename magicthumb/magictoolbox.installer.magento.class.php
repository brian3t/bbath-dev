<?php
    /**
        Magento module installer class
    */

    require_once(dirname(__FILE__) . '/magictoolbox.installer.core.class.php');

    class MagicToolboxMagentoModuleInstallerClass extends MagicToolboxCoreInstallerClass {

        var $skinDir = '/skin/frontend/default/default';
        var $themeDir = '/app/design/frontend/default/default';
        var $themeDirDefault = '/app/design/frontend/default/default';
        var $themeDirBase = '/app/design/frontend/base/default';
        var $replaceJS = false;

        function MagicToolboxMagentoModuleInstallerClass() {
            $this->dir = dirname(dirname(__FILE__));
            $this->modDir = dirname(__FILE__) . '/module';
            $this->getSkinDir();
            //$this->prepareFiles();
        }

        function prepareFiles() {

            if(!file_exists($this->dir . $this->themeDir . '/template/catalog/product/list.phtml')) {
                $src = $this->dir . $this->themeDirDefault . '/template/catalog/product/list.phtml';
                if(!file_exists($src)) {
                    $src = $this->dir . $this->themeDirBase . '/template/catalog/product/list.phtml';
                }
                if(file_exists($this->dir . $this->themeDirDefault . '/template/catalog/product/list_original.phtml')) {
                    $src = $this->dir . $this->themeDirDefault . '/template/catalog/product/list_original.phtml';
                }/* elseif(file_exists($this->dir . $this->themeDirBase . '/template/catalog/product/list_original.phtml')) {
                    $src = $this->dir . $this->themeDirBase . '/template/catalog/product/list_original.phtml';
                }*/
                $this->copyFileRecursive($src, $this->dir . $this->themeDir . '/template/catalog/product/list.phtml');
            }
            if(!file_exists($this->dir . $this->themeDir . '/template/catalog/product/new.phtml')) {
                $src = $this->dir . $this->themeDirDefault . '/template/catalog/product/new.phtml';
                if(!file_exists($src)) {
                    $src = $this->dir . $this->themeDirBase . '/template/catalog/product/new.phtml';
                }
                if(file_exists($this->dir . $this->themeDirDefault . '/template/catalog/product/new_original.phtml')) {
                    $src = $this->dir . $this->themeDirDefault . '/template/catalog/product/new_original.phtml';
                }
                $this->copyFileRecursive($src, $this->dir . $this->themeDir . '/template/catalog/product/new.phtml');
            }

            if(!file_exists($this->dir . $this->themeDir . '/template/catalog/product/view/media.phtml')) {
                $src = $this->dir . $this->themeDirDefault . '/template/catalog/product/view/media.phtml';
                if(!file_exists($src)) {
                    $src = $this->dir . $this->themeDirBase . '/template/catalog/product/view/media.phtml';
                }
                if(file_exists($this->dir . $this->themeDirDefault . '/template/catalog/product/view/media_original.phtml')) {
                    $src = $this->dir . $this->themeDirDefault . '/template/catalog/product/view/media_original.phtml';
                }
                $this->copyFileRecursive($src,
                        $this->dir . $this->themeDir . '/template/catalog/product/view/media.phtml'
                );
            }

            if(!file_exists($this->dir . $this->themeDir . '/template/catalog/product/view/type/options/configurable.phtml')) {
                $src = $this->dir . $this->themeDirDefault . '/template/catalog/product/view/type/options/configurable.phtml';
                if(!file_exists($src)) {
                    $src = $this->dir . $this->themeDirBase . '/template/catalog/product/view/type/options/configurable.phtml';
                }
                if(file_exists($this->dir . $this->themeDirDefault . '/template/catalog/product/view/type/options/configurable_original.phtml')) {
                    $src = $this->dir . $this->themeDirDefault . '/template/catalog/product/view/type/options/configurable_original.phtml';
                }
                $this->copyFileRecursive($src,
                        $this->dir . $this->themeDir . '/template/catalog/product/view/type/options/configurable.phtml'
                );
            }

            if(!file_exists($this->dir . $this->themeDir . '/template/page/html/head.phtml')) {
                $src = $this->dir . $this->themeDirDefault . '/template/page/html/head.phtml';
                if(!file_exists($src)) {
                    $src = $this->dir . $this->themeDirBase . '/template/page/html/head.phtml';
                }
                $this->copyFileRecursive(
                        $src,
                        $this->dir . $this->themeDir . '/template/page/html/head.phtml'
                );
            }

            if(!file_exists($this->dir . '/app/code/local/Mage/Catalog/Block/Product/View/Options/Type/Select.php')) {
                $this->copyFileRecursive(
                        $this->dir . '/app/code/core/Mage/Catalog/Block/Product/View/Options/Type/Select.php',
                        $this->dir . '/app/code/local/Mage/Catalog/Block/Product/View/Options/Type/Select.php'
                );
            }

        }

        function getSkinDir() {
            // go to magento core folder
            chdir($this->dir);

            ob_start();
            // include core magento file (load front page)
            include('index.php');
            ob_end_clean();

            // get current interface and theme
            $interface = Mage::getSingleton('core/design_package')->getPackageName();
            $theme = Mage::getSingleton('core/design_package')->getTheme('template');
            $skin = Mage::getSingleton('core/design_package')->getTheme('skin');

            // setup paths
            $this->skinDir = '/skin/frontend/' . $interface . '/' . $skin;
            $this->themeDir = '/app/design/frontend/' . $interface . '/' . $theme;
            $this->themeDirDefault = '/app/design/frontend/' . $interface . '/default';

            //check Magento version
            $mageVersion = Mage::getVersion();
            $pattern = "/([0-9]+\.[0-9]+\.[0-9]+)(?:\.(?:[0-9]+))*/";
            $matches = array();
            if(preg_match($pattern, $mageVersion, $matches)) {
                if(version_compare($matches[1], '1.4.1', '<')) {
                    $this->replaceJS = true;
                }
            }

            //for fix url's in css files
            $this->resDir = "/" . preg_replace('/https?:\/\/[^\/]+\//is','',Mage::getSingleton('core/design_package')->getSkinUrl('css'));

            // return to installer folder
            chdir(dirname(__FILE__));

            return true;
        }

        function checkPlace() {
            $this->setStatus('check', 'place');
            if(!is_dir($this->dir . '/app') && !file_exists($this->dir . '/index.php')) {
                $this->setError('Wrong location: please upload the files from the ZIP archive to the Magento store directory.');
                return false;
            }
            return true;
        }

        // check what dirs/files we should check for perm
        function _checkPermRecursive($files) {
            $ret = array();
            foreach($files as $f) {
                while(!file_exists($this->dir . $f) && strlen($f) > 0 && strpos($f, '/') !== false) {
                    $f = substr($f, 0, strrpos($f, '/'));
                }
                $ret[] = $f;
            }
            return $ret;
        }

        function checkPerm() {
            $this->setStatus('check', 'perm');
            /*$sel = '/app/code/local/Mage/Catalog/Block/Product/View/Options/Type/Select.php';
            while(!file_exists($this->dir . $sel) && strlen($sel) > 0 && strpos($sel, '/') !== false) {
                $sel = substr($sel, 0, strrpos($sel, '/'));
            }*/
            $files = array_merge(array(
                // directory
                '/app/etc'
            ), $this->_checkPermRecursive(array(
                // dir
                $this->skinDir . '/js',
                $this->skinDir . '/css',
                $this->skinDir . '/images',

                $this->themeDir . '/template/catalog/product',

                $this->themeDir . '/template/catalog/product/view',
                $this->themeDir . '/template/page/html',
                // file

                $this->themeDir . '/template/catalog/product/list.phtml',
                $this->themeDir . '/template/catalog/product/new.phtml',

                $this->themeDir . '/template/catalog/product/view/media.phtml',

                $this->themeDir . '/template/catalog/product/view/type/options/configurable.phtml',

                $this->themeDir . '/template/page/html/head.phtml',

                '/app/code/local/Mage/Catalog/Block/Product/View/Options/Type/Select.php'

            )));

            if($this->replaceJS) {
                $files = array_merge($files, array('/js/varien', '/js/varien/menu.js', '/js/varien/iehover-fix.js'));
            }


            list($result, $wrang) = $this->checkFilesPerm($files);
            if(!$result) {
                $this->setError('This installer need to modify some Magento store files.');
                $this->setError('Please check write access for following files and/or dirrectories of your Magento store:');
                $this->setError(array_unique($wrang), '&nbsp;&nbsp;&nbsp;-&nbsp;');
                return false;
            }
            return true;
        }

        function backupFiles() {
            $this->prepareFiles();

            $this->setStatus('backup', 'files');
            $backups = array(

                $this->themeDir . '/template/catalog/product/list.phtml',
                $this->themeDir . '/template/catalog/product/new.phtml',

                $this->themeDir . '/template/catalog/product/view/media.phtml',

                $this->themeDir . '/template/catalog/product/view/type/options/configurable.phtml',

                $this->themeDir . '/template/page/html/head.phtml',

                '/app/code/local/Mage/Catalog/Block/Product/View/Options/Type/Select.php',

            );

            if($this->replaceJS) {
                $backups = array_merge($backups, array('/js/varien/menu.js', '/js/varien/iehover-fix.js'));
            }

            list($result, $wrang) = $this->createBackups($backups);
            if(!$result) {
                $this->setError('Can\'t create backups for following files:');
                $this->setError($wrang, '&nbsp;&nbsp;&nbsp;-&nbsp;');
                $this->setError('Please check write access');
                return false;
            }
            return true;
        }

        function restoreStep_backupFiles() {
            $backups = array(

                $this->themeDir . '/template/catalog/product/list.phtml',
                $this->themeDir . '/template/catalog/product/new.phtml',

                $this->themeDir . '/template/catalog/product/view/media.phtml',

                $this->themeDir . '/template/catalog/product/view/type/options/configurable.phtml',

                $this->themeDir . '/template/page/html/head.phtml',

                '/app/code/local/Mage/Catalog/Block/Product/View/Options/Type/Select.php',

            );

            if($this->replaceJS) {
                $backups = array_merge($backups, array('/js/varien/menu.js', '/js/varien/iehover-fix.js'));
            }

            $this->removeBackups($backups);
            return true;
        }

        function installFiles() {
            $this->setStatus('install', 'files');


            // rename list.phtml to list_original.phtml
            if(file_exists($this->dir . $this->themeDir . '/template/catalog/product/list_original.phtml')) {
                unlink($this->dir . $this->themeDir . '/template/catalog/product/list.phtml');
            } else {
                rename($this->dir . $this->themeDir . '/template/catalog/product/list.phtml', $this->dir . $this->themeDir . '/template/catalog/product/list_original.phtml');
            }
            // copy new list.phtml
            copy($this->modDir . '/list.phtml', $this->dir . $this->themeDir . '/template/catalog/product/list.phtml');

            // rename new.phtml to new_original.phtml
            if(file_exists($this->dir . $this->themeDir . '/template/catalog/product/new_original.phtml')) {
                unlink($this->dir . $this->themeDir . '/template/catalog/product/new.phtml');
            } else {
                rename($this->dir . $this->themeDir . '/template/catalog/product/new.phtml', $this->dir . $this->themeDir . '/template/catalog/product/new_original.phtml');
            }
            // copy new new.phtml
            copy($this->modDir . '/new.phtml', $this->dir . $this->themeDir . '/template/catalog/product/new.phtml');

            // copy magictoolbox_list.phtml
            copy($this->modDir . '/magictoolbox_list.phtml', $this->dir . $this->themeDir . '/template/catalog/product/magictoolbox_list.phtml');


            // rename list.phtml to media_original.phtml
            if(file_exists($this->dir . $this->themeDir . '/template/catalog/product/view/media_original.phtml')) {
                unlink($this->dir . $this->themeDir . '/template/catalog/product/view/media.phtml');
            } else {
                rename($this->dir . $this->themeDir . '/template/catalog/product/view/media.phtml', $this->dir . $this->themeDir . '/template/catalog/product/view/media_original.phtml');
            }
            // copy new media.phtml (replace old media.phtml)
            copy($this->modDir . '/media.phtml', $this->dir . $this->themeDir . '/template/catalog/product/view/media.phtml');


            // copy new configurable.phtml (replace old configurable.phtml)
            copy($this->modDir . '/configurable.phtml', $this->dir . $this->themeDir . '/template/catalog/product/view/type/options/configurable.phtml');

            if($this->replaceJS) {
                //copy JS files to fix menu flickering in IE
                copy($this->modDir . '/iehover-fix.js', $this->dir . '/js/varien/iehover-fix.js');
                copy($this->modDir . '/menu.js', $this->dir . '/js/varien/menu.js');
            }


            //copy magictoolbox folder into /app/etc
            $this->copyDir($this->modDir . '/magictoolbox', $this->dir . '/app/etc/magictoolbox');

            //copy js/css/img
            $this->copyDir($this->modDir . '/js', $this->dir . $this->skinDir . '/js');
            $this->copyDir($this->modDir . '/css', $this->dir . $this->skinDir . '/css');
            $this->copyDir($this->modDir . '/images', $this->dir . $this->skinDir . '/images');


            //modify Select.php file: allow use options to change image
            $c = file_get_contents($this->dir . '/app/code/local/Mage/Catalog/Block/Product/View/Options/Type/Select.php');
            $c = str_replace('$select->setExtraParams', '$extraParams .= \' optitle="\' . strtolower($_option->getTitle()) . \'"\'; $select->setExtraParams', $c);
            $c = str_replace('$count = 1;', 'if($type == \'radio\') $type .= \'" optitle="\' . strtolower($_option->getTitle()); $count = 1;', $c);
            file_put_contents($this->dir . '/app/code/local/Mage/Catalog/Block/Product/View/Options/Type/Select.php', $c);


            //modify head.phtml file: add headers 
            $c = file_get_contents($this->dir . $this->themeDir . '/template/page/html/head.phtml');
            if(!preg_match('/magictoolbox/is', $c)) {
                $c .= '<?php require_once(BP . str_replace(\'/\', DS, \'/app/etc/magictoolbox/core/header.php\')); ?>';
            }
            file_put_contents($this->dir . $this->themeDir . '/template/page/html/head.phtml', $c);

            //$this->setError('install files');
            return true;
        }

        function restoreStep_installFiles() {
            $files = array(

                $this->themeDir . '/template/catalog/product/list.phtml',
                $this->themeDir . '/template/catalog/product/new.phtml',

                $this->themeDir . '/template/catalog/product/view/media.phtml',

                $this->themeDir . '/template/catalog/product/view/type/options/configurable.phtml',

                $this->themeDir . '/template/page/html/head.phtml',

                '/app/code/local/Mage/Catalog/Block/Product/View/Options/Type/Select.php',

            );

            if($this->replaceJS) {
                $files = array_merge($files, array('/js/varien/menu.js', '/js/varien/iehover-fix.js'));
            }

            $this->restoreFromBackups($files);
            $this->removeDir($this->dir . '/app/etc/magictoolbox');
            unlink($this->dir . $this->themeDir . '/template/catalog/product/view/media_original.phtml');

            unlink($this->dir . $this->themeDir . '/template/catalog/product/list_original.phtml');
            unlink($this->dir . $this->themeDir . '/template/catalog/product/new_original.phtml');
            unlink($this->dir . $this->themeDir . '/template/catalog/product/magictoolbox_list.phtml');

            return true;
        }

        function upgrade($files) {
            $path = $this->dir . $this->skinDir . '/js/';
            foreach($files as $name => $file) {
                if(file_exists($path . $name)) {
                    unlink($path . $name);
                }
                file_put_contents($path . $name, $file);
                chmod($path . $name, 0755);
            }
            return true;
        }

    }
