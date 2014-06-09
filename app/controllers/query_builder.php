<?php

include dirname(__FILE__) . '/../models/project.php';

class Query_builderController extends BasicController {

    public function pre_filter(&$methodName = null) {
        parent::pre_filter($methodName);

        $this->view->addInternalJs("jquery-1.7.1.min.js");
        $this->view->addInternalJs("jquery-ui-1.8.17.custom.min.js");
        $this->view->addInternalJs("utility.js");


        $this->view->addInternalCss("ui-lightness/jquery-ui-1.8.17.custom.css");
    }

    protected $project_name = null;
    protected $project_index = null;
    protected $project_db_file_path = null;
    protected $project_db = null;
    protected $pdo_db = null;

    protected function openProjectDb($pName = '') {
        $this->fetchProject($pName);

        if ($this->project_db_file_path !== null) {
            $this->project_db = new Project($this->project_db_file_path);
        }
    }

    protected function openPDOdb($pName = '') {
        $this->openProjectDb($pName);

        if ($this->project_db !== null) {
            $this->project_db->close();

            $dsn = "{$this->project_db->engine}:host={$this->project_db->host};port={$this->project_db->port}";

            try {
                $this->pdo_db = new PDO($dsn, $this->project_db->user, $this->project_db->password);
              
            } catch (PDOException $exc) {
                MLog::e($exc->getTraceAsString());
                $this->pdo_db = null;
            }
        }
    }

