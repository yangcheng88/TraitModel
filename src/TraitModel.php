<?php

namespace Yangcheng88\TraitModel;
use Illuminate\Support\Facades\Redis;

trait TraitModel
{
    private static $_instance;

    private $parameters;

    private $model;

    private $query;
    
    private $data;

    //根据参数获取list
    public function getListByParameters($parameters = [], $page = 0, $rows = 0, $select = ['*'], $orderBy = '', $sort = 'asc')
    {
        $this->buildQuery($parameters);
        if ($page) $this->query->skip(($page - 1) * $rows)->take($rows);
        if ($orderBy) $this->query->orderBy($orderBy, $sort);
        return $this->listHandle($this->query->select($select)->get()->toArray());
    }

    //根据参数获取list & count
    public function getPageByParameters()
    {
        return [
            'list' => call_user_func_array([$this, 'getListByParameters'], func_get_args()),
            'count' => $this->query->skip(0)->count()//skip 0 防止有分页时count错误
        ];
    }

    //根据参数获取单条数据
    public function getFirstByParameters($parameters = [], $select = ['*'], $orderBy = '', $sort = 'asc')
    {
        $this->buildQuery($parameters);
        if ($orderBy) $this->query->orderBy($orderBy, $sort);
        $ret = $this->query->select($select)->first();
        return $ret ? $this->listHandle([$ret->toArray()])[0] : [];
    }

    //根据参数获取查询器
    public function getQueryByParameters($parameters = [])
    {
        return $this->buildQuery($parameters);
    }

    //构建查询器
    private function buildQuery($parameters)
    {
        if ($this->initQuery('this')->parameters = $parameters) {
            $this->supplyConditions();
            $this->query->where($this->parameters);
        }
        return $this->query;
    }

    //处理特殊where语句
    public function supplyConditions()
    {

    }

    //特殊where语句处理 & unset
    private function dischargeConditions($key, $func)
    {
        if (isset($this->parameters[$key]) && !empty($this->parameters[$key])) $func();
        unset($this->parameters[$key]);
    }

    //get数据装饰
    private function listHandle($data)
    {
        foreach ($data as &$row) {
            $row = $this->rowHandle($row);
        }
        return $data;
    }

    //行数据装饰
    private function rowHandle($row)
    {
        return $row;
    }

    /*
    private function fieldFilter($data, $map)
    {
        foreach ($map as $key=>$val) {
            if (isset($data[$key])) {
                $this->model->$val = $data[$key];
            }
        }
    }
    */
    //判断属性存在后设置model属性
    private function set($dataKey, $modelKey = '')
    {
        $modelKey = empty($modelKey) ? $dataKey : $modelKey;
        if (isset($this->data[$dataKey])) {
            $this->model->$modelKey = $this->data[$dataKey];
        }
    }

    //创建数据
    public function create($data)
    {
        $this->data = $data;
        $this->initModel('this')->store()->save();
        $primaryKey = $this->model->primaryKey;
        return $this->createHandle($this->model->$primaryKey);
    }

    //创建数据完成后...
    public function createHandle($id)
    {
        return $id;
    }

    //更新数据
    public function update($data)
    {
        $this->data = $data;
        $this->model = $this->initModel()->find($data[$this->model->primaryKey]);
        if (!$this->model) return false;
        return $this->updateHandle($this->store()->save());
    }

    //更新数据完成后...
    public function updateHandle($ret)
    {
        return $ret;
    }

    //删除数据
    public function delete($id)
    {
        return $this->initModel()->find($id)->delete();
    }

    //根据条件删除数据
    public function deleteByParameters($parameters)
    {
        return $this->buildQuery($parameters)->delete();
    }

    //redis cache set get
    public static function getCacheData($key, $func, $time = 0)
    {
        if ($data = Redis::get($key)) return json_decode($data, true);

        $data = $func();
        $setData = json_encode($data);
        $time > 0 ? Redis::setex($key, $time, $setData) : Redis::set($key, $setData);
        return $data;
    }

    //静态获取Model实例
    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = (new static);
        }
        return self::$_instance;
    }

    private function initModel($return = 'model')
    {
        $this->model = $this->getORM();
        return $return == 'model' ? $this->model : $this;
    }

    private function initQuery($return = 'query')
    {
        $this->query = $this->getORM()->query();
        return $return == 'query' ? $this->query : $this;
    }


    public function __get($propertyName)
    {
        if ($propertyName == 'query') {
            return $this->initQuery();
        }
        if ($propertyName == 'model') {
            return $this->initModel();
        }
    }


}
