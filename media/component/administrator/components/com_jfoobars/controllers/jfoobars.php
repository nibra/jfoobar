<?php
/**
 * @version   1.0.0
 * @package   com_jfoobars
 * @copyright Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Jfoobars list controller class
 *
 * @package    Joomla
 * @subpackage com_jfoobars
 * @since      1.6
 */
class  JfoobarsAdminControllerJfoobars extends JControllerAdmin
{
    /**
     * Constructor
     *
     * @see    JController
     * @param  array  $config  An optional associative array of configuration settings.
     *
     * @return \JfoobarsAdminControllerJfoobars
     *
     * @since  1.6
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Proxy for getModel
     *
     * @param  string  $name    The name of the model.
     * @param  string  $prefix  The prefix for the PHP class name.
     * @param  array   $config
     *
     * @return JModel
     */
    public function getModel($name = 'Jfoobar', $prefix = 'JfoobarsAdminModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}