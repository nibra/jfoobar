<?php
/**
 * @package     Molajo
 * @subpackage  Helper
 * @copyright   Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('MOLAJO') or die;

/**
 * File Helper
 *
 * @package     Molajo
 * @subpackage  File Helper
 * @since       1.0
 */
class MolajoFileHelper
{
    /**
     * Load a required class
     *
     * @param  string   $file
     * @param  string   $class
     *
     * @throws InvalidArgumentException  if file or class do not exist
     * @return void
     */
    function requireClassFile($file, $class)
    {
        if (substr(basename($file), 0, 4) == 'HOLD') {
            return;
        }
        if (class_exists($class)) {
            return;
        }
        if (!file_exists($file)) {
            throw new InvalidArgumentException(JText::_('MOLAJO_FILE_NOT_FOUND_FOR_CLASS' . ' ' . $file . ' ' . $class));
        }
        JLoader::register($class, $file);

        if (!class_exists($class)) {
            throw new InvalidArgumentException(JText::_('MOLAJO_FILE_NOT_FOUND_FOR_CLASS' . ' ' . $file . ' ' . $class));
        }
    }
}