<?php

  // get study folders
  $dirs = array_map(function($elem){return str_replace('../../../study_output/Emilie/', '', $elem);}, glob('../../../study_output/Emilie/*', GLOB_ONLYDIR));

  // get data pushed
  $studyid=$_POST['put-studyid-here'];
  $sscode=$_POST['put-sscode-here'];
  $data=$_POST['put-data-here'];

  // check if study is listed in dirs
  if (!in_array($studyid,$dirs)){die('invalid study');}

  // check if student id is listed
  if (!isset($sscode)){die('no sscode specified');}

  // check if data packet exists
  if (!isset($data)){die('no data specified');}

  // write to file
  file_put_contents('../../../study_output/Emilie/' . $studyid . '/' . $studyid . '-' . $sscode . '-data.txt', $data, FILE_APPEND);

  // include credit page
  include("credit.html");
  // header(str_replace('XXXX', $sscode, "location: https://uwaterloo.sona-systems.com/webstudy_credit.aspx?experiment_id=4649&credit_token=a85aa553a3324f8abf664da250771a6f&survey_code=XXXX"));
?>
