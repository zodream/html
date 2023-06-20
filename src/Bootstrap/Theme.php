<?php
declare(strict_types=1);
namespace Zodream\Html\Bootstrap;

use Zodream\Disk\Directory;
use Zodream\Disk\File;
use Zodream\Template\ITheme;

class Theme implements ITheme {

    /**
     * @return Directory
     */
    public function getRoot(): Directory {
        return new Directory(__DIR__);
    }

    /**
     * @param $name
     * @return File
     */
    public function getFile($name): File {
        return $this->getRoot()->file($name);
    }
}