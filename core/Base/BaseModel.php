<?php
namespace Core\Base;

class BaseModel
{

    protected $table       = null;
    protected $table_names = [];
    public function __construct()
    {
        if (self::$table === null) {
            $class_name  = get_class($this);
            self::$table = strtolower($class_name) . 's';
        }
    }
    public static function getColumnNames()
    {
        return Db::getValidColumns();
    }
    public static function create($data)
    {
        return Db::table(self::$table)->insert($data);
    }
    public static function update($data, $id)
    {
        Db::table(self::$table)->update($data, $id);
    }
    public static function delete($id)
    {
        Db::table(self::$table)->delete($id);
    }
    public static function all()
    {
        return Db::table(self::$table)->all();
    }
    public static function find($id, $column = 'id')
    {
        return Db::table(self::$table)->find($id, $column);
    }
}
