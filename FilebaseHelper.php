<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FilebaseHelper {

    public static function write($tableName, $contents, $recordId=null, $overwrite=true) {
        $tablePath = self::tablePath($tableName);
        $recordId = self::createId($tableName,$recordId);
        if(!array_key_exists('id',$contents)) {
            $contents +=  ['id' => $recordId];
        } else {
            $recordId = $contents['id'];
        }
        //TODO Check if duplicated key
        $recordPath = $tablePath .'/'.$recordId;
        if(!is_array($contents)) {
            $contents = array('contents' => $contents);
        }
        ksort($contents);
        if(File::exists($recordPath) && $overwrite == false) {
            return null;
        }
        File::put($recordPath, serialize($contents));
        return $contents;
    }

    public static function read($tableName, $recordId=null, $descending=false) {
        $tablePath = self::tablePath($tableName);
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

    public static function select($tableName, $fieldName, $fieldValue, $operator='=',$range=null):array {
        $destinationPath = self::tablePath($tableName) . '/indexes/' . $fieldName;
        if(!File::exists($destinationPath)) {
            self::createIndex($tableName,$fieldName);
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

    public static function delete($tableName, $recordId, $soft=true):bool {
        $sourcePath = self::tablePath($tableName) . '/' . $recordId;
        $targetPath = self::tablePath($tableName) . '/deleted/' . $recordId . '_' . date("YmdHis");
        if(File::exists($sourcePath)) {
            if($soft==true) {
                rename($sourcePath , $targetPath );
            } else {
                unlink($sourcePath);
            }
        }
        return true;
    }

    public static function tables() {
        $destinationPath = self::filePath();
        foreach(array_diff(scandir($destinationPath,0), array(".", "..") ) as $table) {
            if(!is_file($destinationPath . '/' . $table)) {
                $elements[] = $table;
            }
        }
        sort($elements);
        return $elements;
    }

    public static function createIndex($tableName,$fieldName) {
        $destinationPath = self::tablePath($tableName) . '/indexes/' . $fieldName;
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
            self::createIndex($tableName,$field);
        }
    }

    public static function createKey($tableName,$fieldName) {
        $destinationPath = self::tablePath($tableName) . '/keys/' . $fieldName;
        if(!File::exists($destinationPath)) {
            File::put($destinationPath, '');
        }
        $index=array();
        //TODO if duplicate key return error
        foreach(self::read($tableName) as $records){
            $record= self::read($tableName,$records);
            if(isset($record[$fieldName])) {
                $index+= [$records => $record[$fieldName]];
            }
        }
        File::put($destinationPath, serialize($index));
    }

    public static function tableToFile($tableName,$connection=null):bool {
        if($connection==null) {
            $connection = env('DB_CONNECTION');
        }
        if(!DB::connection($connection)->getSchemaBuilder()->hasTable($tableName)) {
            return false;
        }
        $records = DB::connection($connection)
            ->table($tableName)
            ->get();
        if(!isset($records)) {
            return false;
        }
        foreach($records as $record) {
            self::write($tableName,(array)$record);
        }
        return true;
    }

    private static function createId($tableName,$recordId=null) {
        if($recordId==null) {
            $tablePath = self::tablePath($tableName);
            $elements = self::scan($tablePath);
            if(count($elements)==0) {
                return 1;
            }
            return self::read($tableName, max($elements))['id'] + 1;
        }
        return $recordId;
    }

    private static function scan($tablePath) {
        $elements =preg_grep('/^[1-9][0-9]{0,15}$/',scandir($tablePath,0));
        sort($elements);
        return $elements;
    }

    private static function tablePath($tableName):string {
        return self::filePath($tableName);
    }

    private static function filePath($path=null):string {
        $destinationPath = storage_path('app/db/') . $path;
        if(!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777,true,true);
            File::makeDirectory($destinationPath.'/deleted', 0777,true,true);
            File::makeDirectory($destinationPath.'/indexes', 0777,true,true);
            File::makeDirectory($destinationPath.'/keys', 0777,true,true);
        }
        return $destinationPath;
    }
}
