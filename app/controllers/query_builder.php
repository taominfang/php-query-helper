<?php

include dirname(__FILE__) . '/../models/project.php';

class Query_builderController extends BasicController {

    public function pre_filter(&$methodName = null) {
        parent::pre_filter($methodName);

        $this->view->addInternalJs("jquery-1.7.1.min.js");
        $this->view->addInternalJs("jquery-ui-1.8.17.custom.min.js");
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

            $ldb = new BerkeleyDB(__LOCAL_DB_FOLDER__ . '/index.db');


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


        $ldb = new BerkeleyDB(__LOCAL_DB_FOLDER__ . '/index.db');

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
        $ldb = new BerkeleyDB(__LOCAL_DB_FOLDER__ . '/index.db');


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
            $ldb = new BerkeleyDB(__LOCAL_DB_FOLDER__ . '/index.db');

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
                        }
                        else{
                            $re['result'] = 'nosubdata';
                        }
                    }
                }
            }
        }

        MLog::dExport($re);
        $this->view->jsonStr=  json_encode($re);
    }

}

?>