<?php

namespace App\Admin\Controllers;

use App\Models\SystemPosts;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemPostsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '公告';
    
    public function upload(Request $request)
    {
        $disk = 'admin';
        $urls = [];
        // \Log::debug('image-url:',[$request->file()]);
        $ext_error = 0;
        $size_error = 0;
        foreach ($request->file() as $file) {
            $fileExtension = $file->getClientOriginalExtension();
            if (!in_array($fileExtension, ['png', 'PNG', 'jpg', 'JPG', 'gif', 'GIF', 'JPEG', 'jpeg'])) {
                $ext_error++;
            }

            $tmpFile = $file->getRealPath();
            if (filesize($tmpFile) >= 5242880) {
                $size_error++;
            }
            // \Log::debug('image-fileExtension:',[$fileExtension]);
            // $urls[] = Storage::url($file->store('admin'));
        }
        if($ext_error > 0 ){
            return [
                "errno" => 1,
                "data"  => '不支持文件的扩展',//$urls,
            ];
        }
        if($ext_error > 0 ){
            return [
                "errno" => 1,
                "data"  => '文件大小超过5M',//$urls,
            ];
        }
        foreach ($request->file() as $file) {
            $fileExtension = $file->getClientOriginalExtension();
            $tmpFile = $file->getRealPath();
            // 5.每天一个文件夹,分开存储, 生成一个随机文件名
            $fileName = '/images/' . date('Y_m_d') . '/' . md5(time()) . mt_rand(0, 9999) . '.' . $fileExtension;
            if (Storage::disk($disk)->put($fileName, file_get_contents($tmpFile))) {
                $data = [
                    'absolute_path' => config('app.url') . 'uploads' . $fileName,
                    'relative_path' => $fileName,
                    // 'realurl'=>Storage::url($path),
                ];
                $urls[] = $data['absolute_path'];
            }
            // $urls[] = Storage::url($file->store('admin'));
        }
        if(count($urls)==0){
            return [
                "errno" => 1,
                "data"  => '没有文件上传',
            ];
        }
        return [
            "errno" => 0,
            "data"  => $urls,
        ];
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SystemPosts());
        $grid->disableCreateButton(false);

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('title', __('Title'));
            });
        });
//        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'))->modal(__('Content'), function ($model) {
            return $model->content;
        });
        $grid->column('lang', __('Lang'));
        $grid->column('type', __('Type'))->using(SystemPosts::TYPE);
        $grid->column('display', __('Display'))->switch();
        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));


        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(SystemPosts::findOrFail($id));

//        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('content', __('Content'));
        $show->field('lang', __('Lang'));
        $show->field('type', __('Type'));
        $show->field('display', __('Display'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SystemPosts());

        $form->text('title', __('Title'));
//        $form->textarea('content', __('Content'));
        $form->editor('content', __('Content'))->rules('required');
        $form->select('lang', __('Lang'))->options(config('system.lang'))->rules('required');

        $form->select('type', __('Type'))->options(SystemPosts::TYPE)->rules('required');

        $form->switch('display', __('Display'))->default(1);

        return $form;
    }
}
