<?php

include dirname(__FILE__) . '/../models/project.php';

class Query_builderController extends GeneralWrapper {

    protected $view_class = "view_query_builder";

    public function pre_filter(&$methodName = null) {
        parent::pre_filter($methodName);

        $this->view->addInternalJs("jquery-1.7.1.min.js");
        $this->view->addInternalJs("jquery-ui-1.8.17.custom.min.js");
        $this->view->addInternalJs("utility.js");


        $this->view->addInternalCss("ui-lightness/jquery-ui-1.8.17.custom.css");
        header('Content-Type:text/html; charset=utf-8');
    }

    protected $pdo_db = null;

    protected function openPDOdb($pName = '') {
        $this->openProjectDb($pName);

        if ($this->project_db !== null) {
            $this->project_db->close();

            $dsn = "{$this->project_db->engine}:host={$this->project_db->host};port={$this->project_db->port};charset=UTF8";


            $initPDO = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");

            try {
                $this->pdo_db = new PDO($dsn, $this->project_db->user, $this->project_db->password, $initPDO);
            } catch (PDOException $exc) {
                MLog::e($exc->getTraceAsString());
                $this->pdo_db = null;
            }
        }
    }

    public function index() {
        if (!defined('__LOCAL_DB_FOLDER__')) {
            throw new Exception("__LOCAL_DB_FOLDER__ is not defined");
        }

        if (!is_dir(__LOCAL_DB_FOLDER__)) {
            if (!mkdir(__LOCAL_DB_FOLDER__)) {
                throw new Exception("can not to mkdir for " . __LOCAL_DB_FOLDER__);
            }
        }


        $ldb = new FileDB(__LOCAL_DB_FOLDER__ . '/index.db');

        $this->set('projects', $ldb->fetchAll());
        $ldb->close();

        $this->view->project = new Project();
        $this->view->project_name = "";
        $this->fetchProject('load');


        if ($this->project_db_file_path !== null) {

            $this->view->project = new Project($this->project_db_file_path);
            $this->view->project_name = $this->project_name;
            $this->view->project->close();
        }
    }

    public function save_project() {
        $ldb = new FileDB(__LOCAL_DB_FOLDER__ . '/index.db');


        if (empty($_POST['project'])) {
            $proName = uniqid('tmp_');
            $project_index = false;
        } else {
            $proName = $_POST['project'];
            $project_index = $ldb->fetch($proName);
        }

        if ($project_index === false) {
            $project_index = uniqid("proj_");
        }

        $projFileName = __LOCAL_DB_FOLDER__ . '/' . $project_index . '.db';

        $proDB = new Project($projFileName, "mysql", $_POST['host'], $_POST['port'], $_POST['user'], $_POST['password']);


        $ldb->save($proName, $project_index);


        $ldb->close();
        $proDB->close();
        $_SESSION['project_index'] = $project_index;

        $this->redirect($this->view->popUrl("query_builder/select_tables"));
    }

    public function select_tables() {

        $this->openPDOdb();


        if ($this->pdo_db === null) {
            if ($this->project_name !== null) {
                $this->redirect($this->view->popUrl('query_builder/index?load=') . htmlspecialchars($this->project_name));
            } else {
                $this->redirect($this->view->popUrl('query_builder/'));
            }
        }

        $dbs = array();

        $dbsUniqeMap = array();

        $uniqueDbsMap = array();

        foreach ($this->pdo_db->query("show databases") as $row) {
            $dbs[] = $row[0];

            $uid = uniqid('db_');

            $dbsUniqeMap[$row[0]] = $uid;
            $uniqueDbsMap[$uid] = $row[0];
        }

        $this->view->databases = json_encode($dbs);

        $this->view->uidDbsMap = json_encode($uniqueDbsMap);
        $this->view->DbsUidMap = json_encode($dbsUniqeMap);


        $this->view->addInternalJs("items_table_pickup.js");

        unset($this->pdo_db);
    }

