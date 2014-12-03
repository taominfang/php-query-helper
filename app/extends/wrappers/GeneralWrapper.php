<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GeneralWrapper
 *
 * @author mtao60
 */
class GeneralWrapper extends BasicController {

    protected $project_name = null;
    protected $project_index = null;
    protected $project_db_file_path = null;
    protected $project_db = null;

    protected function fetchProject($pName = '',$indexHeader='',$projectNameInSession='project_index') {

        if (empty($pName)) {
            $this->project_index = $_SESSION[$projectNameInSession];

            if (!empty($this->project_index)) {
                $this->project_db_file_path = __LOCAL_DB_FOLDER__ . '/' . $this->project_index . '.db';
            }
        } else if (!empty($_GET[$pName])) {
            $this->project_name = $_GET[$pName];

            $ldb = new FileDB(__LOCAL_DB_FOLDER__ . "/{$indexHeader}index.db");


            $this->project_index = $ldb->fetch($this->project_name);

            if ($this->project_index !== false) {
                $this->project_db_file_path = __LOCAL_DB_FOLDER__ . '/' . $this->project_index . '.db';
            }
            $ldb->close();
        }
    }

    protected function openProjectDb($pName = '') {
        $this->fetchProject($pName);

        if ($this->project_db_file_path !== null) {
            $this->project_db = new Project($this->project_db_file_path);
        }
    }
    
    protected function saveTemplate($indexHeader='',$projectNameInSession='project_index'){
        $ldb = new FileDB(__LOCAL_DB_FOLDER__ . "/{$indexHeader}index.db");


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

        $proDB = new Project($projFileName);


        $ldb->save($proName, $project_index);


        $ldb->close();
        $proDB->close();
        $_SESSION[$projectNameInSession] = $project_index;
        
        return $project_index;
       
    }

}
