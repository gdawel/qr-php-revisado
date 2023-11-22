<?php

class FileHandler
{
    var $error;
    var $filename;

    /**
     * directory_list
     * return an array containing optionally all files, only directiories or only files at a file system path
     * @author     cgray The Metamedia Corporation www.metamedia.us
     *
     * @param    $base_path         string    either absolute or relative path
     * @param    $filter_dir        boolean    Filter directories from result (ignored except in last directory if $recursive is true)
     * @param    $filter_files    boolean    Filter files from result
     * @param    $exclude        string    Pipe delimited string of files to always ignore
     * @param    $recursive        boolean    Descend directory to the bottom?
     * @return    $result_list    array    Nested array or false
     * @access public
     * @license    GPL v3
     */
    function directory_list($directory_base_path, $filter_dir = true, $filter_files = false,
        $exclude = ".|..|.DS_Store|.svn", $recursive = false)
    {
        $directory_base_path = rtrim($directory_base_path, "/") . "/";

        if (!is_dir($directory_base_path)) {
            //error_log(__function__ . "File at: $directory_base_path is not a directory.");
            echo __function__ . "File at: $directory_base_path is not a directory.";
            return false;
        }

        $result_list = array();
        $exclude_array = explode("|", $exclude);

        if (!$folder_handle = opendir($directory_base_path)) {
            error_log(__function__ . "Could not open directory at: $directory_base_path");
            return false;
        } else {
            while (false !== ($filename = readdir($folder_handle))) {
                if (!in_array($filename, $exclude_array)) {
                    if (is_dir($directory_base_path . $filename . "/")) {
                        if ($recursive && strcmp($filename, ".") != 0 && strcmp($filename, "..") != 0) { // prevent infinite recursion
                            error_log($directory_base_path . $filename . "/");
                            $result_list[$filename] = directory_list("$directory_base_path$filename/", $filter_dir,
                                $filter_files, $exclude, $recursive);
                        } elseif (!$filter_dir) {
                            $result_list[] = $filename;
                        }
                    } elseif (!$filter_files) {
                        $result_list[] = $filename;
                    }
                }
            }
            closedir($folder_handle);
            return $result_list;
        }
    }

    function isAllowedExtension($allowedExtensions = array('pdf'))
    {
        foreach ($_FILES["userfiles"]["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $filename = utf8_decode($_FILES["userfiles"]["name"][$key]);
                //$fileextension = end(explode(".", strtolower($name)));
                $path_info = pathinfo($filename);
                $fileextension = $path_info['extension'];

                if (!in_array($fileextension, $allowedExtensions))
                    return false;
            }
        }
        return true;
    }

    function uploadFiles($uploads_dir = '../Uploads', $filename = null)
    {
        $return = -1;

        foreach ($_FILES["userfiles"]["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["userfiles"]["tmp_name"][$key];

                if ($filename) {
                    $original_filename = utf8_decode($_FILES["userfiles"]["name"][$key]);
                    $path_info = pathinfo($original_filename);
                    $fileextension = $path_info['extension'];

                    $name = $filename . '.' . $fileextension;
                } else {
                    $name = date('Ymd') . '_' . utf8_decode($_FILES["userfiles"]["name"][$key]);
                }

                $name = $this->adjustFilename($name);
                $this->filename[] = $name;

                $return = move_uploaded_file($tmp_name, "$uploads_dir/$name");
            }
        }

        return $return;
    }

    function deleteFile($filename, $uploads_dir = '../Uploads')
    {
        if (file_exists($uploads_dir . '/' . $filename)) {
            chdir($uploads_dir . '/');
            chown($filename, 666);
            return @unlink($filename);
        } else {
            $this->error = "Arquivo nao encontrado.";
            return false;
        }
    }

    function adjustFilename($var)
    {
        $var = strtolower($var);
        $var = preg_replace('/[^(\x20-\x7F)]*/','', $var);
        $var = str_replace(" ", "_", $var);
        $var = preg_replace("[#$%^&*?]", "_", $var);
        return $var;
    }

}
?>