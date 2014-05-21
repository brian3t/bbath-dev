#######################################################

 Magic Thumb™
 Magento module version 3.36.2.2
 
 www.magictoolbox.com
 support@magictoolbox.com

 Copyright 2011 Magic Toolbox

#######################################################

INSTALLATION:

1. Unzip the contents, keeping the file structure intact (the folders and files).

2. Copy the 3 folders 'css', 'js' and 'images' into 'skin/fronted/[your_interface]/[your_theme]/' folder of your Magento store.

3. Rename the file 'list.phtml' to 'list_original.phtml' into the 'app/design/frontend/[your_interface]/[your_theme]/template/catalog/product/' folder of your Magento store.

4. Rename the file 'new.phtml' to 'new_original.phtml' into the 'app/design/frontend/[your_interface]/[your_theme]/template/catalog/product/' folder of your Magento store.

5. Copy three files 'list.phtml', 'new.phtml' and 'magictoolbox_list.phtml' into the 'app/design/frontend/[your_interface]/[your_theme]/template/catalog/product/' folder of your Magento store. 

6. Rename the file 'media.phtml' to 'media_original.phtml' into the 'app/design/frontend/[your_interface]/[your_theme]/template/catalog/product/view/' folder of your Magento store.

7. Copy the file 'media.phtml' into the 'app/design/frontend/[your_interface]/[your_theme]/template/catalog/product/view/' folder of your Magento store.

8. Copy the file 'configurable.phtml' into the 'app/design/frontend/[your_interface]/[your_theme]/template/catalog/product/view/type/options' folder of your Magento store. For safety, backup the old 'configurable.phtml' file.

9. Copy the files 'iehover-fix.js' and 'menu.js' into 'js/varien/' folder of your Magento store. To be safe, backup the old 'iehover-fix.js' and 'menu.js' files. Please skip this step if your Magento version is 1.4.1.0 or above.

10. Copy the folder 'magictoolbox' to the 'app/etc/' folder of your Magento store.

11. Open the file app/code/core/Mage/Catalog/Block/Product/View/Options/Type/Select.php in the editor.

Find this line:

  $select->setExtraParams

and add this line before it:

  $extraParams .= ' optitle="' . strtolower($_option->getTitle()) . '"';

Find this line:

  $count = 1;

and add this line before it:

  if($type == 'radio') $type .= '" optitle="' . strtolower($_option->getTitle());

12. Backup your app/design/frontend/[your_interface]/[your_theme]/template/page/html/head.phtml file, then open it in the editor.

13. Insert the following line at the end of the file:

  <?php require_once(BP . str_replace('/', DS, '/app/etc/magictoolbox/core/header.php')); ?>

14. Rename the app/etc/magictoolbox/magicthumb.settings.dat file to magicthumb.settings.ini.

15. Open the app/etc/magictoolbox/magicthumb.settings.ini file into the notepad and configure Magic Thumb. If you want to have different configurations for different themes you can place seperate 'magicthumb.settings.ini' file into the 'app/design/frontend/[your_interface]/[your_theme]/' folder.

16. You've now installed the demo version of Magic Thumb!

17. To upgrade, buy Magic Thumb and overwrite the magicthumb.js file with the same file from the full version.

Buy a single license here:

http://www.magictoolbox.com/buy/magicthumb/

