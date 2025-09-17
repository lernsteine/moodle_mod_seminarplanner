<?php
namespace mod_seminarplanner\importer; defined('MOODLE_INTERNAL')||die();
class xls_importer {
  public static function is_available(): bool { return class_exists('PhpOffice\PhpSpreadsheet\IOFactory'); }
  public function parse(string $filepath): array {
    $events = [];
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filepath);
    $reader->setReadDataOnly(true); $spreadsheet = $reader->load($filepath);
    $sheet = $spreadsheet->getActiveSheet(); $rows = $sheet->toArray(null, true, true, true);
    if(!$rows || count($rows)<2){ return $events; }
    $header = array_map('strtolower', array_map('trim', $rows[1]));
    for($i=2;$i<=count($rows);$i++){ $r=$rows[$i]; $row=array_combine($header,$r);
      if(empty($row['title'])){ continue; }
      $start=strtotime(trim(($row['startdate']??'').' '.($row['starttime']??'')));
      $end  =strtotime(trim(($row['enddate']??($row['startdate']??'')).' '.($row['endtime']??'')));
      $events[] = (object)['title'=>$row['title'],'starttime'=>$start?:time(),'endtime'=>$end?:time(),'location'=>$row['location']??'','trainer'=>$row['trainer']??'','category'=>$row['category']??'','audience'=>$row['audience']??''];
    }
    return $events;
  }
}
