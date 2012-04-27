<?php
/**
 * @version     1.0.0
 * @package     com_jfoobars
 * @copyright   Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Jfoobars Admin Controller
 */
class JfoobarsAdminController extends JController
{
    /**
     * Display a view.
     *
     * @param  boolean     $cachable   If true, the view output will be cached
     * @param  array|bool  $urlparams  An array of safe url parameters and their variable types, for valid values
     *                                 see {@link JFilterInput::clean()}.
     *
     * @return JfoobarsAdminController  This object to support chaining.
     *
     * @since  1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $input = JFactory::getApplication()->input;
        require_once JPATH_COMPONENT . '/helpers/jfoobars.php';

        $view = $input->get('view', 'jfoobars', 'cmd');
        JfoobarsAdminHelper::addSubmenu($view);
        $input->set('view', $view);

        parent::display();

        return $this;
    }
}