    public function removeProfile() {


        $this->fetchProject('del');



        if (!empty($_GET['del'])) {

            $dn = $_GET['del'];
            $ldb = new FileDB(__LOCAL_DB_FOLDER__ . '/index.db');

            $pn = $ldb->fetch($dn);

            if ($pn !== false) {

                $projFileName = __LOCAL_DB_FOLDER__ . '/' . $pn . '.db';

                unlink($projFileName);

                $ldb->delete($dn);
            }

            $ldb->close();
        }

        $this->redirect($this->view->popUrl('query_builder/'));
    }

    public function list_tables() {

        $this->setLayout("ajax.phtml");
        $tables = array();

        $this->openPDOdb();

        if ($this->pdo_db !== null) {

            $this->pdo_db->exec("use " . $_GET['db']);

            foreach ($this->pdo_db->query("show tables") as $row) {
                $tables[] = $row[0];
            }

            unset($this->pdo_db);
           
            $this->set("ajax_output", json_encode($tables));
        }
    }

    public function prepare() {
        if (empty($_GET['tablesInfo'])) {
            throw new Exception("tablesInfo is required");
        }

        $this->view->addInternalJs("logic_edit.js");
        $this->view->addInternalJs("items_table_pickup.js");


        $this->view->addInternalCss("logic_edit.css");

        $tableInfo = json_decode($_GET['tablesInfo'], true);

        if ($tableInfo === NULL) {


            throw new Exception("[{$_GET['tablesInfo']}] is not a json string");
        }

        $db_tables = array();
        $this->openPDOdb();

        if ($this->pdo_db === null) {
            throw new Exception("Open db fail");
        }


        $savedHeader = "";

        foreach ($tableInfo as $db => $tables) {



            $this->pdo_db->exec("use " . $db);

            $tabs = array();

            foreach ($tables as $table) {
                $savedHeader.="__" . $db . "-" . $table;
                $oneTab = array();
                foreach ($this->pdo_db->query("desc " . $table) as $row) {
                    $tdetail = array();
                    $tdetail['name'] = $row['Field'];
                    $tdetail['type'] = $row['Type'];
                    $tdetail['allow_null'] = $row['Null'] === 'NO' ? false : true;
                    $tdetail['key'] = $row['Key'];
                    $tdetail['default'] = $row['Default'];
                    $tdetail['extra'] = $row['Extra'];

                    $oneTab[] = $tdetail;
                }
                $tabs[$table] = $oneTab;
            }
            $db_tables[$db] = $tabs;
        }

        $this->view->db_table_json_info = json_encode($db_tables);
        $this->view->db_table_info = $db_tables;
        $this->view->saved_header = $savedHeader;

        $_SESSION['db_tables'] = serialize($db_tables);
        
    }

    public function create_pdo_connect_code() {
        $this->setLayout("ajax.phtml");

        $this->openProjectDb();

        if ($this->project_db !== null) {
            $this->project_db->close();

            $this->set('engine', $this->project_db->engine);
            $this->set('host', $this->project_db->host);
            $this->set('port', $this->project_db->port);
            $this->set('user', $this->project_db->user);
            $this->set('password', $this->project_db->password);
            if (!empty($_POST['db_name'])) {
                $this->set('dbname', $_POST['db_name']);
            }
        } else {
            
        }
    }

    public function create_definitions() {
        $this->setLayout("ajax.phtml");

       

        $this->openPDOdb();

        if ($this->pdo_db === null) {
            throw new Exception("Open db fail");
        }

        $table = $_POST['create_definitions_table_name'];
        $id = $_POST['create_definitions_id_column'];
        $name = $_POST['create_definitions_text_column'];

        try {
            $selectColumns = " SELECT ";
            $selectColumns .="{$id} id, {$name} name";
            $queryBase = " FROM ";
            $queryBase.="{$table} order by {$id}";


            $runQuery = $selectColumns . $queryBase;

            //echo $runQuery."\n";

            $stmt = $this->pdo_db->prepare($runQuery);
            $stmt_rv = $stmt->execute();

            if ($stmt_rv) {
                $this->set('definition', $stmt->fetchAll());
            } else {
                MLog::e("some thing wrong");
                $this->set('definition', null);
            }
        } catch (PDOException $x) {
            $errorMessage = "database error: " . $x->getMessage();
            MLog::e($errorMessage);
            throw $x;
        }

        $header = $_POST['create_definitions_header'];
        $tailer = $_POST['create_definitions_tailer'];
        $this->set('header', $header);
        $this->set('tailer', $tailer);

        $this->set('class_name', $_POST['create_definitions_class_name']);
        $this->set('map_name', $_POST['create_definitions_map_name']);

        $this->saveGlobalSetting();
        $this->saveSpecialSetting('definition', 'general', array('header' => $header, 'tailer' => $tailer));
    }

