<?php
/**
 *  DB - A simple database class
 *
 * @author        Author: Vivek Wicky Aswal. (https://twitter.com/#!/VivekWickyAswal)
 * @git         https://github.com/wickyaswal/PHP-MySQL-PDO-Database-Class
 * @version      0.2ab
 *
 */
require 'log.class.php';
class DB
{
    # @object, The PDO object
    private $pdo;

    # @object, PDO语句对象
    private $sQuery;

    # @array,  数据库设置
    private $settings;

    # @bool ,  连接到数据库
    private $bConnected = false;

    # @object,  用于记录异常的对象
    private $log;

    # @array, SQL查询的参数
    private $parameters;

    /**
     *   Default Constructor
     *
     *    1. Instantiate Log class.
     *    2. Connect to database.
     *    3. Creates the parameter array.
     */
    public function __construct()
    {
        $this->log = new Log();
        $this->Connect();
        $this->parameters = [];
    }

    /**
     *    此方法连接数据库。
     *
     *    1. Reads the database settings from a ini file.
     *    2. Puts  the ini content into the settings array.
     *    3. Tries to connect to the database.
     *    4. If connection failed, exception is displayed and a log file gets created.
     */
    private function Connect()
    {
        $this->settings = parse_ini_file('settings.ini.php');
        $host           = getenv('DB_HOST') ?? $this->settings['host'];         // 从环境变量获取主机名（db）
        $dbname         = getenv('DB_DATABASE') ?? $this->settings['dbname'];   // 数据库名（cyxiaowu）
        $user           = getenv('DB_USERNAME') ?? $this->settings['user'];     // 用户名（user）
        $pass           = getenv('DB_PASSWORD') ?? $this->settings['password']; // 密码（password）
        $port           = getenv('DB_PORT') ?: 3306;                            // 端口号
        $dsn            = 'mysql:dbname=' . $dbname . ';host=' . $host . '';
        try {
            # Read settings from INI file, set UTF8
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ]);

            # 我们现在可以记录致命错误的任何异常。
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            # 禁用预准备语句的模拟，而使用真正的预准备语句。
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            # 连接成功，将布尔值设置为真。
            $this->bConnected = true;
        } catch (PDOException $e) {
            # Write into log
            echo $this->ExceptionLog($e->getMessage());
            die();
        }
    }
    /*
     *   如果您想关闭PDO连接，可以使用这个小方法
     *
     */
    public function CloseConnection()
    {
        # Set the PDO object to null to close the connection
        # http://www.php.net/manual/en/pdo.connections.php
        $this->pdo = null;
    }

    /**
     *    每个需要执行SQL查询的方法都使用此方法。
     *
     *    1. 如果没有连接，请连接到数据库.
     *    2. 准备查询.
     *    3. 参数化查询.
     *    4. 执行查询.
     *    5. 关于异常:写异常到日志+ SQL查询.
     *    6. 重新设置参数.
     */
    private function Init($query, $parameters = '')
    {
        # Connect to database
        if (! $this->bConnected) {
            $this->Connect();
        }
        try {
            # 准备查询
            $this->sQuery = $this->pdo->prepare($query);

            # 向参数数组添加参数
            $this->bindMore($parameters);

            # 绑定参数
            if (! empty($this->parameters)) {
                foreach ($this->parameters as $param => $value) {
                    if (is_int($value[1])) {
                        $type = PDO::PARAM_INT;
                    } elseif (is_bool($value[1])) {
                        $type = PDO::PARAM_BOOL;
                    } elseif (is_null($value[1])) {
                        $type = PDO::PARAM_NULL;
                    } else {
                        $type = PDO::PARAM_STR;
                    }
                    // 在将值绑定到列时添加类型
                    $this->sQuery->bindValue($value[0], $value[1], $type);
                }
            }

            # 执行SQL
            $this->sQuery->execute();
        } catch (PDOException $e) {
            # 写入日志并显示异常
            echo $this->ExceptionLog($e->getMessage(), $query);
            die();
        }

        # Reset the parameters
        $this->parameters = [];
    }

    /**
     *    @void
     *
     *    将参数添加到参数数组中
     *    @param string $para
     *    @param string $value
     */
    public function bind($para, $value)
    {
        // $this->parameters[sizeof($this->parameters)] = [':' . $para, $value];
        if (is_int($para)) {
            $this->parameters[sizeof($this->parameters)] = [++$para, $value];
        } else {
            $this->parameters[sizeof($this->parameters)] = [':' . $para, $value];
        }
    }
    /**
     *    @void
     *
     *    向参数数组添加更多参数
     *    @param array $parray
     */
    public function bindMore($parray)
    {
        if (empty($this->parameters) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }
    /**
     *  如果SQL查询包含SELECT或SHOW语句，则返回包含所有结果集行的数组
     *    如果SQL语句是DELETE、INSERT或UPDATE语句，则返回受影响的行数
     *
     *  @param  string $query
     *    @param  array  $params
     *    @param  int    $fetchmode
     *    @return mixed
     */
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $query = trim(str_replace("\r", ' ', $query));

        $this->Init($query, $params);

        $rawStatement = explode(' ', preg_replace("/\s+|\t+|\n+/", ' ', $query));

        # 使用哪个SQL语句
        $statement = strtolower($rawStatement[0]);

        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return null;
        }
    }

    /**
     *  返回最后插入的id.
     *  @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 开始事务
     * @return boolean, true on success or false on failure
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     *  执行事务
     *  @return boolean, true on success or false on failure
     */
    public function executeTransaction()
    {
        return $this->pdo->commit();
    }

    /**
     *  回滚事务
     *  @return boolean, true on success or false on failure
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     *    返回一个数组，该数组表示结果集中的一列
     *
     *    @param  string $query
     *    @param  array  $params
     *    @return array
     */
    public function column($query, $params = null)
    {
        $this->Init($query, $params);
        $Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);

        $column = null;

        foreach ($Columns as $cells) {
            $column[] = $cells[0];
        }

        return $column;
    }
    /**
     *    返回一个数组，该数组表示结果集中的一行
     *
     *    @param  string $query
     *    @param  array  $params
     *       @param  int    $fetchmode
     *    @return array
     */
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $this->Init($query, $params);
        $result = $this->sQuery->fetch($fetchmode);
        $this->sQuery->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued,
        return $result;
    }
    /**
     *    返回单个字段/列的值
     *
     *    @param  string $query
     *    @param  array  $params
     *    @return string
     */
    public function single($query, $params = null)
    {
        $this->Init($query, $params);
        $result = $this->sQuery->fetchColumn();
        $this->sQuery->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued
        return $result;
    }
    /**
     * 写入日志并返回异常
     *
     * @param  string $message
     * @param  string $sql
     * @return string
     */
    private function ExceptionLog($message, $sql = '')
    {
        $exception = 'Unhandled Exception. <br />';
        $exception .= $message;
        $exception .= '<br /> You can find the error back in the log.';

        if (! empty($sql)) {
            # Add the Raw SQL to the Log
            $message .= "\r\nRaw SQL : " . $sql;
        }
        # Write into log
        $this->log->write($message);

        return $exception;
    }
}