    protected function fetchProject($pName = '') {

        if (empty($pName)) {
            $this->project_index = $_SESSION['project_index'];

            if (!empty($this->project_index)) {
                $this->project_db_file_path = __LOCAL_DB_FOLDER__ . '/' . $this->project_index . '.db';
            }
        } else if (!empty($_GET[$pName])) {
            $this->project_name = $_GET[$pName];

            $ldb = new FileDB(__LOCAL_DB_FOLDER__ . '/index.db');


            $this->project_index = $ldb->fetch($this->project_name);

            if ($this->project_index !== false) {
                $this->project_db_file_path = __LOCAL_DB_FOLDER__ . '/' . $this->project_index . '.db';
            }
            $ldb->close();
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
        
        MLog::d("projFileName:{$projFileName} index:{$project_index}");
        $this->redirect($this->view->popUrl("query_builder/select_tables"));
    }

    public function select_tables() {

        $this->openPDOdb();
       

        if ($this->pdo_db === null) {
            if ($this->project_name !== null) {
                $this->redirect($this->view->popUrl('query_builder/index?load=') . htmlentities($this->project_name));
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
        //MLog::dExport($dbs);
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
            MLog::d(json_encode($tables));
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

        $db_tables = array();
        $this->openPDOdb();

        if ($this->pdo_db === null) {
            throw new Exception("Open db fail");
        }

        foreach ($tableInfo as $db => $tables) {
            $this->pdo_db->exec("use " . $db);

            $tabs = array();

            foreach ($tables as $table) {
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
            if(!empty($_POST['db_name'])){
                $this->set('dbname',$_POST['db_name']);
            }
            
        } else {
            MLog::d("open project db fail!");
        }
    }

    public function create_class() {
        $this->setLayout("ajax.phtml");




        $this->view->class_name = $_POST['class_name'];

        $this->view->table_varialbe_map = json_decode($_POST['tab_var_map'], true);

        $this->view->db_table_info = unserialize($_SESSION['db_tables']);

        $this->openProjectDb();

        if ($this->project_db !== null) {

            $globalSetting = $this->project_db->get('global_setting');

            if ($globalSetting === null) {
                $globalSetting = array();
            }

            $globalSetting[$_POST['setting_name']] = $this->view->table_varialbe_map;

            $this->project_db->set('global_setting', $globalSetting);

            $this->project_db->close();
        }
    }

    public function create_query() {
        $this->setLayout("ajax.phtml");

        $columns = json_decode($_POST['column_data'], true);

        $tableInfo = json_decode($_POST['table_info'], true);
        $lineIdTableArray = json_decode($_POST['line_id_table_array'], true);
        $uidColumnMap = json_decode($_POST['uid_column_map'], true);
        $lineIdAliasMap = json_decode($_POST['line_id_alias_map'], true);
        $lineIdLogicMap = json_decode($_POST['line_id_logic_map'], true);
        $table_varialbe_map = json_decode($_POST['tab_var_map'], true);


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

                $logicStr = $this->logic2String($logic['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
                $oneFromTable['logic_string'] = $logicStr;
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
                $variables[$one['variable']] = 1;
                $descVairalbeName='ordery_by_' . $one['variable'] . '_is_desc';
                $one['desc_variable']=$descVairalbeName;
                $variables[$descVairalbeName] = 1;
            } else {
                $column = $uidColumnMap[$one['column_id']];
                $tableFullName = $lineIdTableFullNameMap[$column['line_uid']];
                $tableVariable = $tableVariableMap[$tableFullName];
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


        $this->set('variables', array_keys($variables));
        $this->set('pagination_function_enable', !empty($_POST['query_add_page_function']));

        //MLog::dExport($_POST);
//        MLog::dExport($columns, 'columns');
//        MLog::dExport($tableInfo, '$tableInfo');
//        MLog::dExport($lineIdTableArray, '$uidTableMap');
//        MLog::dExport($lineIdAliasMap, '$lineIdAliasMap');
//        MLog::dExport($tableVariableMap, '$tableVariableMap');
//        MLog::dExport($lineIdLogicMap, '$lineIdLogicMap');
//        MLog::dExport($uidColumnMap, '$uidColumnMap');
//        MLog::dExport($variables, '$variables');
        MLog::dExport($oderBy, '$oderBy');
    }

    public function create_update() {
        $this->setLayout("ajax.phtml");

        $columns = json_decode($_POST['column_data'], true);


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

                $logicStr = $this->logic2String($logic['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
                $oneFromTable['logic_string'] = $logicStr;
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
                'table_varialbe' => $tableVariable);

            if ($detail['type'] === 'variable') {
                $variables[$detail['value']] = 1;
            }
        }

        $this->set('columns', $cc);



        $this->set('variables', array_keys($variables));



        //MLog::dExport($_POST);
//        MLog::dExport($columns, 'columns');
//        MLog::dExport($tableInfo, '$tableInfo');
//        MLog::dExport($lineIdTableArray, '$uidTableMap');
//        MLog::dExport($lineIdAliasMap, '$lineIdAliasMap');
//        MLog::dExport($tableVariableMap, '$tableVariableMap');
//        MLog::dExport($lineIdLogicMap, '$lineIdLogicMap');
//        MLog::dExport($uidColumnMap, '$uidColumnMap');
//        MLog::dExport($variables, '$variables');
    }

    public function create_delete() {
        $this->setLayout("ajax.phtml");



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

                $logicStr = $this->logic2String($logic['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
                $oneFromTable['logic_string'] = $logicStr;
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





        $this->set('variables', array_keys($variables));



        //MLog::dExport($_POST);
//        MLog::dExport($columns, 'columns');
//        MLog::dExport($tableInfo, '$tableInfo');
//        MLog::dExport($lineIdTableArray, '$uidTableMap');
//        MLog::dExport($lineIdAliasMap, '$lineIdAliasMap');
//        MLog::dExport($tableVariableMap, '$tableVariableMap');
//        MLog::dExport($lineIdLogicMap, '$lineIdLogicMap');
//        MLog::dExport($uidColumnMap, '$uidColumnMap');
//        MLog::dExport($variables, '$variables');
    }

    public function create_insert() {
        $this->setLayout("ajax.phtml");

        $columns = json_decode($_POST['column_data'], true);


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

                $logicStr = $this->logic2String($logic['logic_data'], $uidColumnMap, $lineIdAliasMap, $variables);
                $oneFromTable['logic_string'] = $logicStr;
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
                'table_varialbe' => $tableVariable);

            if ($detail['type'] === 'variable') {
                $variables[$detail['value']] = 1;
            }
        }

        $this->set('columns', $cc);



        $this->set('variables', array_keys($variables));

        if(!empty($_POST['insert_return_last_id'])){
             $this->set('return_last_id', true);
        }
        else{
             $this->set('return_last_id', false);
        }

        //MLog::dExport($_POST);
        MLog::dExport($columns, 'columns');
//        MLog::dExport($tableInfo, '$tableInfo');
//        MLog::dExport($lineIdTableArray, '$uidTableMap');
//        MLog::dExport($lineIdAliasMap, '$lineIdAliasMap');
//        MLog::dExport($tableVariableMap, '$tableVariableMap');
//        MLog::dExport($lineIdLogicMap, '$lineIdLogicMap');
//        MLog::dExport($uidColumnMap, '$uidColumnMap');
//        MLog::dExport($variables, '$variables');
    }

    public function load_setting() {
        $this->setLayout("ajax.phtml");

        $name = !empty($_GET['name']) ? $_GET['name'] : null;
        $sub_name = !empty($_GET['sub_name']) ? $_GET['sub_name'] : null;

        $re = array();

        if ($name === null) {
            $re['result'] = 'false';
            $re['error_message'] = "no name gaven";
        } else {
            $this->openProjectDb();

            if ($this->project_db === null) {
                $re['result'] = 'false';
                $re['error_message'] = "no project db";
            } else {
                $nv = $this->project_db->get($name);
                if ($nv === null) {
                    $re['result'] = 'nodata';
                } else {

                    if ($sub_name === null) {
                        $re['result'] = 'success';
                        $re['data'] = $nv;
                    } else {
                        if (isset($nv[$sub_name])) {
                            $re['result'] = 'success';
                            $re['data'] = $nv[$sub_name];
                        } else {
                            $re['result'] = 'nosubdata';
                        }
                    }
                }
            }
        }

        MLog::dExport($re);
        $this->view->jsonStr = json_encode($re);
    }

    protected function logic2String($logic, $columnIdInfoMap, $lineIdAliasMap, &$variables) {

        if ($logic === null) {
            return null;
        }

        if ($logic['connector'] === null && $logic['condition'] === null) {
            return ' *** ??? *** ';
        }

        $re = null;
        if ($logic ['single']) {
            if ($logic['condition'] === null) {
                return " ***???*** ";
            }


            $cond = $logic['condition'];

            $logicV = null;

            $leftV = $this->getShowText($cond['left_select'], $cond['left_value'], $columnIdInfoMap, $lineIdAliasMap, $variables);
            $rightV = $this->getShowText($cond['right_select'], $cond['right_value'], $columnIdInfoMap, $lineIdAliasMap, $variables);
            $extraV = $this->getShowText($cond['extra_select'], $cond['extra_value'], $columnIdInfoMap, $lineIdAliasMap, $variables);




            if ($cond ['logic_value'] === '') {
                $logicV = "???";
            } else {
                $logicV = $cond ['logic_value'];
            }


            if ($cond['left_select'] === 'variable_value') {
                $leftV = ":" . $leftV;
            }
            if ($cond['right_select'] === 'variable_value') {
                $rightV = ":" . $rightV;
            }
            if ($cond['extra_select'] === 'variable_value') {
                $extraV = ":" . $extraV;
            }



            if ($logicV === "IS NULL" || $logicV === 'IS NOT NULL') {

                $re = $leftV . " " . $logicV;
            } else if ($logicV === "BETWEEN") {

                $re = $leftV . " " . $logicV . " " . $rightV . " AND " . $extraV;
            } else {
                $re = $leftV . " " . $logicV . " " . $rightV;
            }
        } else {

            $re = "";
            $leftStr = $this->logic2String($logic['left'], $columnIdInfoMap, $lineIdAliasMap, $variables);
            $rightStr = $this->logic2String($logic['right'], $columnIdInfoMap, $lineIdAliasMap, $variables);

            if ($leftStr === null) {
                $leftStr = "___ERROR_left_null___";
            }
            if ($rightStr === null) {
                $leftStr = "___ERROR_right_null___";
            }

            $re .= "( " . $leftStr . ") ";
            if ($logic['connector'] === null) {
                $re .= " ?AND/OR? ";
            } else {
                $re .= $logic['connector'] . " ";
            }
            $re .= "( " . $rightStr . " )";
        }
        return $re;
    }

    protected function getShowText($sel, $v, $columnIdInfoMap, $lineIdAliasMap, &$variables) {
        if ($v === '') {
            return "???";
        }
        if ($sel === "variable_value" || $sel === 'custom_value') {
            if ($sel === "variable_value") {
                $variables[$v] = 1;
            }
            return $v;
        } else {

            $columnInfo = $columnIdInfoMap[$sel];
            $lineId = $columnInfo['line_uid'];
            $tableAlias = $lineIdAliasMap[$lineId];

            if (empty($tableAlias)) {
                return $columnInfo['name'];
            } else {
                return "{$tableAlias}.{$columnInfo['name']}";
            }
        }
    }

}

?>