<?php

namespace xing\webuploader\yii2;

use Yii;
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Json;

class FileInput extends InputWidget
{
    public $options = [];
    private $chooseButtonClass = ['class' => 'btn-default'];
    private $_hashVar;
    private $config;
    private $visitDomain;
    private $uploadUrl;

    public function init ()
    {
        parent::init();

        $this->config = Yii::$app->params['xingUploader']['config'];
        if (empty($this->config)) throw new \Exception('config 为空');
        $this->config = ArrayHelper::merge($this->config, $this->options);

        $this->visitDomain = Yii::$app->params['xingUploader']['visitDomain'] ?? '';
        $this->uploadUrl = Yii::$app->params['xingUploader']['uploadUrl'] ?? '';

        if (empty($this->uploadUrl))
            throw new \Exception("未设置上传url uploadUrl.");

        $id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : ($this->name ?: $this->id);
        $id = preg_replace('/[^\w\d_]/', '_', $id);

        $this->_hashVar = $this->config['modal_id'] = "webupload_config_{$id}";

        $this->config['server'] = $this->uploadUrl;

        empty($this->name) && $this->name = $this->model->formName() . '[' . $this->attribute . ']';
        empty($this->value) && $this->value = $this->model->{$this->attribute} ?? '';

        # 扩展字段
        $this->config['formData']['module'] = $this->config['formData']['module'] ?? ($this->model ? $this->model->formName() : '');

        FileInputAsset::register($this->getView());
    }

    public function run ()
    {
        $config = Json::htmlEncode($this->config);
        $js = <<<JS
            var {$this->_hashVar} = {$config};
            $('#{$this->_hashVar}').webupload_fileinput({$this->_hashVar});
JS;

        $this->getView()->registerJs($js);

        // 单图
        if (empty($this->config['pick']['multiple'])) {
            $html = $this->renderInput();
            $html .= $this->renderImage();
        }
        // 多图
        else {
            $html = $this->renderMultiInput();
            $html .= $this->renderMultiImage();
        }

        echo $html;
    }


    /**
     * @return string
     */
    public function renderInput ()
    {
        $arr = [];
        Html::addCssClass($this->chooseButtonClass, "btn $this->_hashVar");
        $arr[] = $this->hasModel()
            ? Html::activeTextInput($this->model, $this->attribute, ['class' => 'form-control'])
            : Html::textInput($this->name, $this->value, ['class' => 'form-control']);
        $arr[] = Html::tag('span', Html::button('选择图片', $this->chooseButtonClass), ['class' => 'input-group-btn']);

        return Html::tag('div', implode("\n", $arr), ['class' => 'input-group']);
    }

    /**
     * @return string
     */
    public function renderMultiInput ()
    {
        Html::addCssClass($this->chooseButtonClass, "btn $this->_hashVar");
        $arr = [];
        $arr[] = Html::textInput($this->name, null, ['class' => 'form-control', 'readonly' => 'readonly']);
        $arr[] = Html::hiddenInput($this->name, $this->value);
        $arr[] = Html::tag('span', Html::button('选择图片', $this->chooseButtonClass), ['class' => 'input-group-btn']);

        return Html::tag('div', implode("\n", $arr), ['class' => 'input-group']);
    }

    /**
     * @return string
     */
    public function renderImage ()
    {
        $arr = [];
        $arr[] = Html::img($this->getFullUrl($this->value), ['class' => 'img-responsive img-thumbnail cus-img']);
        $arr[] = Html::tag('em', 'x', ['class' => 'close delImage', 'title' => '删除图片']);

        return Html::tag('div', implode("\n", $arr), ['class' => 'input-group', 'style' => 'margin-top:.5em;']);
    }

    /**
     * @return string
     */
    public function renderMultiImage ()
    {
        $srcTmp = $this->value;
        $items = [];
        if ($srcTmp) {
            is_string($srcTmp) && $srcTmp = explode(',', $srcTmp);
            foreach ($srcTmp as $k => $v) {
                $src = $v ? $this->getFullUrl($v) : ',';
                $arr = [];
                $arr[] = Html::img($src, ['class' => 'img-responsive img-thumbnail cus-img']);
                $arr[] = Html::hiddenInput($this->name . "[]", $v);
                $arr[] = Html::tag('em', 'x', ['class' => 'close delMultiImage', 'title' => '删除这张图片']);
                $items[] = Html::tag('div', implode("\n", $arr), ['class' => 'multi-item']);
            }
        } 

        return Html::tag('div', implode("\n", $items), ['class' => 'input-group multi-img-details']);
    }

    private function getFullUrl($url)
    {
        return $url ? $this->visitDomain . $url : $this->config['defaultImage'];
    }
}