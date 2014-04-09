<?php
define('APC','apc');
define('SQLITE','sqlite');
define('MEMCACHE','memcache');
define('FILE','file');
define('CLOUDDB','clouddb');

class BaseCache {
	public $error = '';
	protected $cacheTypes = array(SQLITE, APC, FILE, MEMCACHE, CLOUDDB);
	protected $options = array('cacheName'=>'','version'=>2,'type'=>SQLITE,'ttl'=>86400,'automaticCleaningFactor'=>0,'hashedDirectoryLevel'=>2);
	//$cache is set public so we can remove caches on one web server from another...
	public $cache;
        private $cacheType;
		
	function BaseCache($cacheName,$cacheTTL='',$cacheType='') {
		$this->_construct($cacheName,$cacheTTL,$cacheType);
	}
	
	function _construct($cacheName,$cacheTTL='',$cacheType='') {
		if($cacheTTL!='' && is_numeric($cacheTTL)) {
			$this->options['ttl'] = $cacheTTL;
		}
		if(in_array($cacheType,$this->cacheTypes)) {
			$this->options['type'] = $cacheType;
		}
		switch($this->options['type']) {
			case SQLITE:
				$this->getSQLiteCache($cacheName);
				break;
			case FILE:
				$this->getFileCache($cacheName);
				break;
			case MEMCACHE:
				$this->cache_name = $cacheName;
				$this->initializeMemcache($cacheName);
				break;				
		}
                $this->cacheType = $this->options['type'];
		$this->options['cacheName']=$cacheName;
	}
		
	static function sendNotificationEmailToDhingana($mail_subject,$mail_body) {
		$user = new User();
		$user->setFirstname("Memcache");
		$user->setLastname("Monitor");
		//Get the list of emails to be notified upon failure
		$email_list_arr = array("phapalegaurav@gmail.com, san.sutar@gmail.com");
		
		/*
        foreach($email_list_arr as $email_id){
			$user->setEmail($email_id);
			User::sendCritsendEmail($user,$mail_subject,$mail_body);
		}
		*/
	}
	
    function initializeMemcache($cacheName) {
		$cacheKeyName = MEMCACHE . '_' . $cacheName;
		$cache = Common::getContextVariable($cacheKeyName);
		if($cache && $cache->get($cacheKeyName)) {
			$this->cache = $cache;
		} else {
			$this->cache = new Memcache;
			switch($cacheName) {
				case "playlist":
                case "song":                                    
					$memcache_det = sfConfig::get('app_memcache_server1');
					$memcache_host = $memcache_det["host"];	//app1 (using private ip for fast access)
					$memcache_port = $memcache_det["port"];				
						break;
				case "radiostation":
				case "album":
				case "timeline":
				case "trend":
				case "subscription":
				case "suggestion":
					$memcache_det = sfConfig::get('app_memcache_server3');				
				    $memcache_host = $memcache_det["host"];
					$memcache_port = $memcache_det["port"];				
					break;
				case "api":
				case "user":
					$memcache_det = sfConfig::get('app_memcache_server2');
					$memcache_host = $memcache_det["host"];
					$memcache_port = $memcache_det["port"];
					break;
				case "search":
					$memcache_det = sfConfig::get('app_memcache_server4');
					$memcache_host = $memcache_det["host"];
					$memcache_port = $memcache_det["port"];
					break;
				case "web_default_cache":
					$memcache_host = "app16.dhingana.com";
					$memcache_port = 11235;
					break;
				case "web_song_cache":
					$memcache_host = "app17.dhingana.com";
					$memcache_port = 11236;
					break;
				case "web_search_cache":
					$memcache_host = "app18.dhingana.com";
					$memcache_port = 11237;
					break;									
			}
			
			try {
				$mResult = $this->cache->connect($memcache_host,$memcache_port,4);
				//if($cacheName=="song") { print "flushing cache"; $this->cache->flush();}
				if($mResult===false) {
					$this->error = "[DhinganaCache_v2::initializeMemcache] Could NOT connect to $cacheName memcache server $memcache_host::$memcache_port";
					//DhinganaCache_v2::sendNotificationEmailToDhingana("URGENT: ".ucfirst($cacheName)." Memcache $memcache_host::$memcache_port DOWN", $this->error);
				}
				$this->cache->set($cacheKeyName, 1);
				Common::setContextVariable($cacheKeyName, $this->cache);
			} catch (Exception $e) {
				$this->error = "[DhinganaCache_v2::initializeMemcache] $cacheName memcache - $memcache_host::$memcache_port Exception:".$e->message();
				//DhinganaCache_v2::sendNotificationEmailToDhingana("URGENT: ".ucfirst($cacheName)." Memcache $memcache_host::$memcache_port ERROR", $this->error);
			}
		}
	}
	