    public function create_class() {
        $this->setLayout("ajax.phtml");




        $this->view->class_name = $_POST['class_name'];

        $this->view->table_varialbe_map = json_decode($_POST['tab_var_map'], true);

        $this->view->db_table_info = unserialize($_SESSION['db_tables']);

        
        $this->saveGlobalSetting();
    }

    public function create_query() {
        $this->setLayout("ajax.phtml");

        $columns = json_decode($_POST['column_data'], true);

        $this->set('custom_header_code', $_POST['setting_custom_header_code']);
        $this->set('custom_tailer_code', $_POST['setting_custom_tailer_code']);

        $tableInfo = json_decode($_POST['table_info'], true);
        $lineIdTableArray = json_decode($_POST['line_id_table_array'], true);
        $uidColumnMap = json_decode($_POST['uid_column_map'], true);
        $lineIdAliasMap = json_decode($_POST['line_id_alias_map'], true);
        $lineIdLogicMap = json_decode($_POST['line_id_logic_map'], true);
        $table_varialbe_map = json_decode($_POST['tab_var_map'], true);

        if (!empty($_POST['query_return_first_row'])) {
            $this->set('query_return_first_row', true);
        } else {
            $this->set('query_return_first_row', false);
        }

        $oderBy = json_decode($_POST['order_by_columns'], true);


        $this->set('function_name', $_POST['query_name'], true);
        $this->set('table_size', count($lineIdAliasMap));

        $tableVariableMap = array();
        foreach ($table_varialbe_map as $one) {
            $tableVariableMap["{$one['db']}.{$one['table']}"] = $one['varialbe'];
        }

        $this->set('tname_veriable_map', $tableVariableMap);

        $lineIdTableFullNameMap = array();
        $fromTables = array();
        $variables = array();
        foreach ($lineIdTableArray as $one) {
            $lineId = $one['line_id'];
            $lineIdTableFullNameMap[$lineId] = $one['table_full_name'];
            $oneFromTable = array();
            $oneFromTable['line_id'] = $lineId;
            $oneFromTable['table_full_name'] = $one['table_full_name'];
            $oneFromTable['table_alias'] = $lineIdAliasMap[$lineId];
            $oneFromTable['table_variable'] = '$this->' . $tableVariableMap[$oneFromTable['table_full_name']];

            if (!empty($lineIdLogicMap[$lineId])) {
                $logic = $lineIdLogicMap[$lineId];

                $logicStrs = $this->logic2String($logic['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
                $oneFromTable['logic_strings'] = $logicStrs;
                $oneFromTable['join_type'] = $logic['join_type'];
               
            }

            $fromTables[] = $oneFromTable;
        }

        $this->set('frome_tables', $fromTables);


        $cc = array();
        foreach ($columns as $one) {
            $id = $one['id'];
            $column = $uidColumnMap[$id];
            $tableAlias = $lineIdAliasMap[$column['line_uid']];
            $tableFullName = $lineIdTableFullNameMap[$column['line_uid']];
            $tableVariable = $tableVariableMap[$tableFullName];
            $cc[] = array(
                'column_name' => $column['name'],
                'alias_name' => $one['alias'],
                'table_alias' => $tableAlias,
                'table_varialbe' => $tableVariable);
        }

        $this->set('columns', $cc);


        $whereCluasStr = "";

        if (!empty($lineIdLogicMap['query_where_claus_id'])) {
            $whereCluasStr = $this->logic2String($lineIdLogicMap['query_where_claus_id']['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
        }

        $this->set('where_claus', $whereCluasStr);

        if (isset($one)) {
            unset($one);
        }
        foreach ($oderBy as &$one) {
            
            if ($one['type'] === 'variable') {
                $variables[$one['variable']] = array('func_param' => true, 'bind_type' => '');
                $descVairalbeName = 'ordery_by_' . $one['variable'] . '_is_desc';
                $one['desc_variable'] = $descVairalbeName;
                $variables[$descVairalbeName] = array('func_param' => true, 'bind_type' => '');
            } else {
                $column = $uidColumnMap[$one['column_id']];
                $tableFullName = $lineIdTableFullNameMap[$column['line_uid']];
                $tableVariable = $tableVariableMap[$tableFullName];
                $tableAlias = $lineIdAliasMap[$column['line_uid']];
                $one['column_name'] = $column['name'];
                $one['table_alias'] = $tableAlias;
                $one['table_varialbe'] = $tableVariable;
               
            }
        }

        $this->set('order_by', $oderBy);

        if (!empty($_POST['query_limit_row_number'])) {
            $num = intval($_POST['query_limit_row_number']);
            $from = $_POST['query_limit_from'] === '' ? 0 : intval($_POST['query_limit_from']);
            if ($num > 0 && $from >= 0) {
                $this->set('limit', array('from' => $from, 'num' => $num));
            }
        }


        $this->set('variables', $variables);
        $this->set('pagination_function_enable', !empty($_POST['query_add_page_function']));

        

        $this->saveGlobalSetting();
    }

    public function create_update() {
        $this->setLayout("ajax.phtml");

        $columns = json_decode($_POST['column_data'], true);

        $this->set('custom_header_code', $_POST['setting_custom_header_code']);
        $this->set('custom_tailer_code', $_POST['setting_custom_tailer_code']);

        $lineIdTableArray = json_decode($_POST['line_id_table_array'], true);
        $uidColumnMap = json_decode($_POST['uid_column_map'], true);
        $lineIdAliasMap = json_decode($_POST['line_id_alias_map'], true);
        $lineIdLogicMap = json_decode($_POST['line_id_logic_map'], true);
        $table_varialbe_map = json_decode($_POST['tab_var_map'], true);




        $this->set('function_name', $_POST['update_name'], true);
        $this->set('table_size', count($lineIdAliasMap));

        $tableVariableMap = array();
        foreach ($table_varialbe_map as $one) {
            $tableVariableMap["{$one['db']}.{$one['table']}"] = $one['varialbe'];
        }

        $this->set('tname_veriable_map', $tableVariableMap);

        $lineIdTableFullNameMap = array();
        $fromTables = array();
        $variables = array();
        foreach ($lineIdTableArray as $one) {
            $lineId = $one['line_id'];
            $lineIdTableFullNameMap[$lineId] = $one['table_full_name'];
            $oneFromTable = array();
            $oneFromTable['line_id'] = $lineId;
            $oneFromTable['table_full_name'] = $one['table_full_name'];
            $oneFromTable['table_alias'] = $lineIdAliasMap[$lineId];
            $oneFromTable['table_variable'] = '$this->' . $tableVariableMap[$oneFromTable['table_full_name']];

            if (!empty($lineIdLogicMap[$lineId])) {
                $logic = $lineIdLogicMap[$lineId];

                $logicStrs = $this->logic2String($logic['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
                $oneFromTable['logic_strings'] = $logicStrs;
                $oneFromTable['join_type'] = $logic['join_type'];
            }

            $fromTables[] = $oneFromTable;
        }

        $this->set('frome_tables', $fromTables);

        $whereCluasStr = "";

        if (!empty($lineIdLogicMap['query_where_claus_id'])) {
            $whereCluasStr = $this->logic2String($lineIdLogicMap['query_where_claus_id']['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
        }

        $this->set('where_claus', $whereCluasStr);


        $cc = array();
        foreach ($columns as $id => $detail) {

            $column = $uidColumnMap[$id];
            $tableAlias = $lineIdAliasMap[$column['line_uid']];
            $tableFullName = $lineIdTableFullNameMap[$column['line_uid']];
            $tableVariable = $tableVariableMap[$tableFullName];
            $cc[] = array(
                'column_name' => $column['name'],
                'value' => $detail['value'],
                'type' => $detail['type'],
                'table_alias' => $tableAlias,
                'bind_type' => $detail['data_type'],
                'table_varialbe' => $tableVariable);

            if ($detail['type'] === 'variable') {

                $variables[$detail['value']] = array('func_param' => true, 'bind_var' => true, 'bind_type' => $detail['data_type'], "is_column" => true);
            }
        }

        $this->set('columns', $cc);



        $this->set('variables', $variables);

        $this->set('group_update', !empty($_POST['group_update']));
        $this->set('auto_compatiable', !empty($_POST['auto_compatiable']));
        $this->set('around_transaction', !empty($_POST['around_transaction']));


        
        $this->saveGlobalSetting();
    }

    public function create_delete() {
        $this->setLayout("ajax.phtml");

        $this->set('custom_header_code', $_POST['setting_custom_header_code']);
        $this->set('custom_tailer_code', $_POST['setting_custom_tailer_code']);


        $lineIdTableArray = json_decode($_POST['line_id_table_array'], true);
        $uidColumnMap = json_decode($_POST['uid_column_map'], true);
        $lineIdAliasMap = json_decode($_POST['line_id_alias_map'], true);
        $lineIdLogicMap = json_decode($_POST['line_id_logic_map'], true);
        $table_varialbe_map = json_decode($_POST['tab_var_map'], true);




        $this->set('function_name', $_POST['delete_name'], true);
        $this->set('table_size', count($lineIdAliasMap));

        $tableVariableMap = array();
        foreach ($table_varialbe_map as $one) {
            $tableVariableMap["{$one['db']}.{$one['table']}"] = $one['varialbe'];
        }

        $this->set('tname_veriable_map', $tableVariableMap);

        $lineIdTableFullNameMap = array();
        $fromTables = array();
        $variables = array();
        foreach ($lineIdTableArray as $one) {
            $lineId = $one['line_id'];
            $lineIdTableFullNameMap[$lineId] = $one['table_full_name'];
            $oneFromTable = array();
            $oneFromTable['line_id'] = $lineId;
            $oneFromTable['table_full_name'] = $one['table_full_name'];
            $oneFromTable['table_alias'] = $lineIdAliasMap[$lineId];
            $oneFromTable['table_variable'] = '$this->' . $tableVariableMap[$oneFromTable['table_full_name']];

            if (!empty($lineIdLogicMap[$lineId])) {
                $logic = $lineIdLogicMap[$lineId];

                $logicStrs = $this->logic2String($logic['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
                $oneFromTable['logic_strings'] = $logicStrs;
                $oneFromTable['join_type'] = $logic['join_type'];
            }

            $fromTables[] = $oneFromTable;
        }

        $this->set('frome_tables', $fromTables);

        $whereCluasStr = "";

        if (!empty($lineIdLogicMap['query_where_claus_id'])) {
            $whereCluasStr = $this->logic2String($lineIdLogicMap['query_where_claus_id']['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
        }

        $this->set('where_claus', $whereCluasStr);



        $this->set('group_update', !empty($_POST['group_update']));
        $this->set('around_transaction', !empty($_POST['around_transaction']));


        $this->set('variables', $variables);


        $this->saveGlobalSetting();
    }

    public function create_insert() {
        $this->setLayout("ajax.phtml");

        $columns = json_decode($_POST['column_data'], true);
        $this->set('custom_header_code', $_POST['setting_custom_header_code']);
        $this->set('custom_tailer_code', $_POST['setting_custom_tailer_code']);


        $lineIdTableArray = json_decode($_POST['line_id_table_array'], true);
        $uidColumnMap = json_decode($_POST['uid_column_map'], true);
        $lineIdAliasMap = json_decode($_POST['line_id_alias_map'], true);
        $lineIdLogicMap = json_decode($_POST['line_id_logic_map'], true);
        $table_varialbe_map = json_decode($_POST['tab_var_map'], true);




        $this->set('function_name', $_POST['insert_name'], true);
        $this->set('table_size', count($lineIdAliasMap));

        $tableVariableMap = array();
        foreach ($table_varialbe_map as $one) {
            $tableVariableMap["{$one['db']}.{$one['table']}"] = $one['varialbe'];
        }

        $this->set('tname_veriable_map', $tableVariableMap);

        $lineIdTableFullNameMap = array();
        $fromTables = array();
        $variables = array();
        foreach ($lineIdTableArray as $one) {
            $lineId = $one['line_id'];
            $lineIdTableFullNameMap[$lineId] = $one['table_full_name'];
            $oneFromTable = array();
            $oneFromTable['line_id'] = $lineId;
            $oneFromTable['table_full_name'] = $one['table_full_name'];
            $oneFromTable['table_alias'] = $lineIdAliasMap[$lineId];
            $oneFromTable['table_variable'] = '$this->' . $tableVariableMap[$oneFromTable['table_full_name']];

            if (!empty($lineIdLogicMap[$lineId])) {
                $logic = $lineIdLogicMap[$lineId];

                $logicStrs = $this->logic2String($logic['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
                $oneFromTable['logic_strings'] = $logicStrs;
                $oneFromTable['join_type'] = $logic['join_type'];
            }

            $fromTables[] = $oneFromTable;
        }

        $this->set('frome_tables', $fromTables);

        $whereCluasStr = "";

        if (!empty($lineIdLogicMap['query_where_claus_id'])) {
            $whereCluasStr = $this->logic2String($lineIdLogicMap['query_where_claus_id']['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
        }

        $this->set('where_claus', $whereCluasStr);


        $cc = array();
        foreach ($columns as $id => $detail) {

            $column = $uidColumnMap[$id];
            $tableAlias = $lineIdAliasMap[$column['line_uid']];
            $tableFullName = $lineIdTableFullNameMap[$column['line_uid']];
            $tableVariable = $tableVariableMap[$tableFullName];
            $cc[] = array(
                'column_name' => $column['name'],
                'value' => $detail['value'],
                'type' => $detail['type'],
                'table_alias' => $tableAlias,
                'table_varialbe' => $tableVariable,
                'bind_type' => $detail['data_type'],
                'is_unique_index' => !empty($detail['is_unique_index']) ? true : false
            );

            if ($detail['type'] === 'variable') {

                $variables[$detail['value']] = array('func_param' => true, 'bind_var' => true, 'bind_type' => $detail['data_type'], "is_column" => true);
            }
        }

        $this->set('columns', $cc);

        $this->set('insert_update_method', !empty($_POST['insert_update_method']));

        $this->set('variables', $variables);

        $this->set('group_update', !empty($_POST['group_update']));
        $this->set('auto_compatiable', !empty($_POST['auto_compatiable']));
        $this->set('around_transaction', !empty($_POST['around_transaction']));


        if (!empty($_POST['insert_return_last_id'])) {
            $this->set('return_last_id', true);
        } else {
            $this->set('return_last_id', false);
        }

        $this->saveGlobalSetting();
    }

    public function load_setting() {
        $this->setLayout("ajax.phtml");

        $name = !empty($_GET['name']) ? $_GET['name'] : null;
        $sub_name = !empty($_GET['sub_name']) ? $_GET['sub_name'] : null;

        $re = array();

        if ($name === null) {
            $re['result'] = 'false';
            $re['error_message'] = "no name gaven";
        } else if ($sub_name === null) {
            $re['result'] = 'false';
            $re['error_message'] = "no sub name gaven";
        } else {
            $this->openProjectDb();

            if ($this->project_db === null) {
                $re['result'] = 'false';
                $re['error_message'] = "no project db";
            } else {
                $key = $name . '_' . $sub_name;
                $nv = $this->project_db->get($key);
                if ($nv === null) {
                    $re['result'] = 'nodata';
                } else {

                    $re['result'] = 'success';

                    $re['data'] = $nv;
                }
            }
        }

        $this->view->jsonStr = json_encode($re);
    }

    protected function logic2String($logic, $columnIdInfoMap, $lineIdAliasMap, &$variables) {

        
        if ($logic === null) {
            return null;
        }

        if ($logic['connector'] === null && $logic['condition'] === null) {
            return array('" *** ??? *** "');
        }

        $re = null;
        if ($logic ['single']) {
            if ($logic['condition'] === null) {
                return array('" ***???*** "');
            }


            $cond = $logic['condition'];

            $re = array();
            $logicV = null;

            $leftV = $this->getShowText($cond['left_select'], $cond['left_value'], $columnIdInfoMap, $lineIdAliasMap, $logic['data_type'], $variables);
            $rightV = $this->getShowText($cond['right_select'], $cond['right_value'], $columnIdInfoMap, $lineIdAliasMap, $logic['data_type'], $variables);
            $extraV = $this->getShowText($cond['extra_select'], $cond['extra_value'], $columnIdInfoMap, $lineIdAliasMap, $logic['data_type'], $variables);




            if ($cond ['logic_value'] === '') {
                $logicV = "???";
            } else {
                $logicV = $cond ['logic_value'];
            }

            if ($logicV === "IS NULL" || $logicV === 'IS NOT NULL') {

                $re[] = $this->transformByType($leftV, $cond['left_select']);
                $re[] = "' '";
                $re[] = $this->addQuote($logicV);
            } else if ($logicV === "BETWEEN") {

                $re[] = $this->transformByType($leftV, $cond['left_select']);
                $re[] = "' '";
                $re[] = $this->addQuote($logicV);
                $re[] = $this->addQuote(' ');
                $re[] = $this->transformByType($rightV, $cond['right_select']);
                $re[] = $this->addQuote(' AND ');
                $re[] = $re[] = $this->transformByType($extraV, $cond['extra_select']);
            } else if ($logicV === "IN" || $logicV === 'NOT IN') {
                $re[] = $this->transformByType($leftV, $cond['left_select']);
                $re[] = "' '";
                $re[] = $this->addQuote($logicV);
                $re[] = $this->addQuote(' ');

                $re[] = $this->addQuote('( ');


                if ($cond['right_select'] === 'variable_array_value') {
                    $re[] = $this->transformByType($rightV, $cond['right_select']);
                } else {

                    $rights = explode(',', $rightV);
                    foreach ($rights as $ind => $one) {
                        if ($ind !== 0) {
                            $re[] = $this->addQuote(',');
                        }
                        $re[] = $this->transformByType($one, $cond['right_select']);
                    }
                }


                $re[] = $this->addQuote(')');
            } else {
                $re[] = $this->transformByType($leftV, $cond['left_select']);
                $re[] = "' '";
                $re[] = $this->addQuote($logicV);
                $re[] = $this->addQuote(' ');
                $re[] = $this->transformByType($rightV, $cond['right_select']);
            }
        } else {

            $re = array();
            $leftStr = $this->logic2String($logic['left'], $columnIdInfoMap, $lineIdAliasMap, $variables);
            $rightStr = $this->logic2String($logic['right'], $columnIdInfoMap, $lineIdAliasMap, $variables);

            if ($leftStr === null) {
                $leftStr = array("'___ERROR_left_null___'");
            }
            if ($rightStr === null) {
                $leftStr = array("'___ERROR_right_null___'");
            }

            $re[] = $this->addQuote('(');


            $re = array_merge($re, $leftStr);
            $re[] = $this->addQuote(') ');
            if ($logic['connector'] === null) {
                $re[] = $re[] = $this->addQuote(" ?AND/OR? ");
            } else {
                $re[] = $this->addQuote($logic['connector']);
                ;
            }
            $re[] = $this->addQuote(' (');
            $re = array_merge($re, $rightStr);

            $re[] = $this->addQuote(')');
        }

        $re = $this->combineStringArray($re);

        return $re;
    }

    protected function combineStrings($str1, $str2) {
        $sLen1 = strlen($str1);
        $sLen2 = strlen($str2);

        $re = false;
        if ($sLen1 > 1 && $sLen2 > 1 && substr($str1, 0, 1) === "'" && substr($str2, 0, 1) === "'" && substr($str1, $sLen1 - 1) === "'" && substr($str2, $sLen2 - 1) === "'") {
            $re = substr($str1, 0, $sLen1 - 1) . substr($str2, 1);
        }


        return $re;
    }

    protected function combineStringArray($input) {
        if (empty($input)) {
            return $input;
        }

        $total = count($input);

        if ($total === 1) {
            return $input;
        }

        $newArray = array();
        $buffed = $input[0];
        for ($ind = 1;;) {
            if (isset($input[$ind])) {
                $newS = $this->combineStrings($buffed, $input[$ind]);
                if ($newS === false) {
                    //can not combine
                    $newArray[] = $buffed;
                    $newArray[] = $input[$ind];

                    $ind++;

                    if (isset($input[$ind])) {
                        $buffed = $input[$ind];
                    } else {
                        $buffed = null;
                        break;
                    }
                } else {
                    $buffed = $newS;
                }
                $ind++;
            } else {
                if ($buffed !== null) {
                    $newArray[] = $buffed;
                }
                break;
            }
        }

        return $newArray;
    }

    protected function addQuote($s) {
        return '\'' . $s . '\'';
    }

    protected function transformByType($v, $type) {

        if ($type === 'in_program_definitions' || $type === 'variable_array_value') {

            return $v;
        } else {
            return $this->addQuote($v);
        }
    }

    protected function getShowText($sel, $v, $columnIdInfoMap, $lineIdAliasMap, $dataType, &$variables) {
        if ($v === '') {
            return '???';
        }
        if ($sel === "variable_value") {
            $variables[$v] = array('func_param' => true, 'bind_var' => true, 'bind_type' => $dataType, 'where_variable' => true);
            return ':' . $v;
        } else if ($sel === 'variable_array_value') {

            $variables[$v] = array('func_param' => true, 'bind_var' => false);

            if (strpos($dataType, 'int') === false) {
                return "implode(','," . '$' . "{$v}_alias)";
            } else {
                return "implode(','," . '$' . "{$v})";
            }
        } else if ($sel === 'custom_value' || $sel === 'in_program_definitions') {
            return $v;
        } else {

            $columnInfo = $columnIdInfoMap[$sel];
            $lineId = $columnInfo['line_uid'];
            $tableAlias = $lineIdAliasMap[$lineId];

            if (empty($tableAlias)) {
                return "`{$columnInfo['name']}`";
            } else {
                return "`{$tableAlias}`.`{$columnInfo['name']}`";
            }
        }
    }

    protected function saveSpecialSetting($namespace, $key, $value) {
        if (empty($_POST['setting_name'])) {
            return;
        }
        if (empty($_POST['saved_header'])) {
            return;
        }
        $localKey = $_POST['setting_name'] . $_POST['saved_header'] . '_' . $namespace;


        $this->openProjectDb();
        if ($this->project_db !== null) {
            $localSetting = $this->project_db->get($localKey);

            if ($localSetting === null) {
                $localSetting = array();
            }

            $localSetting[$key] = $value;

            $this->project_db->set($localKey, $localSetting);


            $this->project_db->close();
        }
    }

    protected function saveGlobalSetting() {

        if (empty($_POST['setting_name'])) {
            return;
        }
        if (empty($_POST['saved_header'])) {
            return;
        }
        $globalKey = $_POST['setting_name'] . $_POST['saved_header'] . "_global_setting";

        $this->openProjectDb();
        if ($this->project_db !== null) {



            $globalSetting = $this->project_db->get($globalKey);

            if ($globalSetting === null) {
                $globalSetting = array();
            }

            $globalSetting['table_variable_map'] = json_decode($_POST['tab_var_map'], true);

            $globalSetting['setting_custom_header_code'] = $_POST['setting_custom_header_code'];
            $globalSetting['setting_custom_tailer_code'] = $_POST['setting_custom_tailer_code'];
            $globalSetting['setting_error_log_function_name'] = $_POST['setting_error_log_function_name'];
            $this->set('error_log', $_POST['setting_error_log_function_name']);


            $this->project_db->set($globalKey, $globalSetting);

            $this->project_db->close();
        }
    }

}

?>