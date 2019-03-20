# html
html模块组


## Progress 使用指南

### 注意：此方法不适用于并发高场景，目前不支持嵌套

实例
```php

class Progress extends Zodream\Html\Progress {
    
    public function init() {
        $this->data = [$this->options['a'], 2];
        $this->setStart(0);
    }


    public function play($item) {
        // $item = 1
        // $item = 2
    }
}

$progress = new Progess([
    'a' => 1
])
$progress();

```

具体和web 协作显示进度

```php

function getProgress() {
    if (isset($_POST['key'])) {
        return cache($_POST['key']);
    }
    return new Progess([
       'a' => 1
   ]);
}

$progress = getProgress();
json_encode($progress());

```

js 部分

```js
loopStep = function (data) {
    if (data.key) {
        postJson('/async', data, loopStep);
        return;
    }
    // 完成
}
postJson('/async', {}, loopStep);
```

返回值说明
```php
'key' => '缓存key, 有时表示执行未完成，需要传此key继续',
'current' => '当前执行的最后一个',
'next' => '下一个',
'count' => '总个数',
'time' => '本次执行时间',
'spent' => '总执行时间'
```

完成返回
```php
'count' => '总个数',
'spent' => '总执行时间'
```