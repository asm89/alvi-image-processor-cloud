<?php

namespace Alvi\Bundle\ImageProcessor\ZookeeperBundle;

use \Zookeeper as ExtZookeeper;

/**
 * PHP Zookeeper
 *
 * PHP Version 5.3
 *
 * The PHP License, version 3.01
 *
 * @category  Libraries
 * @package   PHP-Zookeeper
 * @author    Lorenzo Alberton <l.alberton@quipo.it>
 * @copyright 2012 PHP Group
 * @license   http://www.php.net/license The PHP License, version 3.01
 * @link      https://github.com/andreiz/php-zookeeper
 */

/**
 * Derived from the example zookeeper class provided by the library.
 *
 * Example interaction with the PHP Zookeeper extension
 *
 * @category  Libraries
 * @package   PHP-Zookeeper
 * @author    Lorenzo Alberton <l.alberton@quipo.it>
 * @copyright 2012 PHP Group
 * @license   http://www.php.net/license The PHP License, version 3.01
 * @link      https://github.com/andreiz/php-zookeeper
 */
class Zookeeper
{
    /**
     * @var Zookeeper
     */
    private $zookeeper;

    /**
     * Constructor
     *
     * @param string $address CSV list of host:port values (e.g. "host1:2181,host2:2181")
     */
    public function __construct($address)
    {
        $this->zookeeper = new ExtZookeeper($address);
    }

    /**
     * Set a node to a value. If the node doesn't exist yet, it is created.
     * Existing values of the node are overwritten
     *
     * @param string $path  The path to the node
     * @param mixed  $value The new value for the node
     *
     * @return mixed previous value if set, or null
     */
    public function set($path, $value)
    {
        if (!$this->zookeeper->exists($path)) {
            $this->makePath($path);
            $this->makeNode($path, $value);
        } else {
            $this->zookeeper->set($path, $value);
        }
    }

    /**
     * Equivalent of "mkdir -p" on ZooKeeper
     *
     * @param string $path  The path to the node
     * @param string $value The value to assign to each new node along the path
     *
     * @return bool
     */
    public function makePath($path, $value = '')
    {
        $parts = explode('/', $path);
        $parts = array_filter($parts);
        $subpath = '';
        while (count($parts) > 1) {
            $subpath .= '/' . array_shift($parts);
            if (!$this->zookeeper->exists($subpath)) {
                $this->makeNode($subpath, $value);
            }
        }
    }

    /**
     * Create a node on ZooKeeper at the given path
     *
     * @param string $path   The path to the node
     * @param string $value  The value to assign to the new node
     * @param array  $params Optional parameters for the Zookeeper node.
     *                       By default, a public node is created
     *
     * @return string the path to the newly created node or null on failure
     */
    public function makeNode($path, $value, array $params = array())
    {
        if (empty($params)) {
            $params = array(
                array(
                    'perms'  => ExtZookeeper::PERM_ALL,
                    'scheme' => 'world',
                    'id'     => 'anyone',
                )
            );
        }

        return $this->zookeeper->create($path, $value, $params);
    }

    /**
     * Get the value for the node
     *
     * @param string $path the path to the node
     *
     * @return string|null
     */
    public function get($path)
    {
        if (!$this->zookeeper->exists($path)) {
            return null;
        }

        return $this->zookeeper->get($path);
    }

    /**
     * List the children of the given path, i.e. the name of the directories
     * within the current node, if any
     *
     * @param string $path the path to the node
     *
     * @return array the subpaths within the given node
     */
    public function getChildren($path)
    {
        if (strlen($path) > 1 && preg_match('@/$@', $path)) {
            // remove trailing /
            $path = substr($path, 0, -1);
        }

        return $this->zookeeper->getChildren($path);
    }

    /**
     * Get the value of the first child at a given path.
     *
     * @param string $path
     * @param mixed  $or
     *
     * @return mixed Value of first child if successful. $or otherwise
     */
    public function getChildrenOr($path, $or)
    {
        if (!$this->zookeeper->exists($path)) {
            return $or;
        }

        return $this->getChildren($path);
    }

    /**
     * Deletes the node.
     *
     * @param string $path
     *
     * @return boolean
     */
    public function delete($path)
    {
        if (!$this->zookeeper->exists($path)) {
            return false;
        }

        $this->zookeeper->delete($path);

        return true;
    }
}
