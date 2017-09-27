<?php

/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */
class TopLogger
{
    public $conf = array('separator' => "\t", 'log_file' => '');
    private $fileHandle;

    public function log($logData)
    {
        if (('' == $logData) || (array() == $logData)) {
            return false;
        }


        if (is_array($logData)) {
            $logData = implode($this->conf['separator'], $logData);
        }


        $logData = $logData . "\n";
        fwrite($this->getFileHandle(), $logData);
    }

    protected function getFileHandle()
    {
        if (NULL === $this->fileHandle) {
            if (empty($this->conf['log_file'])) {
                trigger_error('no log file spcified.');
            }


            $logDir = dirname($this->conf['log_file']);

            if (!is_dir($logDir)) {
                mkdir($logDir, 511, true);
            }


            $this->fileHandle = fopen($this->conf['log_file'], 'a');
        }


        return $this->fileHandle;
    }
}


?>