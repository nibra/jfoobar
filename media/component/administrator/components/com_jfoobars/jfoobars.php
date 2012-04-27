<?php
/**
 * @version   1.0.0
 * @package   com_jfoobars
 * @copyright Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Access check
if (!JFactory::getUser()->authorise('core.manage', 'com_jfoobars')) {
    throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependencies
jimport('joomla.application.component.controller');

$controller = JController::getInstance('JfoobarsAdmin');
$controller->execute(JFactory::getApplication()->input->get('task', '', 'cmd'));
$controller->redirect();
