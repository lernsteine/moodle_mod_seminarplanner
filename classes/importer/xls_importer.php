<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block definition for mod_seminarplanner.
 *
 * @package    mod_seminarplanner
 * @copyright  2025 Ralf Hagemeister <ralf.hagemeister@lernsteine.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
