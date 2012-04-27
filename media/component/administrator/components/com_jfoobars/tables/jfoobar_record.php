<?php
/**
 * @version     1.0.0
 * @package     com_jfoobars
 * @copyright   Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Jfoobar table record class
 */
class JfoobarsTableJfoobarRecord extends JObject
{
    public $id;
    public $catid;
    public $state;
    public $publish_up;
    public $version;
    public $metadata;
    public $parameters;
    public $custom_fields;
}
