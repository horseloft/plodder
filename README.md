# horseloft/plodder

<h4 align="center">PHP PDO Library support for MySQL、SQLServer</h4>

[参考文档](https://github.com/horseloft/plodder/wiki/document)

1. 第一位数字为主版本号，版本变更不向下兼容；用于整体功能升级和框架结构升级
2. 第二位数字为子版本号，版本变更向下兼容；用于新增功能
3. 第三位数字为修订版本号，版本变更向下兼容；用于BUG修复
4. v1版本适用于: PHP >= 7.1
5. v2版本适用于：PHP >= 8.1

# 注意事项

1. 目前仅做了MySQL和SQLServer的PDO封装，对于SQLServer支持sqlsrv和dblib驱动

2. $connection 数组中的driver仅支持赋值为：mysql 或 sqlserver/sqlsrv/dblib

3. 查询操作(select)需要使用first()方法或者all()方法获取结果集，结果集为数组格式

4. 非查询操作(update/delete/insert)需要使用execute()方法发起执行操作，并返回相应的操作结果

5. 写入操作(insert)支持写入一条数据和多条数据

6. 查询操作没有执行first()方法或者all()方法、非查询方法没有执行execute()方法时，不会创建数据库连接也不会对数据库发起请求

# VERSION

## v1.0.0
1. 支持使用 toSql() 和 toCompleteSql() 方法，输出SQL语句
2. 支持继承 DataObject 的类使用原生SQL语句
3. 支持继承 Reservoir 的类使用连贯操作
4. 支持配置文件中使用 options 参数，设置PDO属性
5. 支持对多数据源和读写分离

## v1.0.1
1. 新增options配置项

## v1.0.3
1. 补充类方法的返回值
2. 补充根类的导入方式
3. 禁用预处理语句的模拟功能
4. 新增默认数据库配置项的读取

## v1.0.4
1. 事务功能支持默认连接配置
