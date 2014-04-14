<?php

namespace Commander\Core\test;

use Commander\Core\Config;

require __DIR__ . "/../Config.php";

/**
 * Class ConfigTest
 *
 * @package Commander\Core\test
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testGetAndSet()
    {
        $obj           = new Config();
        $test["value"] = 12345;
        $test["path"]  = "test.value";

        $obj->set($test["path"], $test["value"]);
        $this->assertSame(
            $test["value"],
            $obj->get($test["path"])
        );
    }

    public function testReference()
    {
        $obj = new Config();
        $test["value"] = 12345;
        $test["path1"] = "test.path";
        $test["path2"] = "test.path_2";


        $obj->set($test["path2"], "%" . $test["path1"] . "%");
        $obj->set($test["path1"], $test["value"]);

        $this->assertSame(
            $test["value"],
            $obj->get($test["path2"])
        );
    }

    public function testDefaultValue()
    {
        $obj = new Config();

        $this->assertTrue(
            $obj->get("missing", true)
        );

        $this->assertSame(
            123,
            $obj->get("missing", 123)
        );

    }

    public function testDifferentDelimiter()
    {
        $obj = new Config("::");

        $test = [
            "path" => "test:path",
            "value" => 12345
        ];

        $obj->set($test["path"], $test["value"]);

        $this->assertSame(
            $test["value"],
            $obj->get($test["path"])
        );
    }

    public function testGetArray()
    {
        $obj = new Config();

        $value = ["big" => "data"];
        $path = "test.config";

        $obj->set(
            $path,
            $value
        );

        $this->assertSame(
            $value,
            $obj->get($path)
        );

        $this->assertSame(
            ["config" => $value],
            $obj->get("test")
        );
    }

    public function testReplaceInString()
    {
        $obj = new Config();

        $obj->set("conf.ref", 123);
        $obj->set("my.val", "I can count %conf.ref%");

        $this->assertSame(
            "I can count 123",
            $obj->get("my.val")
        );
    }

    public function testExist()
    {
        $obj = new Config();
        $this->assertFalse(
            $obj->exist("missing.path")
        );

        $obj->set("missing.path", ["1", "2", "3"]);
        $this->assertSame(
            ["1", "2", "3"],
            $obj->get("missing.path")
        );

        $obj->set("missing.path", null);
        $this->assertFalse(
            $obj->exist("missing.path")
        );
    }

    public function loadFromFile()
    {

    }
}
