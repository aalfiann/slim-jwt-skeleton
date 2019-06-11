<?php
namespace modules\my_app;

class MyApp {

	//base var
    protected $basepath,$baseurl,$basemod;
    
    //construct
    function __construct($container=null) {
        $this->baseurl = (!empty($container)?$container['base_url'].dirname(dirname($_SERVER['PHP_SELF'])):'');
        $this->basepath = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']);
        $this->basemod = dirname(__FILE__);
    }

    //Get modules information
    public function viewInfo(){
        return file_get_contents($this->basemod.'/package.json');
    }

    public function yourAppMethod(){
        // Write your code here...
    }

}
