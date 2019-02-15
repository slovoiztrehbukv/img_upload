<?php

require 'config.php';

// this "if"-block bellow will be runed only after form submition
if (!empty($_POST)) {
    $new_files = array(); // init [] for file info; using array instead of string cause possibly functionality expanding
    $status = false; // 'true' means: file was succesfully saved in 'uploaded' directory
    for ($i = 1; $i <= count($_POST); $i++) {

        // getting the incoming data
        $file_user_name = $_POST['file_'.$i.'_name']; // user's file's defined name without extension
        $file_name = $_FILES['file_'.$i]['name']; // user's file's original name with extension
        $file_temp_name = $_FILES['file_'.$i]['tmp_name']; // temporary uploaded file (path)


        // getting file's properties
        $finfo = getimagesize($file_temp_name); // width, height and MIME-type
        $mime =  $finfo['mime'];


        // getting file's format
        $ext = ($mime == 'image/png') ? '.png' : '.jpg'; // dot included!


        // forming the output
        if (imageChecking($file_user_name.$ext, $file_temp_name, $upload_dir, $finfo, $status)) {
            echo '<li>
                  file
                  <strong>'.$file_name.'</strong>
                  was succesfully uploaded with name
                  <strong>'.$file_user_name.'</strong>
                  </li>';


            // file normal saving (with no scaling)
            move_uploaded_file($file_temp_name, // temporary file
                             $upload_dir.'/'.$file_user_name.$ext); // final destination}

            $status = true;
        }

        if ($status == true) {
            $new_files[$i] = [
            'url' => $file_user_name.$ext
          ];
        }
    }

    if (!empty($new_files)) {
        mailing(compact('new_files'), $email); // sending info to administrator
        dbWriting($new_files);
    }

    die; // preventing all page's html except form response
}

  /*
  * main functions
  */

  function imageChecking($img, $tmp, $dir, $finfo, &$status) // ( 1. user's filename for existense checking | 2. user's file's temporary name | 3. path to 'uploaded' directory | 4. mime type for unwished extension detection + width&height information | 5. link to the 'status')
  {
      $full_path = $dir.'/'.$img; // path for existed file


      // 1. Checking for existense
      if (file_exists($full_path)) {
          echo '<li class="file-error">file <strong>'.$img.'</strong> is already uploaded! try to use another name!</li>';
          $status = false;
          return false;
      }


      // 2. Checking for file's format (jpg&png only)
      if (($finfo['mime'] != 'image/png') && ($finfo['mime'] != 'image/jpeg')) {
          echo '<li class="file-error">file <strong>'.$img.'</strong> isn\'t an image! try to upload another file!</li>';
          $status = false;
          return false;
      }


      // 3. Checking for image size
      if ($finfo[0] > 500 || $finfo[1] > 500) {
          echo '<li>file <strong>'.$img.'</strong> was too big! but don\'t worry, we\'ve took care about this :)</li>';
          resizing($tmp, $finfo['mime'], $dir.'/'.$img, $status); // file will be generated and saved via imagecreate function(native), not via 'move_uploaded_file'
          return false;
      }

      return true; // everything's checked
  }

  function resizing($file, $mime, $outfile_path, &$status_ok)
  {
      if ($mime == 'image/png') {
          $img = imagecreatefrompng($file);
          $img = imagescale($img, 500);
          imagepng($img, $outfile_path, -1);
          $status_ok = true;
      } else {
          $img = imagecreatefromjpeg($file);
          $img = imagescale($img, 500);
          imagejpeg($img, $outfile_path, -1);
          $status_ok = true;
      }
  }

  function mailing(array $info, $email):void
  {
      $text  = '<div class="mail-block">'.PHP_EOL;
      $text .= '<h3>New files are uploaded: </h3>'.PHP_EOL;
      $text .= '<ul>'.PHP_EOL;
      foreach ($info['new_files'] as $file) {
          $text .= "<li><a href=\"uploaded/".$file['url']."\">".$file['url']."</a></li>".PHP_EOL;
      }
      $text .= '</ul>'.PHP_EOL;
      $text .= '<span>at '.date('d F H:i:s', strtotime("+3 hours")).'</span>';
      $text .= '</div>'.PHP_EOL;
      $text .= '<hr>'.PHP_EOL;

      file_put_contents(__DIR__.'/mail_logs.html', $text, FILE_APPEND | LOCK_EX); // switch to native 'mail' function on real web-server
  }

  function dbWriting(array $info)
  {
      global $data, $table, $db;

      $sql_img_add  = "INSERT INTO $table (image_url) VALUES ";
      foreach ($info as $file) {
          $sql_img_add .= "('".$file['url']."'),";
      }

      $final_sql = rtrim($sql_img_add, ",");

      $data =  $db->prepare($final_sql);
      return $data->execute();
  }

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Images uploading</title>
    <style media="screen">
        * {
          margin: 0;
          padding: 0;
          font-family: monospace;
          color: #27a;
        }

        html {
          background: #333;
        }

        .wrapper {
          background-color: #27a;
          margin: 50px auto 0 auto;
          width: -moz-fit-content;
          width: fit-content;
        }

        .container {
          width: 700px;
        }

        .form {
          position: relative;
        }

        .file-group {
          margin: 15px 0;
        }

        .form-group {
          display: flex;
          justify-content: center;
        }

        input,label {
          outline: none;
          border: 0;
          /* border-radius: 3px; */
          padding: 10px 20px;

        }
        *[disabled] {
          cursor: no-drop;
          color: #777;
        }

        .file-group-label {
          background: #ccc;
          cursor: pointer;
        }

        .file-input {
          display: none;
        }

        .file-name {
            text-align: right;
            margin-left: -3px;
            width: 240px;
            background-color: #eee;
        }

        .file-add {
          /* margin-left: -10px; */
          position: absolute;
          bottom: -30px;
          right: -35px;
          z-index: -1;
          border-radius: 10px 0;
          padding: 10px 15px;
          background-color: #fff;
          color: #27a;
          cursor: pointer;
        }

        .file-add[disabled] {
          background-color: #222;
          color: #777;
          cursor: no-drop;
        }

        .submit-btn {
          position: absolute;
          right: 0;
          bottom: -35px;
          color: #fff;
          background-color: #27a;
          border-radius: 0 0 10px 10px;
          cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #f1bb42;
        }

        #info-ul {
          padding-top: 15px;
        }

        #info-ul > li {
          color: #74d03a;
          list-style: none;
        }

        #info-ul > li > strong {
          color: #fff;
          text-decoration: underline;
        }

        #info-ul > li.file-error   {
          color: #d61414d4;
        }
    </style>
  </head>

  <body>

    <div class="wrapper">
      <div class="container">
        <div class="form-group">
          <form id="upload-form" class="form"  method="post" enctype="multipart/form-data">
            <div class="reporting-block">
              <ul id="info-ul"></ul>
            </div>
            <div class="file-group" id="file-group">
              <label class="file-group-label">
                <span class="file-name-span">select file</span>
                <input type="file" name="file_1" class="file-input" id="file" accept="image/jpeg,image/x-png">
              </label>
              <input type="text" name="file_1_name" placeholder="file name" disabled class="file-name" id="file-name">

            </div>
            <input type="button"  value="+" disabled class="file-add" id="file-add">
            <input type="submit" name="uploading" value="Upload!" class="submit-btn" id="submit-btn">
          </form>
        </div>
      </div>
    </div>

    <script>

    /*
    * variables definition
    */

      let submitButton = document.getElementById('submit-btn');
      let fileGroup = document.getElementById('file-group');
      let file = document.getElementById('file');
      let fileName = document.getElementById('file-name');
      let fileAdd = document.getElementById('file-add');
      let form = document.getElementById('upload-form');
      let counter = 1;
      let infoBlock = document.getElementById('info-ul');

    /*
    * events handling
    */

      file.addEventListener('change',function () {
        if(file.files.length > 0 ) {
          fileName.value = deExtensioning(file.files[0].name);
          fileName.disabled = false;
          fileAdd.disabled = false;
        } else {
          fileName.disabled = true;
          fileAdd.disabled = true;
          fileName.value = '';
        }
      });

      fileAdd.addEventListener ('click', function () {
        newFileGroup();
        fileAdd.disabled = true;
      });

      submitButton.addEventListener ('click', function (e) {
        e.preventDefault();
        let filesArr = document.getElementsByClassName('file-name');

        sending(filesArr);
        informing();
      });

    /*
    * main functions
    */

      function newFileGroup () {

        /*
        * file_'s counter
        */

        counter++;

        /*
        *  elements creating
        */

        // outter div

        let fileGroup = document.createElement('div');
        fileGroup.classList.add('file-group');

        // div's inner

        let label = document.createElement('label');
        label.classList.add('file-group-label');

        let input = document.createElement('input');
        input.classList.add('file-name');
        input.placeholder = 'file name';
        input.name = 'file_'+counter+'_name';
        input.disabled = true;

        // label's inner

        let span = document.createElement('span');
        span.classList.add('file-name-span');
        span.innerHTML = 'select file';

        let fileInput = document.createElement('input');
        fileInput.classList.add('file-input');
        fileInput.type = 'file';
        fileInput.name = 'file_' + counter;
        fileInput.accept = "image/jpeg,image/x-png";


        /*
        * events handling
        */

        fileInput.addEventListener ('change', function () {
          if (fileInput.files.length > 0) {
            fileAdd.disabled = false;
            input.disabled = false;
            input.value = deExtensioning(fileInput.files[0].name);
          } else {
            fileAdd.disabled = true;
            input.disabled = true;
            input.value = '';
          }
        });

        /*
        * elements appending
        */

        label.appendChild(span);
        label.appendChild(fileInput);

        fileGroup.appendChild(label);
        fileGroup.appendChild(input);

        form.appendChild(fileGroup);

      }

      function sending () {
        let Req = new XMLHttpRequest;
        form = document.getElementById('upload-form');
        let data = new FormData(form);

        Req.open('POST','', true);
        Req.send(data);

        Req.addEventListener('readystatechange',function() {
          if(Req.status == 200) {
            informing(Req.responseText);
          }
        });
      }

      function informing (data) {
        infoBlock.innerHTML = data;
      }

      function deExtensioning (fileName) {
        let cleanFileName = fileName.split('.');
        cleanFileName.pop();
        string = cleanFileName.join('.');
        return string;
      }
    </script>

  </body>
</html>
