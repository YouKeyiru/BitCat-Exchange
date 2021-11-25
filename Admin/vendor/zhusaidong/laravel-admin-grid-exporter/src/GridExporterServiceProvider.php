<?php

namespace Zhusaidong\GridExporter;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Support\ServiceProvider;

class GridExporterServiceProvider extends ServiceProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function boot(GridExporter $extension)
	{
		if(!GridExporter::boot())
		{
			return;
		}
		
		Admin::booting(function()
		{
			Grid::init(function(Grid $grid)
			{
				//全局加载导出
				$grid->exporter(new Exporter());
			});
		});
	}
}