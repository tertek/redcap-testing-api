<?php
// Set the namespace defined in your config file
namespace STPH\TestingAPI;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;
use System;
use RestUtility;
use RCView;
use RedCapDB;
use Authentication;
use User;


/**
 * Class TestingAPI
 * @package STPH\TestingAPI
 */
class TestingAPI extends AbstractExternalModule {
    
    private $config;
    private $token;
    private $isTestingUsersSet;

    private $request;
    private $post;

    private $response;


    public function __construct() {
        parent::__construct();
        $this->config =  System::getConfigVals();
        $this->token = $this->getSystemSetting('testing_api_token');
        $this->isTestingUsersSet = $this->getSystemSetting("flag_testing_users_set");

    }

    # Process Testing API request as REDCap API request 
    # without REDCap API token (false) since we're using our own token
    public function processTestingRequest() {

        $this->request = RestUtility::processRequest(false);
        $this->post = $this->request->getRequestVars();

        $this->checkAuthentication();
        $this->handleEndpoint();
        //$this->handleResponse();
    }

    # Check Authentication with Testing API Token
    # Deny access if token mismatch or no token supplied
    protected function checkAuthentication() {
        if(!isset($_SERVER['HTTP_TOKEN']) || $_SERVER['HTTP_TOKEN']  !==  $this->token ) {
            RestUtility::sendResponse(403);
        }
    }

    protected function handleEndpoint() {
        if(!isset($this->post['content']) || !isset($this->post['action'])) {
            RestUtility::sendResponse(400, "No content and/or action set.");
        }

        # Include endpoint to generate response
        require ("endpoints/" . $this->post['content'] . "/" . $this->post['action']. ".php");

        # Return response
        RestUtility::sendResponse(200, json_encode($this->response), 'json');

    }

    #  Get all project ids for projects that are not demo projects
    private function getProjectInfo() {
        $sql = "SELECT p.project_id, p.project_name, p.status,p.project_language, p.surveys_enabled FROM `redcap_projects` p WHERE p.purpose IS NOT NULL";
        $query = $this->query( $sql, array() );
        $result = mysqli_fetch_all( $query , MYSQLI_ASSOC);
        
        //return array_merge(...$result);
        return $result;
    }

    private function getModuleIDs() {
        $sql = "SELECT external_module_id FROM `redcap_external_modules` WHERE NOT directory_prefix = 'testing_api'";
        $query = $this->query( $sql, array() );
        $result = mysqli_fetch_all( $query , MYSQLI_NUM);
        return array_merge(...$result);
    }

    #   Get info for all active external modules (except testing api module)
    #   active means enabled set to true or version not null if no enabled exists
    private function getModuleInfo() {
        $sql = "SELECT m.external_module_id, m.directory_prefix, s.key, s.value FROM `redcap_external_modules` m JOIN `redcap_external_module_settings` s ON m.external_module_id = s.external_module_id WHERE NOT m.directory_prefix = 'testing_api' AND( (s.key = 'enabled' AND s.value = 'true') OR ( (s.key = 'version' AND s.value IS NOT NULL and s.external_module_id NOT IN(SELECT external_module_id FROM redcap_external_module_settings s WHERE s.key = 'enabled' GROUP BY external_module_id))) ) ORDER BY s.external_module_id";
        $query = $this->query( $sql, array() );
        $result = mysqli_fetch_all( $query , MYSQLI_ASSOC);

        return $result;
    }

    public function renderModulePage() {

        ## Page title
        print RCView::h4(array('style'=>'margin-top:0;'), '<i class="fas fa-vial"></i> Testing API' . "<span class=\"text-secondary ml-2\">(Cypress REDCap)</span>");
        print  "<p>
			This page will help you to configure your local <a href='#link-to-cypress-redcap'>cypress-redcap</a> application for sucessfully authenticating with REDCap and retrieve testing data.
		</p>";
        
        ## Subtitle
        print "<p style='padding-top:10px;color:#800000;font-weight:bold;font-family:verdana;font-size:13px;'>Copy & Paste into your <code>.env</code> file</p>";

        # Warning if token is not set
        if(!isset($this->token) || empty($this->token)) {          
            print RCView::warnBox("Testing Token is not set. Please re-enable this module to fix the issue.");
        }
 
        else {
            print RCView::pre( array(), 
                    RCView::code(array('style' => 'color:#e83e8c;'), 
                        'REDCAP_BASEURL = '.APP_PATH_WEBROOT_FULL . '<br>' .
                        'REDCAP_TESTING_API_TOKEN = '.$this->token . '<br>' .
                        'REDCAP_TESTING_ENDPOINT_URL = api/?NOAUTH&type=module&prefix=testing_api&page=index'
                    )                    
                  );
         }
    }

    #  Generate Testing API Token
    private function generateTestingToken(){
        //  taken from /Classes/RedCapDB::setAPIToken
        return strtoupper(md5( USERID . APP_PATH_WEBROOT_FULL  . generateRandomHash(mt_rand(64, 128))));
    }

    # Set a new token if empty or null
    private function handleTestingToken() {
        if( empty( $this->token ) || null ===  $this->token ) {
            $token = $this->token =  $this->generateTestingToken();
            $this->setSystemSetting('testing_api_token', $token);
        }
    }

    # Hook into "enable module on system level"
    public function redcap_module_system_enable() {               
        #   Handle Testing API Token 
        $this->handleTestingToken();
    }
}