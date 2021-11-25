<?php

namespace Zhusaidong\GridExporter;

use Encore\Admin\Extension;
use Encore\Admin\Grid;

class GridExporter extends Extension
{
	public $name = 'gridExporter';
	public $menu = [
		'title' => 'GridExporter',
		'path'  => 'gridExporter',
		'icon'  => 'fa-gears',
	];
}
