<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FilebaseHelper {

    public static function write($tableName, $contents, $recordId=null, $overwrite=true) {
        $tablePath = self::tables($tableName);
        $recordId = self::creatId($tableName,$recordId);
        if(!array_key_exists('id',$contents)) {
            $contents +=  ['id' => $recordId];
        } else {
            $recordId = $contents['id'];
        }
        $recordPath = $tablePath .'/'.$recordId;
        if(!is_array($contents)) {
            $contents = array('contents' => $contents);
        }
        ksort($contents);
        if(!File::exists($recordPath)) {
            File::put($recordPath, serialize($contents));
        } else {
            if($overwrite==false) {
                return null;
            }
            File::put($recordPath, serialize($contents));
        }
        return $contents;
    }

    public static function read($tableName, $recordId=null, $descending=false) {
        $tablePath = self::tables($tableName);
        if($recordId==null) { // all records
            $elements = self::scan($tablePath);
            if($descending ==true) {
                rsort($elements);
            }
            return $elements;
        }
        $recordPath = $tablePath .'/'.$recordId;
        if(!File::exists($recordPath)) {
            return null;
        }
        $contents= unserialize(File::get($recordPath));
        if($descending ==true) {
            krsort($contents);
        }
        return $contents;
    }

    public static function select($tableName, $fieldName, $fieldValue, $operator='=',$range=null) {
        $destinationPath = self::tables($tableName) . '/indexes/' . $fieldName;
        if(!File::exists($destinationPath)) {
            self::creatIndex($tableName,$fieldName);
        }
        $indexes= unserialize(File::get($destinationPath));
        $recordsIds=array();
        foreach($indexes as $i=>$index){
            if ($range ==null || in_array($i, $range)) {
                switch($operator) {
                    case '=':
                        if($index == $fieldValue) {
                            array_push($recordsIds, $i);
                        }
                        break;
                    case '<>':
                        if($index != $fieldValue) {
                            array_push($recordsIds, $i);
                        }
                        break;
                    case 'like':
                        if(mb_strpos($index, $fieldValue) !== false) {
                            array_push($recordsIds, $i);
                        }
                        break;
                    case '>':
                        if(is_numeric($index) && is_numeric($fieldValue) && ($index > $fieldValue)) {
                            array_push($recordsIds, $i);
                        }
                        break;
                    case '<':
                        if(is_numeric($index) && is_numeric($fieldValue) && ($index < $fieldValue)) {
                            array_push($recordsIds, $i);
                        }
                        break;
                    case '>=':
                        if(is_numeric($index) && is_numeric($fieldValue) && ($index >= $fieldValue)) {
                            array_push($recordsIds, $i);
                        }
                        break;
                    case '<=':
                        if(is_numeric($index) && is_numeric($fieldValue) && ($index <= $fieldValue)) {
                            array_push($recordsIds, $i);
                        }
                        break;
                }
            }
        }
        return $recordsIds;
    }

    public static function delete($tableName, $recordId, $soft=true) {
        $sourcePath = self::tables($tableName) . '/' . $recordId;
        $targetPath = self::tables($tableName) . '/deleted/' . $recordId . '_' . date("YmdHis");
        if(File::exists($sourcePath)) {
            rename($sourcePath , $targetPath );
        }
    }

    public static function creatId($tableName,$recordId=null) {
        if($recordId==null) {
            $tablePath = self::tables($tableName);
            $elements = self::scan($tablePath);
            if(count($elements)==0) {
                return 1;
            }
            return self::read($tableName, max($elements))['id'] + 1;
        }
        return $recordId;
    }

    public static function creatIndex($tableName,$fieldName) {
        $destinationPath = self::tables($tableName) . '/indexes/' . $fieldName;
        if(!File::exists($destinationPath)) {
            File::put($destinationPath, '');
        }
        $index=array();
        foreach(self::read($tableName) as $records){
            $record= self::read($tableName,$records);
            if(isset($record[$fieldName])) {
                $index+= [$records => $record[$fieldName]];
            }
        }
        File::put($destinationPath, serialize($index));
    }

    public static function indexAll($tableName, $fullScan=false) {
        $fields = array();
        foreach(self::read($tableName) as $records){
            $record= self::read($tableName,$records);
            foreach($record as $i=>$field) {
                if(!in_array($i, $fields, true)){
                    array_push($fields, $i);
                }
            }
            if($fullScan==false) {
                break;
            }
        }
        foreach($fields as $field) {
            self::creatIndex($tableName,$field);
        }
    }

    public static function tableToFile($tableName,$connection=null) {
        if($connection==null) {
            $connection = env('DB_CONNECTION');
        }
        $records = DB::connection($connection)
            ->table($tableName)
            ->get();
        foreach($records as $record) {
            self::write($tableName,(array)$record);
        }
    }

    public static function tables($tableName=null) {
        $destinationPath = self::filePath($tableName,true);
        if($tableName==null){
            foreach(array_diff(scandir($destinationPath,0), array(".", "..") ) as $table) {
                if(!is_file($destinationPath . '/' . $table)) {
                    $elements[] = $table;
                }
            }
            sort($elements);
            return $elements;
        }
        return $destinationPath;
    }

    public static function scan($tablePath) {
        $elements =preg_grep('/^[1-9][0-9]{0,15}$/',scandir($tablePath,0));
        sort($elements);
        return $elements;
    }

    public static function filePath($path=null,$directory=true):string {
        $destinationPath = storage_path('app/public/db/') . $path;
        if(!File::exists($destinationPath) && $directory==true) {
            File::makeDirectory($destinationPath, 0777,true,true);
            File::makeDirectory($destinationPath.'/deleted', 0777,true,true);
            File::makeDirectory($destinationPath.'/indexes', 0777,true,true);
        }
        if(!File::exists($destinationPath) && $directory==false) {
            File::put($destinationPath, '');
        }
        return $destinationPath;
    }

}
