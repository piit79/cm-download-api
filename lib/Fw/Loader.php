<?php

namespace Fw;

class Loader
{
    /**
     * Root of relative namespace paths
     * @var string
     */
    private $root = "";

    /**
     * List of registered namespaces
     * @var array
     */
    private $namespaces = array();

    /**
     * Loader constructor
     *
     * @param string $root default namespace root on the filesystem
     */
    public function __construct($root)
    {
        $this->root = rtrim($root, DIRECTORY_SEPARATOR);
    }

    /**
     * Register namespaces
     *
     * @param $namespaces array
     */
    public function registerNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace => $namespaceRoot) {
            // make sure the namespace internally ends in a backslash
            if (substr($namespace, -1) != '\\') {
                $namespace .= '\\';
            }
            // if namespace root path is relative prepend the global root
            if (substr($namespaceRoot, 0, 1) != DIRECTORY_SEPARATOR) {
                $namespaceRoot = $this->root . ($namespaceRoot ? DIRECTORY_SEPARATOR : "") . $namespaceRoot;
            }
            $this->namespaces[$namespace] = $namespaceRoot;
        }
        // sort namespaces by key in descending orders
        // this will ensure we're autoloading from more specific namespaces first
        uksort($this->namespaces, function($a, $b) {
            // return reverse comparison
            return self::compareNamespaces($b, $a);
        });
    }

    /**
     * Compare namespaces treating the root namespace "\" as "smallest"
     *
     * @param string $a
     * @param string $b
     * @return int -1 if $a < $b, 0 if $a == $b, 1 if $a > $b
     */
    protected static function compareNamespaces($a, $b)
    {
        if ($a != "\\" && $b != "\\") {
            return strcmp($a, $b);
        }
        if ($a == $b) {
            return 0;
        } elseif ($a == "\\") {
            return -1;
        }
        return 1;
    }

    /**
     * Autoload method
     *
     * @param string $fullClassName fully qualified class name to autoload
     * @return boolean true if the class was loaded
     */
    public function autoload($fullClassName)
    {
        list($classNamespace, $className) = self::splitClassName($fullClassName);
        foreach ($this->namespaces as $namespace => $namespaceRoot) {
            $relativeNamespace = self::getRelativeNamespace($namespace, $classNamespace);
            if ($relativeNamespace !== false) {
                $classPathFull = self::getClassPath($namespaceRoot, $relativeNamespace, $className);
                if (is_file($classPathFull) && include_once $classPathFull) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Split fully qualified class name to class namespace and class name
     *
     * @param string $fullClassName fully qualified class name
     * @return string[2] array($classNamespace, $className)
     */
    protected static function splitClassName($fullClassName)
    {
        $matches = array();
        if (!preg_match('/^(.*\\\\?)([^\\\\]+)$/U', $fullClassName, $matches)) {
            throw new \RuntimeException("Invalid class specification: " . $fullClassName);
        }
        $classNamespace = $matches[1];
        $className = $matches[2];
        // make sure namespace path ends in a backslash
        if (substr($classNamespace, -1) != '\\') {
            $classNamespace .= '\\';
        }
        return array($classNamespace, $className);
    }

    /**
     * Return relative namespace "path" from $namespace1 to $namespace2
     * or false if $namespace2 doesn't contain $namespace1
     *
     * @param string $namespace1
     * @param string $namespace2
     * @return string|false
     */
    protected static function getRelativeNamespace($namespace1, $namespace2)
    {
        if ($namespace1 == $namespace2) {
            return "";
        }
        if ($namespace1 == "\\") {
            return $namespace2;
        }
        if (($namespace2 != "\\" && strpos($namespace2, $namespace1) === 0)
            || $namespace2 == $namespace1) {
            // strip the namespace1 prefix from namespace2
            $relativeNamespace = substr($namespace2, strlen($namespace1));
            return $relativeNamespace;
        }
        return false;
    }

    /**
     * Get full class path
     *
     * @param string $namespaceRoot root of the namespace
     * @param string $relativeNamespace relative namespace from the root
     * @param string $className name of the class
     * @return string full path of the class file
     */
    protected static function getClassPath($namespaceRoot, $relativeNamespace, $className)
    {
        // treat underscores in class names as namespace separators
        $classFilename = str_replace('_', DIRECTORY_SEPARATOR, $className) . ".php";
        $classPathRelative = str_replace("\\", DIRECTORY_SEPARATOR, $relativeNamespace);
        $classPathFull = $namespaceRoot . DIRECTORY_SEPARATOR . $classPathRelative . $classFilename;
        return $classPathFull;
    }

    /**
     * Register the autoload method
     *
     * @param boolean $prepend prepend the autoloader on the queue instead of appending it
     * @return boolean true on success, false on failure
     */
    public function register($prepend = false)
    {
        return spl_autoload_register(array($this, "autoload"), false, $prepend);
    }

}
