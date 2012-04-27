<?php
/**
 * @version   1.0.0
 * @package   com_jfoobar
 * @copyright Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('MOLAJO') or die;

/** @const JPATH_ADMINISTRATOR '/home/nibra/Joomla Extensions/Joomla_2.5-Std-Installation/administrator' */
require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/discover.php';
include_once dirname(__FILE__) . '/file.php';
jimport('joomla.client.helper');
jimport('joomla.application.component.model');
jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.filesystem.file');

/**
 * Extension Manager Create Model
 *
 * @package    Molajo
 * @subpackage com_jfoobar
 * @since      1.6
 */
class InstallerModelCreate extends JModel
{
    /** @var  string  Model context string */
    protected $_context = 'com_jfoobar.create';

    /** @var  string  Singular of the extension name to be replaced */
    protected $_replacesingle = 'jfoobar';

    /** @var  string  Plural of the extension name to be replaced */
    protected $_replaceplural = 'jfoobars';

    /** @var  string  Singular of the new extension name */
    protected $_single = null;

    /** @var  string  Plural of the new extension name */
    protected $_plural = null;

    /**
     * Populate the model state
     *
     * Method to auto-populate the model state.
     *
     * Note: Calling getState in this method will result in recursion.
     *
     * @since    1.6
     */
    protected function populateState()
    {
        $application = JFactory::getApplication();
        $input = $application->input;

        // messages
        $this->setState('message', $application->getUserState('com_jfoobar.message'));
        $this->setState('extension_message', $application->getUserState('com_jfoobar.extension_message'));

        $application->setUserState('com_jfoobar.message', '');
        $application->setUserState('com_jfoobar.extension_message', '');

        // extension type
        $this->setState('create.createtype', $input->get('createtype', 'component', 'cmd'));

        // module
        $this->setState('create.module_name', $input->get('module_name', '', 'cmd'));

        // plugin
        $this->setState('create.plugin_name', $input->get('plugin_name', '', 'cmd'));
        $this->setState('create.plugin_type', $input->get('plugin_type', 'content', 'cmd'));

        parent::populateState();
    }

    /**
     * Create and install an extension
     *
     * Creates and then Installs a Molajo Extension as per user instructions
     *
     * Note: was not able to use the create controller - the form submit of create.create did not find the folder/file
     * Change the task to create and added the create method to the display controller
     * JLoader::register('InstallerControllerCreate', MOLAJO_LIBRARY_COM_JFOOBARER.'/controllers/create.php');
     * require_once MOLAJO_LIBRARY_COM_JFOOBARER.'/controllers/create.php';
     *
     * @return  boolean|string  True or extension name on success, false on failure
     */
    function create()
    {
        // set ftp credentials, if used
        JClientHelper::setCredentialsFromRequest('ftp');

        // component
        if ($this->getState('create.createtype') == 'component') {
            return $this->_createComponent();

        } else if ($this->getState('create.createtype') == 'module') {
            return $this->_createModule();

        } else if ($this->getState('create.createtype') == 'plugin') {
            return $this->_createPlugin();

        } else if ($this->getState('create.createtype') == 'layout') {
            return $this->_createLayout();

        } else if ($this->getState('create.createtype') == 'template') {
            return $this->_createTemplate();

        } else {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_CREATE_INVALID_EXTENSION_TYPE_FAILED') . ': ' . $this->getState('create.createtype'), 'error');
            return false;
        }
    }

