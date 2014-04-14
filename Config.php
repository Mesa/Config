<?php

namespace Commander\Core;

/**
 * Class Config
 *
 * @package Commander\Core
 */
class Config
{
    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $reference;
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $delimiter
     * @param string $reference
     */
    public function __construct($delimiter = ".", $reference = "%")
    {
        $this->delimiter = $delimiter;
        $this->reference = $reference;
    }

    /**
     * @param      $path
     * @param bool $default
     *
     * @return array|bool
     */
    public function get($path, $default = false)
    {
        $path = explode($this->delimiter, $path);

        $data = $this->data;
        foreach ($path as $step) {
            if (isset($data[$step])) {
                $data = & $data[$step];
            } else {
                return $default;
            }
        }

        /**
         * Copy config data and replace it when needed with newest config Data
         */
        $tmp = $data;
        if (!is_array($tmp)) {
            $this->prepare($tmp);
        } else {
            array_walk_recursive($tmp, [$this, "prepare"]);
        }

        return $tmp;
    }

    /**
     * @param $data
     */
    protected function prepare(&$data)
    {
        if (!is_string($data)) {
            return;
        }

        $refLength = strlen($this->reference);

        if (substr($data, 0, $refLength) == $this->reference
            && substr($data, $refLength * -1) == $this->reference
            && substr_count($data, $this->reference) == 2
        ) {

            $path = substr($data, $refLength, strlen($data) - ($refLength * 2));
            $data = $this->getReference($path);
            return;
        }

        if (preg_match_all('/%(?<path>[\w\.]+)%/', $data, $matches)) {
            foreach ($matches["path"] as $path) {
                if ($this->exist($path)) {
                    $data = str_replace("%" . $path . "%", $this->get($path, $data), $data);
                }
            }
        }
    }

    /**
     * @param $path
     *
     * @return array
     */
    protected function getReference($path)
    {
        $path = explode($this->delimiter, $path);

        $data = $this->data;
        foreach ($path as $step) {
            if (isset($data[$step])) {
                $data = & $data[$step];
            }
        }

        return $data;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function exist($path)
    {

        $path = explode($this->delimiter, $path);

        $data = $this->data;
        foreach ($path as $step) {
            if (!isset($data[$step])) {
                return false;
            }

            $data = & $data[$step];
        }

        return true;
    }

    /**
     * Inset $value into the array and override all existing data. Use NULL to delete
     *
     * @param $path
     * @param $value
     *
     * @return bool
     */
    public function set($path, $value)
    {
        $path = explode($this->delimiter, $path);
        $data = & $this->data;
        foreach ($path as $step) {
            if (!isset($data[$step])) {
                $data[$step] = "";
            }
            $data = & $data[$step];
        }

        $data = $value;

        return true;
    }

    /**
     * @param $fileName
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @return bool
     */
    public function load($fileName)
    {
        if (empty($fileName)) {
            throw new \BadMethodCallException("Config filename was empty");
        }

        if (!file_exists($fileName)) {
            throw new \("Config file not found [$fileName]");
        }

        $newConfig = include $fileName;

        if (!is_array($newConfig)) {
            throw new \UnexpectedValueException($fileName);
        }
        $this->addValues($newConfig);

        return true;
    }

    /**
     * Merge two arrays and create new items when they are missing or overwrite it
     * when the exist.
     *
     * @param array $newValue
     */
    public function addValues(array &$newValue)
    {
        $target = &$this->data;

        foreach ($newValue as $key => $value) {
            if (is_array($value)) {
                if (!isset($target[$key])) {
                    $target[$key] = [];
                }
                $this->addNewValues($value, $target[$key]);
            } elseif (is_int($key)) {
                $target[] = $value;
            } else {
                $target[$key] = $value;
            }
        }
    }
}
