<?php
/**
 * @version   1.0.0
 * @package   com_jfoobars
 * @copyright Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * @package    Joomla.Administrator
 * @subpackage com_jfoobars
 * @since      1.6
 */
class JfoobarsAdminControllerJfoobar extends JControllerForm
{
    /**
     * Class constructor
     *
     * @param  array  $config  A named array of configuration variables.
     *
     * @return \JfoobarsAdminControllerJfoobar
     *
     * @since  1.6
     */
    function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Check if the user can add a new record.
     *
     * @param  array    $data  An array of input data.
     *
     * @return boolean  True, if allowed
     *
     * @since    1.6
     */
    protected function allowAdd($data = array())
    {
        $input = JFactory::getApplication()->input;

        // Initialise variables.
        $user = JFactory::getUser();
        $categoryId = JArrayHelper::getValue($data, 'catid', $input->get('filter_category_id', 0, 'int'), 'int');
        $allow = null;

        if (!empty($categoryId)) {
            // If the category has been passed in the data or URL check it.
            $allow = $user->authorise('core.create', 'com_jfoobars.category.' . $categoryId);
        }

        if (is_null($allow)) {
            // In the absence of better information, revert to the component permissions.
            return parent::allowAdd();
        } else {
            return $allow;
        }
    }

    /**
     * Check if the user can edit an existing record.
     *
     * @param  array    $data  An array of input data.
     * @param  string   $key   The name of the key for the primary key.
     *
     * @return boolean  True, if allowed
     *
     * @since  1.6
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Initialise variables.
        $recordId = (int)isset($data[$key]) ? $data[$key] : 0;
        $user = JFactory::getUser();
        $userId = $user->get('id');

        // Check general edit permission first.
        if ($user->authorise('core.edit', 'com_jfoobars.jfoobar.' . $recordId)) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($user->authorise('core.edit.own', 'com_jfoobars.jfoobar.' . $recordId)) {
            // Now test the owner is the user.
            $ownerId = (int)isset($data['created_by']) ? $data['created_by'] : 0;
            if (empty($ownerId) && $recordId) {
                // Need to do a lookup from the model.
                $record = $this->getModel()->getItem($recordId);
                if (empty($record)) {
                    return false;
                }
                $ownerId = $record->created_by;
            }

            // If the owner matches the current user then do the test.
            if ($ownerId == $userId) {
                return true;
            }
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
    }

    /**
     * Run batch operations
     *
     * @param  JModelAdmin  $model
     *
     * @return boolean      True if successful, false otherwise and internal error is set.
     *
     * @since  1.6
     */
    public function batch($model)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Set the model
        if (empty($model)) {
            $model = $this->getModel('Jfoobar', '', array());
        }

        // Preset the redirect
        $this->setRedirect(JRoute::_('index.php?option=com_jfoobars&view=jfoobars' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }
}