    /**
     * Create a component
     *
     * Copies files from source to Extension location and changes literals to correct values
     *
     * @return  boolean|string  Extension name on success, false on failure
     *
     * @since   1.6
     */
    protected function _createComponent()
    {
        $application = JFactory::getApplication();
        $input = $application->input;

        // file, class and method
        $classFolder = __DIR__ . '/component/';

        $filename = JFile::makeSafe($input->get('source', 'jfoobars', 'cmd'));
        $filename = JFilterOutput::stringURLSafe($filename);
        $extensionClassname = 'InstallerModelCreate' . ucfirst($filename) . 'Component';
        $filename = $filename . '.php';

        // register create class
        $filehelper = new MolajoFileHelper();
        $results = $filehelper->requireClassFile($classFolder . $filename, $extensionClassname);
        if ($results === false) {
            $application->enqueueMessage(JText::_('PLG_SYSTEM_CREATE_INSTALL_EXTENSION_FAILED') . ': ' . $extensionClassname, 'error');
            return false;
        }

        // create extension
        /** @var InstallerModelCreate $extensionCreator */
        $extensionCreator = new $extensionClassname();
        $extension = $extensionCreator->create();
        if ($extension === false) {
            $application->enqueueMessage(JText::_('PLG_SYSTEM_CREATE_INSTALL_EXTENSION_FAILED') . ': ' . $this->getState('create.createtype'), 'error');
            return false;
        }

        // install extension
        $results = $this->_installExtension(strtolower($extension));
        if (!$results) {
            $application->enqueueMessage(JText::_('PLG_SYSTEM_CREATE_INSTALL_EXTENSION_FAILED') . ': ' . $this->getState('create.createtype'), 'error');
            return false;
        }

        return $extension;
    }

    /**
     * Create a module
     *
     * Copies files from source to Extension location and changes literals to correct values
     *
     * @return  boolean|string  Extension name on success, false on failure
     *
     * @since   1.6
     */
    protected function _createModule()
    {
        $application = JFactory::getApplication();
        $input = $application->input;

        // file, class and method
        $classFolder = __DIR__ . '/module/';

        $filename = JFile::makeSafe($input->get('source', 'jfoobars', 'cmd'));
        $filename = JFilterOutput::stringURLSafe($filename);
        $extensionClassname = 'InstallerModelCreate' . ucfirst($filename) . 'Module';
        $filename = $filename . '.php';

        // register create class
        $filehelper = new MolajoFileHelper();
        $results = $filehelper->requireClassFile($classFolder . $filename, $extensionClassname);
        if ($results === false) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_CREATE_INSTALL_EXTENSION_FAILED') . ': ' . $extensionClassname, 'error');
            return false;
        }

