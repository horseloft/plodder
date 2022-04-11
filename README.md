# horseloft/database

<h4 align="center">PHP PDO Library support for MySQL、SQLServer</h4>

**简单封装了PDO类的操作，仅支持MySQL和SQLServer**

# 文档

[参考文档](https://github.com/horseloft/horseloft-php/wiki/%E6%95%B0%E6%8D%AE%E5%BA%93)

1. 目前仅做了MySQL和SQLServer的PDO封装

2. $connection 数组中的driver仅支持赋值为：mysql 或 sqlserver/sqlsrv/dblib

3. 查询操作(select)需要使用first()方法或者all()方法获取结果集，结果集为数组格式

4. 非查询操作(update/delete/insert)需要使用execute()方法发起执行操作，并返回响应的操作结果

5. 写入操作(insert)支持写入一条数据和多条数据

6. 查询操作没有执行first()方法或者all()方法、非查询方法没有执行execute()方法时，不会创建数据库连接也不会对数据库发起请求

**[v1.0.0]**
1. 添加对SQLServer数据库的支持
2. 相同的数据库配置不再重复创建多个连接资源
3. 重构事务语法
4. 重构PDO原生语句语法

**[v1.0.1]**
1. 新增对于多数据源和读写分离的数据库支持

**[v1.0.2]**
1. 重命名获取SQL语句方法名
2. 运行过程中的异常用HorseloftDatabaseException抛出
3. whereRaw方法新增参数绑定功能

**[v1.0.3]**
1. 优化toCompleteSql()方法
2. 异常处理修复
3. 优化读取数据库配置文件方法

**[v1.0.4]**
1. 新增sqlsrv驱动类型的PDO操作

**[v1.0.5]**
1. 新增一些通用PDO连接属性
2. 优化PDO连接的charset属性

**[v1.0.6]**
1. 新增PDO的sqlsrv扩展属性，用于处理decimals类型数据的异常

**[v1.0.7]**
1. PDO连接配置新增options配置项，用于配置PDO的option参数项
