<?php
namespace Zodream\Html\Bootstrap;

use Zodream\Disk\Directory;
use Zodream\Disk\File;
use Zodream\Template\Theme as BaseTheme;

class Theme extends BaseTheme {

    /**
     * @return Directory
     */
    public function getRoot() {
        return new Directory(__DIR__);
    }

    /**
     * @param $name
     * @return File
     */
    public function getFile($name) {
        return $this->getRoot()->file($name);
    }
}