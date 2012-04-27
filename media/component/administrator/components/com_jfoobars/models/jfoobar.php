<?php
/**
 * @version   1.0.0
 * @package   com_jfoobars
 * @copyright Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/jfoobars.php';

/**
 * Item Model for an Jfoobar
 *
 * @package    Joomla.Administrator
 * @subpackage com_jfoobars
 * @since      1.6
 */
class JfoobarsAdminModelJfoobar extends JModelAdmin
{
    /**
     * @var        string    The prefix to use with controller messages.
     * @since    1.6
     */
    protected $text_prefix = 'COM_JFOOBARS';

    /**
     * Test whether a record can be deleted.
     *
     * @param  \JfoobarsTableJfoobarRecord  $record  A record object.
     *
     * @return boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since  1.6
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->state != -2) {
            return false;
        }
        return JFactory::getUser()->authorise('core.delete', 'com_jfoobars.jfoobar.' . (int)$record->id);
    }

    /**
     * est whether a record can have its state edited.
     *
     * @param  \JfoobarsTableJfoobarRecord  $record  A record object.
     *
     * @return boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     * @since  1.6
     */
    protected function canEditState($record)
    {
        $user = JFactory::getUser();

        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_jfoobars.jfoobar.' . (int)$record->id);
        } elseif (!empty($record->catid)) {
            return $user->authorise('core.edit.state', 'com_jfoobars.category.' . (int)$record->catid);
        } else {
            return parent::canEditState($record);
        }
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param  \JfoobarsTableJfoobar  $table  A JTable object.
     *
     * @return void
     *
     * @since    1.6
     */
    protected function prepareTable($table)
    {
        // Set the publish date to now
        if ($table->state == 1 && intval($table->publish_up) == 0) {
            $table->publish_up = JFactory::getDate()->toSql();
        }

        // Increment the content version number.
        $table->version++;

        // Reorder the jfoobars within the category so the new jfoobar is first
        if (empty($table->id)) {
            $table->reorder('catid = ' . (int)$table->catid . ' AND state >= 0');
        }
    }

    /**
     * Get a new table object
     *
     * As a default, this method returns an instance of the Jfoobar table.
     *
     * @param  string  $type    The table type to instantiate
     * @param  string  $prefix  A prefix for the table class name. Optional.
     * @param  array   $config  Configuration array for model. Optional.
     *
     * @return JTable  A database table object
     */
    public function getTable($type = 'Jfoobar', $prefix = 'JfoobarsTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Get a single record.
     *
     * @param  integer  $pk  The id of the primary key.
     *
     * @return \JfoobarsTableJfoobar|boolean  Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        /** @var \JObject $item */
        $item = parent::getItem($pk);
        if (is_object($item)) {
            $registry = new JRegistry;
            $registry->loadString($item->metadata);
            $item->metadata = $registry->toArray();

            $registry = new JRegistry;
            $registry->loadString($item->parameters);
            $item->parameters = $registry->toArray();

            $registry = new JRegistry;
            $registry->loadString($item->custom_fields);
            $item->custom_fields = $registry->toArray();
        }
        return $item;
    }

    /**
     * Method to get the record form.
     *
     * @param  array    $data        Data for the form.
     * @param  boolean  $loadData    True if the form is to load its own data (default case), false if not.
     *
     * @return JForm|boolean  A JForm object on success, false on failure
     *
     * @since  1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        /** @var JForm $form */
        $form = $this->loadForm('com_jfoobars.jfoobar', 'jfoobar', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        // Determine correct permissions to check.
        $id = (int)$this->getState('jfoobar.id');
        if (!empty($id)) {
            // Existing record. Can only edit in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit');
            // Existing record. Can only edit own jfoobars in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit.own');

        } else {
            // New record. Can only create in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.create');
        }

        // Modify the form based on Edit State access controls.
        if (!$this->canEditState((object)$data)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an jfoobar you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }
        return $form;
    }

    /**
     * Get the data that should be injected in the form.
     *
     * @return  \JfoobarsTableJfoobarRecord|array  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_jfoobars.edit.jfoobar.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Prime some default values.
            if ($this->getState('jfoobar.id') == 0) {
                $app = JFactory::getApplication();
                $data->set('catid', $app->input->get('catid', $app->getUserState('com_jfoobars.jfoobars.filter.category_id'), 'int'));
            }
        }
        return $data;
    }

    /**
     * Save the form data.
     *
     * @param  array    $data  The form data
     *
     * @return boolean  True on success
     *
     * @since  1.6
     */
    public function save($data)
    {
        if (JFactory::getApplication()->input->get('task', '', 'cmd') == 'save2copy') {
            // Alter the title for save as copy
            list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
            $data['title'] = $title;
            $data['alias'] = $alias;
        }
        return parent::save($data);
    }

    /**
     * Get a set of ordering conditions
     *
     * @param  \JfoobarsTableJfoobarRecord|\JfoobarsTableJfoobar  $table   A record object.
     *
     * @return array     An array of conditions to add to ordering queries
     *
     * @since  1.6
     */
    protected function getReorderConditions($table)
    {
        $condition = array();
        $condition[] = 'catid = ' . (int)$table->catid;
        return $condition;
    }
}