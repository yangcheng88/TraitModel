#### 安装

*composer require yangcheng88/traitmodel:dev-main*

#### 使用方式
``` php
<?php

use xxModel;
use Yangcheng88\TraitModel\TraitModel;

class TestModel
{
    use TraitModel;

    public function getORM()
    {
        return new xxModel;//ORM Model
    }
}
TestModel::instance()->create();
```


#### 方法demo(以下方法在TestModel中实现)
*保存数据时给数据对象赋值逻辑*
``` php
private function store($data)
{
    $this->model->name = $data['name'];
    $this->model->pid = intval($data['pid']);
    $this->model->route = $data['route'];
    return $this->model;
}
```

*获取数据后对数据列表装饰逻辑*
``` php
private function listHandle($data)
{
    foreach ($data as &$d) {
        $d['route'] = trim($d['route'], '/');
    }
    return $data;
}
```

*获取数据后对数据行装饰逻辑*
``` php
private function rowHandle($data)
{
    $data['test'] = 123;
    return $data;
}
```


*处理特殊查询条件逻辑*
``` php
private function supplyConditions()
{
    $this->dischargeConditions('permissionids', function () {
        $this->query->whereIn('id', $this->parameters['permissionids']);
    });
}
```

*取出参数中条件，并传入闭包函数处理逻辑*
``` php
$this->dischargeConditions('permissionids', function () {
    $this->query->whereIn('id', $this->parameters['permissionids']);
});
```

*数据create后逻辑*
``` php
public function createHandle($id, $data)
{
    return $id;
}

```

*数据update后逻辑*
``` php
public function updateHandle($ret, $data)
{
    return $ret;
}
```

*判断属性存在后设置model属性*
``` php
$this->set('datakey');
```

*判断redis缓存是否存在，不存在写入缓存，返回数据*
``` php
$this->getCacheData('cms_all_routes', function(){
    return $this->getQueryByParameters()->pluck('route')->toArray();
}, 3);
```