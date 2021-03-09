<?php

    //  Necessary for saveUser method
    global $default_datetime_format, $default_number_format_decimal, $default_number_format_thousands_sep;


    //  Abort if Testing Users have already been set
    if($this->isTestingUsersSet)  {
        RestUtility::sendResponse(400, "Testing Users already set.");
        exit();
    }

    if(!$this->post['users']) {
        RestUtility::sendResponse(400, "No Testing Users defined.");
        exit();
    }
   
    //  Fetch users from request body
    $users = (object) json_decode($this->post["users"]);
    
    //  Setup database access
    $db = new RedCapDB();

    $response = array();

    foreach($users as $user) {

        // Ensure user doesn't already exist in user_information or auth table for inserts
        $userExists = $db->usernameExists($user->name);
        if($userExists) {
            RestUtility::sendResponse(400, "User $user->name already exists in the database. Test user creation aborted.");
            exit();            
        }
        
        // Save user to database
        $sql = $db->saveUser(

            null,   // ui_id 
            $user->name, 
            "Testing",   // fname
            "API",   // lname
            null,   // email 
            null, // email2
            null, // email3
            null, // inst_id 
            null, // expiration
            null, // user_sponsor
            "Created via API for testing purposes only. Do not use on production.", // user_comments 
            1,   // allow_create_db
            $user->pass,  //  password
            $default_datetime_format,
            $default_number_format_decimal,
            $default_number_format_thousands_sep,
            1,  //  display_on_email_users
            null,   //  user_phone
            null, //  user_phone_sms
        );

        if(!$sql) {
            RestUtility::sendResponse(400, "There was an error while saving user $user->name to the database.");
            exit();
        }

        $userid = $db->getUserInfoByUsername($user->name)->ui_id;

        // Set privileges
        foreach($user->privileges as $attr => $value) {
            User::saveAdminPriv($userid, $attr, $value);
        }

        // Log user creation event
        \Logging::logEvent(implode(";\n", $sql),"redcap_auth","MANAGE",$user->name,"user = '{$user->name}'","Create test user (User: $user->name)");
        
        // Push into response array
        $response[] = $db->getUserInfoByUsername($user->name);

    }

    //  Save final response
    $this->response = $response;

    #   Set the flag
    //$this->isTestingUsersSet = true;
    //$this->setSystemSetting('flag_testing_users_set', $this->isTestingUsersSet);



