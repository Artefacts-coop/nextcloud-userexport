<?php

  $active_page = 'users';
  require_once 'functions.php';
  include_once 'config.php';

  session_secure_start();

  require_once 'l10n/'.$_SESSION['language'].'.php';

  // Filter POST array and save keys with value 'true' as constant
  $_SESSION['data_choices'] = array_keys($_POST,'true');

  if(!$_SESSION['data_choices']) {
    header('Content-Type: text/html; charset=utf-8');
    exit(L10N_ERROR . L10N_SELECT_AT_LEAST_ONE_COLUMN ." ". L10N_RETURN_TO_FORM);
  }

  $_SESSION['filters_set'] = array_keys($_POST, 'set_filter');
  $_SESSION['filter_group'] = $_POST['filter_group'] ?? null;
  $_SESSION['filter_quota'] = $_POST['filter_quota'] ?? null;
  $_SESSION['type_quota'] = $_POST['type_quota'] ?? null;
  $_SESSION['compare_quota'] = $_POST['compare_quota'] ?? null;

  $_SESSION['filter_ll_since'] = $_POST['filter_ll_since'] != ""
      ? $_POST['filter_ll_since']
      : '1970-01-01';

  $_SESSION['filter_ll_before'] = $_POST['filter_ll_before'] != ""
      ? $_POST['filter_ll_before']
      : date('Y-m-d');

  $export_type = $_POST['export_type'];
  $display_or_download = $_POST['submit'];

  $userlist = $_SESSION['filters_set']
    ? filter_users()
    : $_SESSION['userlist'];

  if($display_or_download == 'download') {

    // Set filename or create one depending on GET parameters
    if( (! isset($filename_download)) || $filename_download == null)
      $filename_download = "nextcloud-userlist_".date('Y-m-d_Hi').".csv";

    // Create and populate CSV file with selected user data and set filename variable
    $filename = build_csv_file(select_data_all_users(
        $_SESSION['data_choices'], $userlist, 'csv'), $_POST['csv_headers']);

    download_file( $filename, $mime_type=null, $filename_download, $_SESSION['temp_folder']);
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
      echo build_table_user_data(select_data_all_users(null, $userlist));
    else
      echo build_csv_user_data(select_data_all_users(null, $userlist, null, ','));

    ?>
  </body>
</html>
