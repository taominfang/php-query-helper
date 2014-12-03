<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of html_builder
 *
 * @author mtao60
 */
include dirname(__FILE__) . '/../models/project.php';

class Html_builderController extends GeneralWrapper {

    public function pre_filter(&$methodName = null) {
        parent::pre_filter($methodName);

        $this->view->addInternalJs("jquery-1.7.1.min.js");
        $this->view->addInternalJs("jquery-ui-1.8.17.custom.min.js");
        $this->view->addInternalJs("utility.js");
        $this->view->addInternalJs("ajax_post.js");


        $this->view->addInternalCss("ui-lightness/jquery-ui-1.8.17.custom.css");
        header('Content-Type:text/html; charset=utf-8');
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


        $ldb = new FileDB(__LOCAL_DB_FOLDER__ . '/html_index.db');

        $this->set('savedTemplates', $ldb->fetchAll());
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

    public function new_template() {

        $pName = $this->saveTemplate('html_');
        $this->redirect($this->view->popUrl("html_builder/select_type?project={$pName}"));
    }

    public function select_type() {

        if (!empty($_REQUEST['project'])) {
            $_SESSION['project_index'] = $_REQUEST['project'];
        }

        $this->openProjectDb();

        if ($this->project_db !== null) {
            $this->project_db->close();

            $wh = $this->project_db->get('general_wrapper_header');
            $wt = $this->project_db->get('general_wrapper_tailer');
        }

        if (!empty($wh)) {
            $this->set('general_wrapper_header', $wh);
        } else {
            $this->set('general_wrapper_header', '');
        }
        if (!empty($wt)) {
            $this->set('general_wrapper_tailer', $wt);
        } else {
            $this->set('general_wrapper_tailer', '');
        }
    }

    protected function saveGeneralWrapperInfo() {

        $this->openProjectDb();

        if ($this->project_db !== null) {

            if (isset($_REQUEST['general_wrapper_header'])) {
                $this->project_db->set('general_wrapper_header', $_REQUEST['general_wrapper_header']);
            }

            if (isset($_REQUEST['general_wrapper_tailer'])) {
                $this->project_db->set('general_wrapper_tailer', $_REQUEST['general_wrapper_tailer']);
            }


            $this->project_db->close();
        }
    }

    protected $select_setting_values = array(
        'select_wrapper_header',
        'select_class',
        'select_name',
        'select_id',
        'select_id_as_name',
        'select_value',
        'select_from_variable',
        'foreach_as_key',
        'foreach_as_value',
        'option_wrapper_header',
        'option_wrapper_tailer',
        'option_class',
        'option_id',
        'option_name',
        'option_name_as_id',
        'option_value',
        'option_lable',
        'selected_left',
        'selected_right',
        'option_lable',
        'selected_is_number'
    );
    protected $radio_setting_values = array(
        'radio_wrapper_header',
        'radio_class',
        'radio_button_wrapper_header',
        'radio_button_wrapper_tailer',
        'radio_button_class',
        'radio_wrapper_tailer'
    );

    protected function beforePrepareCreate($createName, $settings) {
        $this->saveGeneralWrapperInfo();

        $header = $this->project_db->get("{$createName}_wrapper_header");
        if (empty($header)) {
            $header = $this->project_db->get("general_wrapper_header");
        }

        $tailer = $this->project_db->get("{$createName}_wrapper_tailer");
        if (empty($tailer)) {
            $tailer = $this->project_db->get("general_wrapper_tailer");
        }

        $this->set("{$createName}_wrapper_header", $header);
        $this->set("{$createName}_wrapper_tailer", $tailer);

        foreach ($settings as $one) {
            $this->set($one, $this->project_db->get($one));
        }
    }

    protected function beforeDoCreateSaveSetting($settings) {
        $this->openProjectDb();

        if ($this->project_db !== null) {
            foreach ($settings as $one) {
                if (isset($_POST[$one])) {
                    $this->project_db->set($one, $_POST[$one]);
                }
            }
            $this->project_db->close();
        }
    }

    protected function setViewValuesFromPost($vs) {
        foreach ($vs as $one) {
            if (isset($_POST[$one])) {

                $this->set($one, $_POST[$one]);
            }
        }
    }

    public function create_select() {
        $this->beforePrepareCreate("select", $this->select_setting_values);
    }

    public function create_radio() {
        $this->beforePrepareCreate("select", $this->radio_setting_values);
    }

    public function do_create_select() {
        $this->setLayout("ajax.phtml");
        $this->beforeDoCreateSaveSetting($this->select_setting_values);
        $this->setViewValuesFromPost($this->select_setting_values);
    }

    public function do_create_radio() {
        $this->setLayout("ajax.phtml");
        $this->beforeDoCreateSaveSetting($this->radio_setting_values);


        $this->setViewValuesFromPost($this->radio_setting_values);
        $this->setViewValuesFromPost(array(
            'radio_button_current_value',
            'radio_button_name',
        ));

        $rList = json_decode($_POST['radio_button_list']);

        $radioList = array();
        foreach ($rList as $one) {
            $radioList[] = array(
                'val' => $_POST['radio_button_value_' . $one],
                'label' => $_POST['radio_button_label_' . $one],
                'id' => $_POST['radio_button_id_' . $one]
            );
        }
        
        if(!empty($_POST['radio_button_current_value']) && strpos($_POST['radio_button_current_value'], '$')===0){
            $this->set('current_value_is_variable',true);
        }
        else{
            $this->set('current_value_is_variable',false);
        }
        

        MLog::dExport($rList);
        MLog::dExport($_POST);
        MLog::dExport($radioList);
        $this->set('radio_list', $radioList);
    }

}
