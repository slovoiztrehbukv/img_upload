<?php

  /*******         directory for uploaded files      *********/

  chdir('uploaded'); // here
  $upload_dir = getcwd();



  /***************       db config & etc       ***************/

   $host      =   "127.0.0.1"; // can't use 'localhost' in PHP 7
   $dbname    =   "images_uploading";
   $user      =   "root";
   $password  =   "vy84aJbZdS7VU2z";
   $dsn       =   "mysql:host={$host};dbname={$dbname};charset=UTF8";

   $table     =   "images"; // the name of table we want

   $email     =   "test@test.test"; // admin's mail for notification



   /*****************     db connection       ****************/

   try {
       $db = new PDO($dsn, $user, $password);
       $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
       echo $e->getMessage();
   }


    /*******************      queries      ******************/

   $sql_table_check  =  "SHOW TABLES LIKE '$table'";
   $sql_table_create =  "CREATE TABLE $dbname.$table
           (
             id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY ,
             image_url VARCHAR(255) NOT NULL ,
             uploaded_at  TIMESTAMP
           )
           ";


    /**********      table existense check      ***********/

    $data = $db->prepare($sql_table_check);
    $data->execute();

    if (empty($data->fetchAll())) { // table doesn't exist
        $data = $db->prepare($sql_table_create);
        $data->execute();
    }
