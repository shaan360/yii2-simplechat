<?php
/**
 * @link https://github.com/bubasuma/yii2-simplechat
 * @copyright Copyright (c) 2015 bubasuma
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace bubasuma\simplechat;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ListView;

/**
 * Class ConversationWidget
 * @package bubasuma\simplechat
 *
 * @author Buba Suma <bubasuma@gmail.com>
 * @since 1.0
 */
class ConversationWidget extends ListView
{
    /**
     * The current user
     * @var array
     */
    public $user;

    /**
     * The current conversation
     * @since 2.0
     * @var array
     */
    public $current;

    public $clientOptions = [];

    public $liveOptions = [];

    private $tag;


    public function registerJs()
    {
        $id = $this->options['id'];
        if (!isset($this->clientOptions['selector'])) {
            $class = explode(' ', $this->itemOptions['class']);
            $this->clientOptions['selector'] = '.' . $class[0];
        }
        $options = Json::htmlEncode($this->clientOptions);
        $user = Json::htmlEncode($this->user);
        $current = Json::htmlEncode($this->current);
        $view = $this->getView();
        ConversationAsset::register($view);
        $view->registerJs("jQuery('#$id').yiiSimpleChatConversations($user, $current, $options);");
    }


    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        if (!isset($this->itemOptions['class'])) {
            $this->itemOptions['class'] = 'conversation-item';
        }
        $this->tag = ArrayHelper::remove($this->options, 'tag', 'div');
        echo Html::beginTag($this->tag, $this->options);

    }

    public function run()
    {
        $this->registerJs();
        echo Html::endTag($this->tag);
    }

    public function renderItem($model, $key, $index)
    {
        if ($this->itemView === null) {
            $content = $key;
        } elseif (is_string($this->itemView)) {
            $content = $this->getView()->renderFile($this->itemView, array_merge([
                'model' => $model,
                'key' => $key,
                'index' => $index,
                'user' => $this->user,
                'is_current' => $model['contact_id'] == $this->current['contact']['id'],
                'settings' => $this->clientOptions,
            ], $this->viewParams));
        } else {
            $content = call_user_func($this->itemView, $model, $key, $index, $this);
        }
        $options = $this->itemOptions;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        if ($tag !== false) {
            $options['data-key'] = is_array($key) ? json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string)$key;
            $options['data-contact'] = $model['contact_id'];
            if (isset($this->clientOptions['unreadCssClass'])) {
                if ($model['new_messages'] > 0) {
                    Html::addCssClass($options, $this->clientOptions['unreadCssClass']);
                }
            }
            if (isset($this->clientOptions['currentCssClass'])) {
                if ($model['contact_id'] == $this->current['contact']['id']) {
                    Html::addCssClass($options, $this->clientOptions['currentCssClass']);
                }
            }
            return Html::tag($tag, $content, $options);
        } else {
            return $content;
        }
    }


    public function renderSection($name)
    {
        switch ($name) {
            case '{items}':
                return $this->renderItems();
            default:
                return false;
        }
    }

}