        // create extension
        /** @var InstallerModelCreate $extensionCreator */
        $extensionCreator = new $extensionClassname ();
        $extension = $extensionCreator->create();
        if ($extension === false) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_CREATE_INSTALL_EXTENSION_FAILED') . ': ' . $this->getState('create.createtype'), 'error');
            return false;
        }

        // install extension
        $results = $this->_installExtension(strtolower($extension));
        if (!$results) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_CREATE_INSTALL_EXTENSION_FAILED') . ': ' . $this->getState('create.createtype'), 'error');
            return false;
        }

        return $extension;
    }

    /**
     * Create a plugin
     *
     * Copies files from source to Extension location and changes literals to correct values
     *
     * @return  boolean|string  Extension name on success, false on failure
     */
    protected function _createPlugin()
    {
        return true;
    }

    /**
     * Create a layout
     *
     * @return  boolean|string  Extension name on success, false on failure
     */
    protected function _createLayout()
    {
        return true;
    }

    /**
     * Create a template
     *
     * @return  boolean|string  Extension name on success, false on failure
     */
    protected function _createTemplate()
    {
        return true;
    }

    /**
     * Install an extension
     *
     * @param  string  $extension  The name of the extension
     *
     * @return bool    True on success
     */
    protected function _installExtension($extension)
    {
        // verify package retrieved
        $installer = new InstallerModelDiscover();

        $results = $installer->purge();
        if (!$results) {
            JFactory::getApplication()->setUserState('com_jfoobar.message', JText::_('PLG_SYSTEM_CREATE_PURGE_DISCOVERY_FAILED'));
            return false;
        }

        // verify package retrieved
        $installer->discover();

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('extension_id');
        $query->from('`#__extensions`');
        $query->where('`state`= -1');
        $query->where('`element`=' . $db->quote($extension));

        $db->setQuery((string)$query);
        $discoveredExtensionID = (int)$db->loadResult();

        if ($discoveredExtensionID <= 0) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_CREATE_RETRIEVE_EXTENSION_ID_FAILED') . ': ' . $discoveredExtensionID, 'error');
            return false;
        }

        // install created extension
        $installer = JInstaller::getInstance();
        $installer->discover_install($discoveredExtensionID);

        $application = JFactory::getApplication();

        $this->setState('action', 'remove');
        $this->setState('name', $installer->get('name'));
        $application->setUserState('com_jfoobar.message', $installer->message);
        $application->setUserState('com_jfoobar.extension_message', $installer->get('extension_message'));

        // double-check that the extension is no longer listed as not installed
        $query = 'SELECT extension_id FROM #__extensions where state = -1 AND extension_id = ' . $discoveredExtensionID;
        $dbo = JFactory::getDBO();
        $dbo->setQuery($query);
        $discoveredExtensionID = (int)$dbo->loadResult();
        if ($discoveredExtensionID > 0) {
            $application->setUserState('com_jfoobar.message', JText::_('PLG_SYSTEM_CREATE_INSTALL_EXTENSION_FAILED'));
            return false;
        }

        // results
        $application->enqueueMessage(JText::sprintf('PLG_SYSTEM_CREATE_INSTALL_SUCCESS', JText::_('PLG_SYSTEM_CREATE_INSTALL_TYPE_' . strtoupper($this->getState('create.createtype')))));
        return true;
    }

    /**
     * Copy source files
     *
     * @param  string   $source       The source directory
     * @param  string   $destination  The destination directory
     *
     * @return boolean  True on success
     */
    protected function _copySource($source, $destination)
    {
        if (!JFolder::exists($source)) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_FOLDER_NOT_FOUND') . ' ' . $source, 'error');
            return false;
        }

        if (JFolder::exists($destination)) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_DESTINATION_FOLDER_ALREADY_EXISTS') . ' ' . $destination, 'error');
            return false;
        }

        if (!JFolder::copy($source, $destination)) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_COPY_FOLDER_FAILED') . ' ' . $source . ' ' . $destination, 'error');
            return false;
        }

        // retrieve all folder names for destination
        $folders = JFolder::folders($destination, '', true, true, array('.svn', 'CVS'));
        $folders[] = $destination;

        // process files in each folder
        foreach ($folders as $folder) {

            // retrieve all file names in folder
            $files = JFolder::files($folder);

            // process each file
            foreach ($files as $file) {

                // retrieve current file extension
                $fileExtension = JFile::getExt($file);

                // rename files, if needed
                if (strtolower($file) == $this->_replacesingle . '.' . $fileExtension) {
                    $existingName = $this->_replacesingle . '.' . $fileExtension;
                    $newName = $this->_single . '.' . $fileExtension;
                    $this->_renameFile($existingName, $newName, $folder);
                    $this->_changeWords($folder . '/' . $newName);

                } else if (strtolower($file) == $this->_replaceplural . '.' . $fileExtension) {
                    $existingName = $this->_replaceplural . '.' . $fileExtension;
                    $newName = $this->_plural . '.' . $fileExtension;
                    $this->_renameFile($existingName, $newName, $folder);
                    $this->_changeWords($folder . '/' . $newName);

                } else if ($fileExtension == 'ini') {
                    if ($file == substr($file, 0, 10) . $this->_replaceplural . '.ini') {
                        $existingName = substr($file, 0, 10) . $this->_replaceplural . '.ini';
                        $newName = substr($file, 0, 10) . $this->_plural . '.ini';
                        $this->_renameFile($existingName, $newName, $folder);
                        $this->_changeWords($folder . '/' . $newName);
                    }
                    if ($file == substr($file, 0, 10) . $this->_replaceplural . '.sys.ini') {
                        $existingName = substr($file, 0, 10) . $this->_replaceplural . '.sys.ini';
                        $newName = substr($file, 0, 10) . $this->_plural . '.sys.ini';
                        $this->_renameFile($existingName, $newName, $folder);
                        $this->_changeWords($folder . '/' . $newName);
                    }
                } else {
                    $this->_changeWords($folder . '/' . $file);
                }
            }
        }

        // process each folder for renames last
        foreach ($folders as $folder) {

            // rename folders, as needed
            if (basename($folder) == $this->_replacesingle) {
                // see if the parent folders have been renamed
                $parentPath = dirname($folder);
                if (!JFolder::exists(dirname($parentPath))) {
                    $parentPath = str_replace($this->_replacesingle, strtolower($this->_single), $parentPath);
                    $parentPath = str_replace($this->_replaceplural, strtolower($this->_plural), $parentPath);
                }
                // rename folder
                $existingName = $this->_replacesingle;
                $newName = $this->_single;
                $this->_renameFolder($existingName, $newName, $parentPath);

            } else if (basename($folder) == $this->_replaceplural) {
                // see if the parent folders have been renamed
                $parentPath = dirname($folder);
                if (!JFolder::exists(dirname($parentPath))) {
                    $parentPath = str_replace($this->_replacesingle, strtolower($this->_single), $parentPath);
                    $parentPath = str_replace($this->_replaceplural, strtolower($this->_plural), $parentPath);
                }
                // rename folder
                $existingName = $this->_replaceplural;
                $newName = $this->_plural;
                $this->_renameFolder($existingName, $newName, $parentPath);
            }
        }

        return true;
    }

    /**
     * Rename a folder
     *
     * @param  string   $existingName  The old name of the directory
     * @param  string   $newName       The new name of the directory
     * @param  string   $path          The parent directory
     *
     * @return boolean  True on success
     */
    protected function _renameFolder($existingName, $newName, $path)
    {
        $application = JFactory::getApplication();

        if (!JFolder::exists($path)) {
            $application->enqueueMessage(JText::_('PLG_SYSTEM_FOLDER_NOT_FOUND') . ' ' . $path, 'error');
            return false;
        }
        if (!JFolder::exists($path . '/' . $existingName)) {
            $application->enqueueMessage(JText::_('PLG_SYSTEM_FOLDER_NOT_FOUND') . ' ' . $path . $existingName, 'error');
            return false;
        }

        if (!JFolder::move($existingName, $newName, $path)) {
            $application->enqueueMessage(JText::_('PLG_SYSTEM_RENAME_FOLDER_FAILED') . ' ' . $path . $existingName, 'error');
            return false;
        }

        return true;
    }

    /**
     * Rename a file
     *
     * @param  string   $existingName  The old name of the file
     * @param  string   $newName       The new name of the file
     * @param  string   $path          The parent directory
     *
     * @return boolean  True on success
     */
    protected function _renameFile($existingName, $newName, $path)
    {
        if (!JFolder::exists($path)) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_FOLDER_NOT_FOUND') . ' ' . $path, 'error');
            return false;
        }
        if (!JFile::exists($path . '/' . $existingName)) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_FILE_NOT_FOUND') . ' ' . $path . '/' . $existingName, 'error');
            return false;
        }
        if (JFile::exists($path . '/' . $newName)) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_FILE_ALREADY_EXISTS') . ' ' . $path . '/' . $newName, 'error');
            return false;
        }

        if (!JFile::move($existingName, $newName, $path)) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_RENAME_FILE_FAILED') . ' ' . $path . '/' . $existingName, 'error');
            return false;
        }

        return true;
    }

    /**
     * Change words
     *
     * Changes words in file for plural and singular
     *
     * @param   string   $file  The name of a file
     *
     * @return  boolean  True on success
     */
    protected function _changeWords($file)
    {
        if (!JFile::exists($file)) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_CREATE_CHANGE_WORDS_FILE_NOT_FOUND') . ': ' . $file, 'error');
            return false;
        }

        $body = JFile::read($file);

        $body = str_replace($this->_replaceplural, strtolower($this->_plural), $body);
        $body = str_replace(strtoupper($this->_replaceplural), strtoupper($this->_plural), $body);
        $body = str_replace(ucfirst($this->_replaceplural), ucfirst($this->_plural), $body);

        $body = str_replace($this->_replacesingle, strtolower($this->_single), $body);
        $body = str_replace(strtoupper($this->_replacesingle), strtoupper($this->_single), $body);
        $body = str_replace(ucfirst($this->_replacesingle), ucfirst($this->_single), $body);

        return JFile::write($file, $body);
    }
}