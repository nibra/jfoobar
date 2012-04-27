<?php
/**
 * @version   1.0.0
 * @package   com_jfoobars
 * @copyright Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Jfoobars Helper Class
 */
class JfoobarsAdminHelper
{
    const FILTER_NONE       = 'NONE';
    const FILTER_NO_HTML    = 'NH';
    const FILTER_BLACK_LIST = 'BL';
    const FILTER_WHITE_LIST = 'WL';

    /**
     * Add a submenu
     *
     * Configure the link bar
     *
     * @param  string  $vName
     *
     * @return void
     */
    public static function addSubmenu($vName = 'jfoobars')
    {
        JSubMenuHelper::addEntry(
            JText::_('COM_JFOOBARS_TITLE_JFOOBARS'),
            'index.php?option=com_jfoobars&view=jfoobars',
            $vName
        );
        JSubMenuHelper::addEntry(
            JText::_('COM_JFOOBARS_SUBMENU_CATEGORIES'),
            'index.php?option=com_categories&extension=com_jfoobars',
            'categories');
    }

    /**
     * Get available actions
     *
     * Gets a list of the actions that can be performed.
     *
     * @return JObject  The permissions of the user indexed by action
     *
     * @since    1.0
     */
    public static function getActions()
    {
        $user = JFactory::getUser();
        $result = new JObject;

        $assetName = 'com_jfoobars';

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }
        return $result;
    }


    /**
     * Filter text
     *
     * Applies the content tag filters to arbitrary text as per settings for current user group
     *
     * @param  string  $text  The text string to filter
     *
     * @return string  The filtered string
     */
    public static function filterText($text)
    {
        // Filter settings
        jimport('joomla.application.component.helper');
        $config = JComponentHelper::getParams('com_jfoobars');
        $user = JFactory::getUser();
        $userGroups = JAccess::getGroupsByUser($user->get('id'));

        $filters = $config->get('filters');

        $blackListTags = array();
        $blackListAttributes = array();

        $whiteListTags = array();
        $whiteListAttributes = array();

        $noHtml = false;
        $whiteList = false;
        $blackList = false;
        $unfiltered = false;

        // Cycle through each of the user groups the user is in.
        // Remember they are include in the Public group as well.
        foreach ($userGroups as $groupId) {
            // May have added a group by not saved the filters.
            if (!isset($filters->$groupId)) {
                continue;
            }

            // Each group the user is in could have different filtering properties.
            $filterData = $filters->$groupId;
            $filterType = strtoupper($filterData->filter_type);

            if ($filterType == self::FILTER_NO_HTML) {
                // Maximum HTML filtering.
                $noHtml = true;
            } else if ($filterType == self::FILTER_NONE) {
                // No HTML filtering.
                $unfiltered = true;
            } else {
                // Black or white list.
                // Preprocess the tags and attributes.
                $tags = explode(',', $filterData->filter_tags);
                $attributes = explode(',', $filterData->filter_attributes);
                $tempTags = array();
                $tempAttributes = array();

                foreach ($tags AS $tag) {
                    $tag = trim($tag);
                    if ($tag) {
                        $tempTags[] = $tag;
                    }
                }

                foreach ($attributes AS $attribute) {
                    $attribute = trim($attribute);
                    if ($attribute) {
                        $tempAttributes[] = $attribute;
                    }
                }

                // Collect the black or white list tags and attributes.
                // Each list is cummulative.
                if ($filterType == self::FILTER_BLACK_LIST) {
                    $blackList = true;
                    $blackListTags = array_merge($blackListTags, $tempTags);
                    $blackListAttributes = array_merge($blackListAttributes, $tempAttributes);
                }
                else if ($filterType == self::FILTER_WHITE_LIST) {
                    $whiteList = true;
                    $whiteListTags = array_merge($whiteListTags, $tempTags);
                    $whiteListAttributes = array_merge($whiteListAttributes, $tempAttributes);
                }
            }
        }

        // Remove duplicates before processing (because the black list uses both sets of arrays).
        $blackListTags = array_unique($blackListTags);
        $blackListAttributes = array_unique($blackListAttributes);
        $whiteListTags = array_unique($whiteListTags);
        $whiteListAttributes = array_unique($whiteListAttributes);

        // Unfiltered assumes first priority.
        if (!$unfiltered) {
            // Black lists take second precedence.
            if ($blackList) {
                // Remove the white-listed attributes from the black-list.
                $filter = JFilterInput::getInstance(
                    array_diff($blackListTags, $whiteListTags), // blacklisted tags
                    array_diff($blackListAttributes, $whiteListAttributes), // blacklisted attributes
                    1, // blacklist tags
                    1 // blacklist attributes
                );
            } else if ($whiteList) {
                // White lists take third precedence.
                $filter = JFilterInput::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0); // turn off xss auto clean
            } else {
                // No HTML takes last place.
                $filter = JFilterInput::getInstance();
            }
            $text = $filter->clean($text, 'html');
        }
        return $text;
    }
}
