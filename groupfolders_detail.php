<?php

  $active_page = 'groupfolders';
  require_once 'functions.php';
  include_once 'config.php';

  session_secure_start();

  require_once 'l10n/'.$_SESSION['language'].'.php';

  $export_type = $_POST['export_type'];
  $display_or_download = $_POST['submit'];

  if($display_or_download == "download") {
    // Set filename or create one depending on GET parameters
    if($filename_download == null)
      $filename_download = "nextcloud-groupfolderlist_" . date("Y-m-d_Hi") . ".csv";

    // Set default column headers or no column headers depending on selection
    $headers = $_POST['csv_headers'] == 'default'
      ? L10N_ID.','.L10N_NAME.','.L10N_GROUPS.','.L10N_QUOTA_USED.','
        .L10N_PERCENTAGE_USED.','.L10N_QUOTA.','.L10N_ACL.','.L10N_ADMIN
      : 'no_headers';

    // Create and populate CSV file with groupfolder data and set filename variable
    $filename = build_csv_file(build_groupfolder_data('array'),$headers);

    download_file($filename, $mime_type=null, $filename_download, $_SESSION['temp_folder']);
    exit();
  }

  echo "<html lang='{$_SESSION['language']}'>";

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
    <meta charset="UTF-8">
    <title>Nextcloud Userexport</title>
    <script>
      /**
        * Source of the following function 'sortTable':
        * https://stackoverflow.com/a/49041392
        *
        * sort table columns on header click
        *
        */
      function sortTable() {
        const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

        const comparer = (idx, asc) => (a, b) => ((v1, v2) =>
          v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
          )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

        document.querySelectorAll('th').forEach(th => th.addEventListener('click', (() => {
          const table = th.closest('table');
        Array.from(table.querySelectorAll('tr:nth-child(n+2)'))
          .sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
          .forEach(tr => table.appendChild(tr) );
        })));
      }
    </script>
  </head>

  <body>
    <?php

    include 'navigation.php';

    if(!$_SESSION['authenticated']) {
      header('Content-Type: text/html; charset=utf-8');
      exit('<br>'.L10N_CONNECTION_NEEDED);
    }

    print_status_overview();

    /**
      * Display results page either as HTML table or comma separated values (CSV)
      */
    if($export_type == 'table')
      echo build_table_groupfolder_data();
    else
      echo build_groupfolder_data();

    ?>
  </body>
</html>
