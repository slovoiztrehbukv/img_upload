<?php

require 'config.php';
if (!empty($_POST)) {
    for ($i = 1; $i <= count($_POST); $i++) {
        echo '<li>
        file:
        <strong>'.$_FILES['file_'.$i]['name'].'</strong>
        was succesfully uploaded by name
        <strong>'.$_POST['file_'.$i.'_name'].'</strong>
        </li>';
        move_uploaded_file($_FILES['file_'.$i]['tmp_name'], // temporary file
                           $upload_dir.'/'.$_POST['file_'.$i.'_name']); // final destination
    }
    die;
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

        #info-ul > li {
          color: #fff;
          list-style: none;
        }

        #info-ul > li > strong {
          color: #fff;
          text-decoration: underline;
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
                <input type="file" name="file_1" class="file-input" id="file">
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
          fileName.value = file.files[0].name;
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


        /*
        * events handling
        */

        fileInput.addEventListener ('change', function () {
          if (fileInput.files.length > 0) {
            fileAdd.disabled = false;
            input.disabled = false;
            input.value = fileInput.files[0].name;
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
    </script>

  </body>
</html>
