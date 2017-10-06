# yii2-webuploader
==========================

使用框架：YII2
特点：支持表单widget 和非表单widget，单图、多图、多字段、多model
使用webuploader插件

更新速度：正式项目中使用，按需求更新（无新需求不更新）


## 安装

推荐使用composer进行安装

```
$ php composer.phar require xing.chen/webuploader dev-master
```

## 使用
在params.php或者params-local.php内增加xingUploader
```php
'xingUploader' => [
        // 访问url 
        'visitDomain' => '如http://xxx.com/upload/或/upload',
        // 上传url
        'uploadUrl' => '/file-upload/xing',
        'config' => [
        // 上传无图片时预览时的默认图片
            'defaultImage' => '/images/icon/upload.jpg',
            'disableGlobalDnd' => true,
            'accept' => [
                'title' => 'Images',
                'extensions' => 'gif,jpg,jpeg,bmp,png',
                'mimeTypes' => 'image/jpg,image/jpeg,image/png,image/gif,image/bmp',
            ],
            'pick' => [
                'multiple' => false,
            ],
        ],
    ],
```

视图文件

单图
```php
<?php 
// ActiveForm
echo $form->field($model, 'thumb')->widget('xing\webuploader\yii2\FileInput'); 

// 非 ActiveForm

?>
<div class="form-group field-store-thumb">
    <label class="control-label" for="store-thumb">商品封面</label>
    <?=\xing\webuploader\yii2\FileInput::widget(['name' => 'thumb','value' => '123.jpg'])?>
</div>
```

多图
```php
<?php 
// ActiveForm
echo $form->field($model, 'photos')->widget('xing\webuploader\yii2\FileInput', [
	'options' => [
		'pick' => [
			'multiple' => true,
		],
	],
]); ?>

// 非ActiveForm

<div class="form-group field-store-photos">
    <label class="control-label" for="store-photos">商品相册</label>
    <?=\xing\webuploader\yii2\FileInput::widget(['name' => 'photos', 'options' => ['formData' => [
        'module' => $model->formName()],
        'pick' => ['multiple' => true]
    ]])?>
</div>
```

### 控制器处理示例
```php
<?php

class FileUploadController extends \yii\rest\Controller
{
    public function actionXing()
    {
        try {
            // 参考或下载我的另一个项目： xing.chen/upload
            $return = \xing\upload\core\UploadFactory::getInstance('yii')->upload('file', Yii::$app->request->post('module'));
            // 返回上传成功
            return [
                'msg' => null,
                'code' => 0,
                'url' => $return['url'],
                'attachment' => $return['saveUrl'],
            ];
        } catch (\Exception $e) {
            # 返回上传失败
            return ['msg' => $e->getMessage(), 'code' => 1];
        }
    }
}
?>
```

## 注意
如果是修改的多图片操作，务必保证 $model->file = 'src1,src2,src3,...'; 或者 $model->file = ['src1', 'src2'. 'src3', ...];
