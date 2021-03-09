<?php
        # Return a selection of REDCap config information, modules & project info and general server info.

        $this->response =  array(

            "configuration" => array(
                "redcap_version" => $this->config["redcap_version"],
                "api_enabled" => $this->config["api_enabled"],
                "is_development_server"=> $this->config["is_development_server"],
    
                "language_global" => $this->config["language_global"],
                "project_language" => $this->config["project_language"],
                
                "homepage_contact_email" => $this->config["homepage_contact_email"],
                "project_contact_email" => $this->config["project_contact_email"],
            ),

            "projects" =>$this->getProjectInfo(),
            "external_modules" => $this->getModuleInfo(),
            

            // dev only
            //"ids" => $this->getModuleIDs(),

            //"request_vars" => $this->request->getRequestVars(),
            //"server" => $_SERVER
            
        );