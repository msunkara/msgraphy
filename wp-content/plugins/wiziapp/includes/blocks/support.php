<?php 

if (!defined('WP_WIZIAPP_BASE')) 
    exit();

class WiziappSupport {
    private $path = '';
    private static $instance = null;
    
    private function __construct() {
        $this->path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;        
    }
    
    public static function getInstance(){
        if (is_null(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }
    
    public function listLogs(){
        if (is_dir($this->path) && is_readable($this->path)){
            if ($logsDir = opendir($this->path)){
                while (($log = readdir($logsDir)) !== false){
                    if (preg_match("/\.log\.php$/", $log) ){
                        $logs[] = array(
                            'name' => $log, 
                            'date' => filemtime($this->path.$log),
                            'size' => filesize($this->path.$log),
                        );
                            
                    }
                } 
                $this->returnResults(array('logs'=>$logs), 'listLogs');
            } else {
                $this->alert(500, "Could not open log directory", 'listLogs');
            }
        } else {
            $this->alert(500, "The log directory does not exists", 'listLogs');
        }      
        
    }
    
    public function getLog($log){  
        $file = $this->path.$log;
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
        } else {
            header("HTTP/1.0 404 Not Found");
        }
        exit;
        
    }
    
    protected function alert($code, $msg, $action=''){
        $status = array(
            'action' => $action,
            'status' => false,
            'code' => $code,
            'message' => Yii::t('yii',$msg),
        );
        
        // API request should never be cached
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        $result = array('header' => $status);
        
        header('Content-type: application/json');
        echo json_encode($result);
        exit();
    }  
    
    protected function returnResults($body, $action=''){
        $status = array(
            'action' => $action,
            'status' => true,
            'code' => 200,
            'message' => '',
        );
        
        $result = array_merge(array('header' => $status), $body);
        
        header('Content-type: application/json');
        echo json_encode($result);
        
        exit();
    }  
    
}
