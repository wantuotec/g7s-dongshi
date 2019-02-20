<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Initialize the rwdb
 *
 * @category      rwdb
 * @author        Spike
 * @param         array    $params
 * @param         bool     $active_record_override     Determines if active record should be used or not
 */
function &RWDB($params, $active_record_override = null)
{
    static $rwdbs = array();
    static $count = 0;
    static $dbConfig;
    if (!isset($params['cluster'])) {
        show_error('Cluster should be defined');
    }
    
    $cluster = $params['cluster'];
    $farm = isset($params['farm']) ? $params['farm'] :'';
    $mode = isset($params['mode']) && $params['mode'] == 'read' ? 'read' : 'write';
    $key = $cluster .'_'. $farm .'_'. $mode;

    if( isset($rwdbs[$key]) ){
        if (php_sapi_name() == 'cli') {
            // 在命令行模式下，先关闭再重连，防止 MySQL server has gone away（这个意思就是每次都重新连接数据库）
            is_object($rwdbs[$key]) && $rwdbs[$key]->close();
        } else {
            // 如果在一些不合适的情况下调用了close方法，那么conn_id会没有，这个时候需要重新连接数据库
            if (is_resource($rwdbs[$key]->conn_id) OR is_object($rwdbs[$key]->conn_id)) {
                return $rwdbs[$key];
            }
        }
    }
    // if didn't load the config
    if( empty($dbConfig) ){
        // Is the config file in the environment folder?
        if (!defined('ENVIRONMENT') or !file_exists($file_path = APPPATH . 'config/' .ENVIRONMENT . '/rwdb.php')) {
            if (!file_exists($file_path = APPPATH . 'config/rwdb.php')) {
                show_error('The configuration file rwdb.php does not exist.');
            }
        }
        require_once ($file_path);
    }

    if (!empty($farm)) {
        // cluster mode
        $cluster_config = $dbConfig['db_cluster'];
        $phy_config = $dbConfig['db_physical'];
        if(empty($cluster_config[$cluster]))
        {
            show_error('Undefined cluster');
        }
        
        // if defined policy,use it to create farm_id
        if(!empty($cluster_config[$cluster]['farm_policy']) && file_exists($cluster_config[$cluster]['farm_policy'])){
            $farm_id = call_user_func($cluster_config[$cluster]['farm_policy'],$farm);
        }else{
            $farm_id = $farm;
        }
        
          // default farm 0
        if(isset($cluster_config[$cluster]['map'][$farm_id]))
        {
            $phy_shard_id = $cluster_config[$cluster]['map'][$farm_id];
        }
        else 
        {
            $phy_shard_id = $cluster_config[$cluster]['map'][0];
        }
        $db_name = $cluster_config[$cluster]['db_name_prefix'].str_pad($farm_id,3,"0",STR_PAD_LEFT);
        
        if(!isset($phy_config[$phy_shard_id])){
            show_error('Undefined db_physical server in config/rwdb.php:'.$phy_shard_id);
        }
        
        $config = $phy_config[$phy_shard_id];
        
    } else {
        // single mode
        $single_config = $dbConfig['db_singles'];
        if (empty($single_config[$cluster])) {
            show_error('Undefined cluster');
        }
        $db_name = $single_config[$cluster]['database'];
        $phy_config = $dbConfig['db_physical'];
        
        if(!isset($phy_config[$single_config[$cluster]['map']])){
            show_error('Undefined db_physical server in config/rwdb.php:'.$single_config[$cluster]['map']);
        }
        
        $config = $phy_config[$single_config[$cluster]['map']];

    }

    $conn_config = $config[$mode];
    $conn_config['database'] = $db_name;   
     
    // No DB specified yet?  Beat them senseless...
    if (!isset($conn_config['dbdriver']) or $conn_config['dbdriver'] == '') {
        show_error('You have not selected a database type to connect to.');
    }

    // Load the DB classes.  Note: Since the active record class is optional
    // we need to dynamically create a class that extends proper parent class
    // based on whether we're using the active record class or not.
    // Kudos to Paul for discovering this clever use of eval()

    if ($active_record_override !== null) {
        $active_record = $active_record_override;
    }

    require_once (BASEPATH . 'database/DB_driver.php');

    if (!isset($active_record) or $active_record == true) {
        require_once (BASEPATH . 'database/DB_active_rec.php');

        if (!class_exists('CI_DB')) {
            eval('class CI_DB extends CI_DB_active_record { }');
        }
    } else {
        if (!class_exists('CI_DB')) {
            eval('class CI_DB extends CI_DB_driver { }');
        }
    }
    require_once (BASEPATH . 'database/drivers/' . $conn_config['dbdriver'] . '/' .
        $conn_config['dbdriver'] . '_driver.php');

    // Instantiate the DB adapter
    $driver = 'CI_DB_' . $conn_config['dbdriver'] . '_driver';
    $DB = new $driver($conn_config);

    if ($DB->autoinit == true) {
        $DB->initialize();
    }

    if (isset($conn_config['stricton']) && $conn_config['stricton'] == true) {
        $DB->query('SET SESSION sql_mode="STRICT_ALL_TABLES"');
    }
    $rwdbs[$key] = $DB;
    // file_put_contents('e:/112.txt', var_export($DB, TRUE) . "\n");
    return $DB;
}


/* End of file RWDB.php */
/* Location: ./system/database/rwdb.php */
