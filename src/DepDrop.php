<?php
namespace kilyakus\depdrop;

use kilyakus\widgets\InputWidget;
use kilyakus\widgets\Config;
use kilyakus\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;

class DepDrop extends InputWidget
{
    const TYPE_DEFAULT = 1;
    const TYPE_SELECT2 = 2;

    public $type = self::TYPE_DEFAULT;
    public $select2Options = [];
    public $pluginName = 'depdrop';

    public function run()
    {
        if (empty($this->pluginOptions['url'])) {
            throw new InvalidConfigException("The 'pluginOptions[\"url\"]' property has not been set.");
        }
        if (empty($this->pluginOptions['depends']) || !is_array($this->pluginOptions['depends'])) {
            throw new InvalidConfigException("The 'pluginOptions[\"depends\"]' property must be set and must be an array of dependent dropdown element identifiers.");
        }
        if (empty($this->options['class'])) {
            $this->options['class'] = 'form-control';
        }
        if ($this->type === self::TYPE_SELECT2) {
            Config::checkDependency('select2\Select2', 'yii2-widget-select2', 'for dependent dropdown for Select2');
        }
        if ($this->type !== self::TYPE_SELECT2 && !empty($this->options['placeholder'])) {
            $this->data = ['' => $this->options['placeholder']] + $this->data;
        }
        $this->registerAssets();
    }

    public function registerAssets()
    {
        $view = $this->getView();
        DepDropAsset::register($view)->addLanguage($this->language, 'depdrop_locale_');
        DepDropExtAsset::register($view);
        $this->registerPlugin($this->pluginName);
        if ($this->type === self::TYPE_SELECT2) {
            $loading = ArrayHelper::getValue($this->pluginOptions, 'loadingText', 'Loading ...');
            $this->select2Options['data'] = $this->data;
            $this->select2Options['options'] = $this->options;
            if ($this->hasModel()) {
                $settings = ArrayHelper::merge($this->select2Options, [
                    'model' => $this->model,
                    'attribute' => $this->attribute
                ]);
            } else {
                $settings = ArrayHelper::merge($this->select2Options, [
                    'name' => $this->name,
                    'value' => $this->value
                ]);
            }
            echo Select2::widget($settings);
            $id = $this->options['id'];
            $view->registerJs("initDepdropS2('{$id}','{$loading}');");
        } else {
            echo $this->getInput('dropdownList', true);
        }
    }
}
