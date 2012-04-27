<?php
/**
 * @version     1.0.0
 * @package     com_jfoobars
 * @copyright   Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * List of Jfoobar records.
 *
 * @package    Joomla
 * @subpackage com_jfoobars
 */
class JfoobarsAdminModelJfoobars extends JModelList
{
    /**
     * Constructor
     *
     * @see    \JController
     * @param  array  $config  An optional associative array of configuration settings.
     *
     * @since  1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'catid', 'a.catid', 'category_title',
                'state', 'a.state',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'ordering', 'a.ordering',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
            );
        }
        parent::__construct($config);
    }

    /**
     * Auto-populate the model state.
     *
     * Note: Calling getState in this method will result in recursion.
     *
     * @param  string  $ordering   The ordering column
     * @param  string  $direction  The ordering direction
     *
     * @return void
     *
     * @since  1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Adjust the context to support modal layouts.
        $layout = $layout = JFactory::getApplication()->input->get('layout', '', 'cmd');
        if (!empty($layout)) {
            $this->context .= '.' . $layout;
        }

        $this->setState('filter.search',
            $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search')
        );
        $this->setState('filter.access',
            $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int')
        );
        $this->setState('filter.author_id',
            $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id')
        );
        $this->setState('filter.published',
            $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '')
        );
        $this->setState('filter.category_id',
            $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id')
        );
        parent::populateState('a.title', 'asc');
    }

    /**
     * Get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param  string  $id  A prefix for the store id.
     *
     * @return string  A store id
     *
     * @since  1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.author_id');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return \JDatabaseQuery
     *
     * @since  1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.alias, a.subtitle, ' .
                    'a.snippet, a.fulltext, a.catid, ' .
                    'a.created, a.created_by, a.created_by_alias, ' .
                    'a.modified, a.modified_by, ' .
                    'a.checked_out, a.checked_out_time, ' .
                    'a.state, a.publish_up, a.publish_down, ' .
                    'a.access, a.asset_id, a.version, a.language, a.ordering, ' .
                    'a.metakey, a.metadesc, a.metadata, ' .
                    'a.parameters, a.custom_fields'
            )
        );
        $query->from('#__jfoobars AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the categories.
        $query->select('c.title AS category_title');
        $query->join('LEFT', '#__categories AS c ON c.id = a.catid');

        // Join over the users for the author.
        $query->select('ua.name AS author_name');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Filter by access level.
        $access = (int)$this->getState('filter.access');
        if (!empty($access)) {
            $query->where('a.access = ' . $access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
        }

        // Filter by a single or group of categories.
        $categoryId = $this->getState('filter.category_id');
        if (is_numeric($categoryId)) {
            $query->where('a.catid = ' . (int)$categoryId);
        } else if (is_array($categoryId)) {
            JArrayHelper::toInteger($categoryId);
            $categoryId = implode(',', $categoryId);
            $query->where('a.catid IN (' . $categoryId . ')');
        }

        // Filter by author
        $authorId = $this->getState('filter.author_id');
        if (is_numeric($authorId)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int)$authorId);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } elseif (stripos($search, 'author:') === 0) {
                $search = $db->Quote('%' . $db->escape(substr($search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol == 'a.ordering' || $orderCol == 'category_title') {
            $orderCol = 'category_title ' . $orderDirn . ', a.ordering';
        }
        $query->order($db->escape($orderCol . ' ' . $orderDirn));
        return $query;
    }

    /**
     * Build a list of authors
     *
     * @return \JDatabaseQuery
     *
     * @since  1.6
     */
    public function getAuthors()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text');
        $query->from('#__users AS u');
        $query->join('INNER', '#__jfoobars AS c ON c.created_by = u.id');
        $query->group('u.id');
        $query->order('u.name');

        // Setup the query
        $db->setQuery((string)$query);

        // Return the result
        return $db->loadObjectList();
    }

    /**
     * Get a list of jfoobars.
     *
     * Overridden to add a check for access levels.
     *
     * @return array|boolean  An array of data items on success, false on failure.
     * @since  1.6.1
     */
    public function getItems()
    {
        $items = parent::getItems();
        if (JFactory::getApplication()->isSite()) {
            $groups = JFactory::getUser()->getAuthorisedViewLevels();

            for ($x = 0, $count = count($items); $x < $count; $x++) {
                //Check the access level. Remove jfoobars the user shouldn't see
                if (!in_array($items[$x]->access, $groups)) {
                    unset($items[$x]);
                }
            }
        }
        return $items;
    }
}