	function get($key,$namespace='',$doNotTestCacheValidity=false) {
		$key = $this->prepend_cache_name_to_key($key);
		switch ($this->options['type']) {
			case APC:
				$value = apc_fetch($key);
				return false === $value ? null : $value;
				break;
			case SQLITE:
				return $this->cache->get($key,$namespace,$doNotTestCacheValidity);
				break;
			case FILE:
				return $this->cache->get($key,'',$doNotTestCacheValidity);
				break;
			case MEMCACHE:
				if($this->error=='') {
					return $this->cache->get($key);
				} else {
					return false;
				}
				break;
		}
	}
	
	function set($key,$value,$namespace='',$expire=0) {
		$key = $this->prepend_cache_name_to_key($key);
		switch ($this->options['type']) {
			case APC:
				//return sfProcessCache::set($key,$value,$this->options['ttl']);
				return apc_store($key,$value,$this->options['ttl']);
				break;
			case SQLITE:
				return $this->cache->set($key,$namespace,$value);
				break;
			case FILE:
				return $this->cache->set($key,'',$value);
				break;	
			case MEMCACHE:
				if($this->error=='') {
					if($value instanceof Song) {
						//print "cache was instance of Song...chaning to array before storing";
						$value = $value->toCustomArray();
					}
					return $this->cache->set($key,$value,MEMCACHE_COMPRESSED,$expire);
				} else {
					return false;
				}
				break;							
		}		
	}
	
	function remove($key,$namespace='',$bustWebServers=true) {
		$key = $this->prepend_cache_name_to_key($key);
		//require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'Common.php');
		require_once('util/Common.php');
		//error_log("[dhingana cache v2] calling other servers to remove cache $key");
		switch ($this->options['type']) {
			case APC:
				//return sfProcessCache::remove($key,$value,$this->options['ttl']);
				return apc_delete($key);
				break;
			case SQLITE:
				//$this->removeCacheFromAllServers('remove',$key,$namespace,$bustWebServers);
				return $this->cache->remove($key,$namespace);
				break;
			case FILE:
				//$this->removeCacheFromAllServers('remove',$key,$namespace,$bustWebServers);
				return $this->cache->remove($key,'');
				break;
			case MEMCACHE:
				return $this->cache->delete($key);
				break;								
		}		
	}
	
	function removeCacheFromAllServers($type,$key,$namespace='',$bustWebServers=true) {
		$bustWebServers = false;
		if($bustWebServers) {
			//$host = sfContext::getInstance()->getRequest()->getHost();
			$host = $_SERVER['SERVER_ADDR']; 
			$webservers = explode(',',sfConfig::get('app_webservers'));
			foreach($webservers as $webserver) {
				$serverInfo = explode('=',$webserver);
				if($serverInfo[0]!=$host) {
					//print "busting cache = ".$webserver."<bR>";
					$cacheAPI = new sfWebBrowser(array(),'sfCurlAdapter');	
					//print "api = ".'http://'.$webserver."/api/cache/type/$type/key/$key/namespace/$namespace/options/".Common::base64_urlencode(serialize($this->options));
					//print "$type, $key, $namespace, ".Common::base64_urlencode(serialize($this->options));		
					$cacheAPI->get('http://'.$serverInfo[1].'/api/cache', array('type'=>$type,'key'=>$key,'namespace'=>$namespace,'options'=>Common::base64_urlencode(serialize($this->options))),array('api-key'=>'dhingana-cache-api'));
					//print "response = ".$cacheAPI->getResponseText();
					//print "cache busted...";
				}
			}
		} else {
			//print "not busting caching..";
		}
	}
	
	function has($key,$namespace='',$doNotTestCacheValidity=false) {
		$key = $this->prepend_cache_name_to_key($key);
		switch ($this->options['type']) {
			case APC:
				//return sfProcessCache::has($key);
				return false === apc_fetch($key) ? false : true;
				break;
			case SQLITE:
				return $this->cache->has($key,$namespace,$doNotTestCacheValidity);
				break;
			case FILE:
				return $this->cache->has($key,'',$doNotTestCacheValidity);
				break;
			case MEMCACHE:
				if($this->error=='') {
					return false === $this->cache->get($key) ? false: true;
				} else {
					return false;
				}
				break;				
		}		
	}
	
	function clean($namespace,$bustWebServers=true) {
		switch ($this->options['type']) {
			case APC:
				return apc_clear_cache('user');
				break;
			case SQLITE:
				$this->removeCacheFromAllServers('clean','',$namespace,$bustWebServers);
				return $this->cache->clean($namespace);
				break;
			case FILE:
				$this->removeCacheFromAllServers('clean','',$namespace,$bustWebServers);
				return $this->cache->clean($namespace);
				break;		
		}		
	}

	function prepend_cache_name_to_key($key){
	    if(is_array($key)){
	        $new_keys = array();
	        foreach($key as $value){
	            $new_keys[] = $this->options['cacheName'] . '_' . $value;	            
	        }
	        return $new_keys;
	    }
		return $this->options['cacheName'] . '_' . $key;		
	}
        
	//Removing the destructor since this is unsetting the shared object
        /*function __destruct() {
            if($this->cacheType == MEMCACHE) {
                if(!$this->cache->close()) {
                    error_log("ERROR: error closing memcache connection");
                }
            }
        }*/
}
