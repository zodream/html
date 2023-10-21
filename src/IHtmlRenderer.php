<?php
declare(strict_types=1);
namespace Zodream\Html;

use Zodream\Infrastructure\Contracts\ArrayAble;

interface IHtmlRenderer {
    public function render(array|ArrayAble $data): string;
    public function renderInput(array|ArrayAble $data): string;
}